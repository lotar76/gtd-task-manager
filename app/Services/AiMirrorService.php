<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AiMirrorCache;
use App\Models\Goal;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMirrorService
{
    private const CACHE_TTL = [
        'day' => 60,       // 1 час в минутах
        'week' => 1440,    // 1 день
        'month' => 1440,   // 1 день
        'year' => 10080,   // 1 неделя
    ];

    private string $systemPrompt = <<<'PROMPT'
Ты — «Зеркало жизни», встроенный помощник в GTD-приложении.

Твоя задача — анализировать данные о жизни пользователя и давать честную, живую обратную связь. Не статистику — а осознанный взгляд со стороны.

ПРАВИЛА ОБЩЕНИЯ:
- Говори как умный друг, не как робот. Коротко, по делу.
- Не лей воду. Каждое предложение — ценность.
- Называй вещи своими именами. «Ты забил на здоровье» — нормально.
- Не хвали без причины. Хвали только реальные достижения.
- Задавай вопросы которые заставляют думать.
- Не используй эмодзи, списки, маркдаун. Только живой текст.
- Если faith_enabled=true — можешь добавить короткую цитату из Библии, но только если она реально в тему. Не вставляй для галочки. Если false — не добавляй.

СТРУКТУРА ДАННЫХ ПОЛЬЗОВАТЕЛЯ:
- Сферы жизни — пользователь сам создаёт сферы (названия в данных). У каждой сферы есть задачи (done/total) и дни без внимания.
- Задачи — привязаны к сфере напрямую ИЛИ через цель/поток. Количество задач отражает реальные действия в сфере.
- Цели — привязаны к сферам. У цели есть прогресс (%), дедлайн, статус. Цель реализуется через потоки и задачи.
- Потоки (проекты) — принадлежат целям. Поток содержит задачи с прогрессом (done/total). Застрявший поток = дни без активности.

АНАЛИЗИРУЙ:
1. Баланс сфер — где перекос? Какие сферы доминируют, какие голодают?
2. Цели — на каком они этапе? Опережаешь, отстаёшь, стоишь?
   - «До цели X осталось Y дней, а прогресс Z% — давай ускоряйся» или «нормальный темп» или «цель мёртвая, прогресс не движется»
   - Если целей нет — «бесцельная жизнь, задачи есть но зачем?»
3. Потоки → Цели — какой поток тормозит какую цель? Если поток застрял — цель под угрозой.
   - «Поток X тормозит цель Y — N дней без движения»
   - «Потоки по цели Z выполнены — цель близка к достижению!»
4. Задачи без сферы — если есть, это неосознанная активность
5. Тренды — что растёт, что падает, что стоит?
6. Рекомендации — конкретные, выполнимые, по одной на проблему

ФОРМАТ ОТВЕТА — строго JSON, никакого текста вне JSON.
PROMPT;

    private array $periodPrompts = [
        'day' => <<<'PROMPT'
Период: ДЕНЬ.

Проанализируй сегодняшний план пользователя. Обрати внимание:
- Какие сферы представлены в задачах, какие — нет
- Есть ли перекос (например, всё — работа)
- Есть ли важные сферы которые давно без внимания (days_without_attention)
- Цели: есть ли движение к целям сегодня? Или день уходит в рутину без связи с целями?
- Потоки: какие потоки двигаются, какие стоят? Если поток стоит — скажи к какой цели он привязан
- Если целей нет вообще — мягко обрати внимание: задачи без целей это жизнь по инерции

Главный вопрос дня: «Ты сегодня приближаешься к своим целям или просто занят?»

Вот данные:
PROMPT,

        'week' => <<<'PROMPT'
Период: НЕДЕЛЯ.

Проанализируй неделю пользователя. Обрати внимание:
- Распределение внимания по сферам за неделю (процент задач)
- Какие сферы получили 0% внимания
- Потоки: какие активны, какие застряли? Застрявший поток — якорь для цели, назови какой
  - «Поток X тормозит цель Y — Z дней без движения»
  - «Потоки по цели Z выполнены — цель на финише!»
- Цели: темп за неделю — ускоряешься или замедляешься?
  - Если прогресс < ожидаемого: «До цели X осталось Y дней. Текущий темп не вытянет — давай ускоряйся»
  - Если прогресс в норме: «Цель X — хороший темп, продолжай»
  - Если прогресс 0 и дедлайн приближается: «Цель X — мёртвый груз. Либо займись, либо честно закрой»
- Если целей нет: «Неделя без целей — ты просто крутишь колесо. Куда ты идёшь?»
- Есть ли дни когда всё ушло в одну сферу

Главный вопрос недели: «Эта неделя приблизила тебя к жизни которую ты хочешь?»

Вот данные:
PROMPT,

        'month' => <<<'PROMPT'
Период: МЕСЯЦ.

Проанализируй месяц пользователя. Обрати внимание:
- Общая нагрузка — запланировано vs выполнено, реален ли темп
- Тренды по сферам — что выросло, что упало (было → стало)
- Застрявшие потоки — сколько дней без движения и какие цели они тормозят
  - «Поток X тормозит цель Y уже Z дней. Это не пауза, это провал»
- Цели: месячный прогресс vs ожидание
  - Сравни фактический прогресс с ожидаемым. Если отстаёт — «осталось N дней, нужно ускориться в M раз»
  - Если цель на нуле месяц+ — «цель X не двигается. Это ещё цель или уже мечта?»
  - Если цель достигнута — «поздравляю! Цель X достигнута, можно ставить новую»
- Потоки → Цели: все потоки по цели выполнены = цель на финише. Ни один не двигается = цель провалена.
- Перекосы на длинной дистанции — сфера без внимания месяц это уже запущено

Главный вопрос месяца: «Ты приближаешься к жизни которую хочешь, или уходишь от неё?»

Вот данные:
PROMPT,

        'year' => <<<'PROMPT'
Период: ГОД.

Проанализируй годовую картину пользователя. Обрати внимание:
- Сколько целей достигнуто, сколько в процессе, сколько стоят мёртвым грузом
  - Для каждой активной цели: статус (on_track/behind/at_risk/stalled) и прогноз — успеет или нет
  - Если цель stalled: «Цель X стоит уже месяцами. Либо возьмись, либо удали — мёртвый груз тянет вниз»
  - Если цель at_risk: «Цель X под угрозой — осталось N дней а прогресс M%. Нужно ускориться в K раз»
- Тренд индекса баланса — жизнь становится сбалансированнее или нет
- Какие сферы системно растут, какие системно падают
- Потоки: сколько завершено, сколько застряли. Если все потоки цели выполнены — «цель достигнута!»
- Если целей мало или нет — «год без целей это год на автопилоте. Ты строишь жизнь или она тебя?»
- Общая траектория — куда человек движется

Главный вопрос года: «Ты строишь жизнь которую хочешь, или жизнь которая получается?»

Вот данные:
PROMPT,
    ];

    /**
     * Получить AI-сообщение (с кешем)
     */
    public function getMessage(int $workspaceId, string $period, array $mirrorData): array
    {
        // Проверяем кеш
        $periodKey = $this->getPeriodKey($period);
        $cached = $this->getCached($workspaceId, $period, $periodKey);
        if ($cached) {
            return $cached;
        }

        // Проверяем наличие API-ключа
        $apiKey = config('openrouter.api_key');
        if (empty($apiKey)) {
            return $this->getFallbackMessage($period, $mirrorData);
        }

        // Подготавливаем данные для AI
        $workspace = Workspace::find($workspaceId);
        $faithEnabled = $workspace?->faith_enabled ?? true;
        $aiInput = $this->buildAiInput($workspaceId, $period, $mirrorData, $faithEnabled);

        // Вызываем AI
        try {
            $response = $this->callOpenRouter($aiInput, $period);
            if ($response) {
                $response['is_fallback'] = false;
                $this->saveToCache($workspaceId, $period, $periodKey, $response);
                return $response;
            }
        } catch (\Throwable $e) {
            Log::warning('AiMirrorService: OpenRouter call failed', [
                'error' => $e->getMessage(),
                'workspace_id' => $workspaceId,
                'period' => $period,
            ]);
        }

        // Fallback
        return $this->getFallbackMessage($period, $mirrorData);
    }

    /**
     * Подготовить входные данные для AI
     */
    private function buildAiInput(int $workspaceId, string $period, array $mirrorData, bool $faithEnabled): array
    {
        $input = [
            'period' => $period,
            'faith_enabled' => $faithEnabled,
            'balance_index' => $mirrorData['balance_index'] ?? 0,
        ];

        $spheres = $mirrorData['spheres'] ?? [];

        // Контекст сфер — общий для всех периодов
        $input['spheres'] = array_map(fn($s) => [
            'name' => $s['name'],
            'tasks_done' => $s['tasks_done'],
            'tasks_total' => $s['tasks_total'],
            'days_without_attention' => $s['days_without_attention'],
        ], $spheres);

        // Пропущенные сферы (0 задач за период)
        $input['missing_spheres'] = array_map(fn($s) => [
            'name' => $s['name'],
            'days_without_attention' => $s['days_without_attention'] ?? null,
        ], $mirrorData['missing_spheres'] ?? []);

        // Цели и потоки — всегда передаём, для любого периода
        $input['goals'] = $this->buildGoalsContext($workspaceId);

        // Период-специфичные данные (данные лежат на корневом уровне mirrorData!)
        switch ($period) {
            case 'day':
                $input['summary'] = $mirrorData['summary'] ?? [];
                break;

            case 'week':
                $input['summary'] = $mirrorData['summary'] ?? [];
                $input['attention_distribution'] = array_map(fn($a) => [
                    'name' => $a['name'],
                    'percentage' => $a['percentage'],
                    'tasks_count' => $a['tasks_count'] ?? 0,
                ], $mirrorData['attention_distribution'] ?? []);
                $input['projects'] = array_map(fn($p) => [
                    'name' => $p['name'],
                    'sphere_name' => $p['sphere_name'] ?? null,
                    'goal_name' => $p['goal_name'] ?? null,
                    'done' => $p['done'],
                    'total' => $p['total'],
                    'is_stalled' => $p['is_stalled'] ?? false,
                    'days_since_activity' => $p['days_since_activity'] ?? null,
                ], $mirrorData['projects'] ?? []);
                break;

            case 'month':
                $input['planned_vs_done'] = $mirrorData['planned_vs_done'] ?? [];
                $input['sphere_trends'] = array_map(fn($t) => [
                    'name' => $t['name'],
                    'prev_pct' => $t['prev_pct'],
                    'current_pct' => $t['current_pct'],
                    'change' => $t['change'],
                    'direction' => $t['direction'] ?? 'stable',
                ], $mirrorData['sphere_trends'] ?? []);
                $input['stalled_projects'] = array_map(fn($p) => [
                    'name' => $p['name'],
                    'sphere_name' => $p['sphere_name'] ?? null,
                    'goal_name' => $p['goal_name'] ?? null,
                    'days_since_activity' => $p['days_since_activity'],
                    'done' => $p['done'] ?? 0,
                    'total' => $p['total'] ?? 0,
                ], $mirrorData['stalled_projects'] ?? []);
                $input['goals_at_risk'] = array_map(fn($g) => [
                    'name' => $g['name'],
                    'sphere_name' => $g['sphere_name'] ?? null,
                    'progress' => $g['progress'],
                    'expected_progress' => $g['expected_progress'],
                    'days_left' => $g['days_left'],
                ], $mirrorData['goals_at_risk'] ?? []);
                break;

            case 'year':
                $input['goals_summary'] = $mirrorData['goals_summary'] ?? [];
                $input['active_goals'] = array_map(fn($g) => [
                    'name' => $g['name'],
                    'sphere_name' => $g['sphere_name'] ?? null,
                    'progress' => $g['progress'],
                    'days_left' => $g['days_left'] ?? null,
                    'status' => $g['status'],
                ], $mirrorData['active_goals'] ?? []);
                $input['achieved_goals'] = array_map(fn($g) => [
                    'name' => $g['name'],
                    'sphere_name' => $g['sphere_name'] ?? null,
                    'completed_at' => $g['completed_at'] ?? null,
                ], $mirrorData['achieved_goals'] ?? []);
                $input['sphere_yearly_trends'] = array_map(fn($t) => [
                    'name' => $t['name'],
                    'trend' => $t['trend'],
                    'avg_attention' => $t['avg_attention'],
                ], $mirrorData['sphere_yearly_trends'] ?? []);
                break;
        }

        return $input;
    }

    /**
     * Собрать контекст целей и потоков для AI (для любого периода)
     */
    private function buildGoalsContext(int $workspaceId): array
    {
        $goals = Goal::where('workspace_id', $workspaceId)
            ->whereNotNull('life_sphere_id')
            ->with(['lifeSphere:id,name', 'projects' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get();

        if ($goals->isEmpty()) {
            return [];
        }

        return $goals->map(function ($goal) {
            $progress = $goal->progress;
            $daysLeft = $goal->deadline ? (int) Carbon::now()->diffInDays($goal->deadline, false) : null;

            // Определяем статус
            $status = 'active';
            if ($goal->status === 'completed') {
                $status = 'completed';
            } elseif ($goal->deadline) {
                $totalDays = $goal->created_at->diffInDays($goal->deadline);
                $elapsed = $goal->created_at->diffInDays(Carbon::now());
                $expectedProgress = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 100;

                if ($progress == 0 && $elapsed > 30) {
                    $status = 'stalled';
                } elseif ($progress < $expectedProgress * 0.5) {
                    $status = 'at_risk';
                } elseif ($progress < $expectedProgress * 0.8) {
                    $status = 'behind';
                } else {
                    $status = $progress > $expectedProgress ? 'ahead' : 'on_track';
                }
            }

            // Потоки цели
            $projects = $goal->projects->map(function ($project) {
                $totalTasks = $project->tasks()->count();
                $doneTasks = $project->tasks()->whereNotNull('completed_at')->count();
                $lastCompleted = $project->tasks()->max('completed_at');
                $daysSinceActivity = $lastCompleted
                    ? (int) Carbon::parse($lastCompleted)->diffInDays(Carbon::now())
                    : null;

                return [
                    'name' => $project->name,
                    'done' => $doneTasks,
                    'total' => $totalTasks,
                    'is_stalled' => ($daysSinceActivity === null || $daysSinceActivity >= 7) && $totalTasks > 0,
                    'days_since_activity' => $daysSinceActivity,
                ];
            })->filter(fn($p) => $p['total'] > 0)->values()->toArray();

            return [
                'name' => $goal->name,
                'sphere_name' => $goal->lifeSphere?->name,
                'status' => $status,
                'progress' => $progress,
                'days_left' => $daysLeft !== null ? max(0, $daysLeft) : null,
                'deadline' => $goal->deadline?->format('Y-m-d'),
                'projects' => $projects,
            ];
        })->toArray();
    }

    /**
     * Вызов OpenRouter API
     */
    private function callOpenRouter(array $aiInput, string $period): ?array
    {
        $baseUrl = config('openrouter.base_url');
        $apiKey = config('openrouter.api_key');
        $model = config('openrouter.model');

        $periodPrompt = $this->periodPrompts[$period] ?? $this->periodPrompts['day'];
        $userContent = $periodPrompt . "\n\n" . json_encode($aiInput, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $responseFormat = json_encode([
            'main_message' => 'string: 2-4 предложения, главное сообщение',
            'followup_message' => 'string|null: дополнительное сообщение, глубже',
            'mood' => 'string: positive|concerned|warning|serious|reflective',
            'recommendations' => [
                ['sphere' => 'string: название сферы', 'action' => 'string: конкретное действие', 'why' => 'string: причина'],
            ],
            'bible_verse' => ['text' => 'string', 'ref' => 'string'],
        ], JSON_UNESCAPED_UNICODE);

        $systemContent = $this->systemPrompt . "\n\nОтвечай строго в таком JSON-формате:\n" . $responseFormat;

        Log::info('AiMirrorService: sending request', [
            'model' => $model,
            'period' => $period,
            'input_keys' => array_keys($aiInput),
            'goals_count' => count($aiInput['goals'] ?? []),
        ]);

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemContent],
                    ['role' => 'user', 'content' => $userContent],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1024,
            ]);

        if (!$response->successful()) {
            Log::warning('AiMirrorService: OpenRouter returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return null;
        }

        // Парсим JSON из ответа AI
        return $this->parseAiResponse($content);
    }

    /**
     * Парсинг JSON из ответа AI (может содержать markdown code block)
     */
    private function parseAiResponse(string $content): ?array
    {
        // Убираем markdown code blocks если есть
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            Log::warning('AiMirrorService: Failed to parse AI response as JSON', [
                'content' => substr($content, 0, 500),
            ]);
            return null;
        }

        // Валидируем обязательные поля
        if (empty($parsed['main_message']) || empty($parsed['mood'])) {
            return null;
        }

        // Нормализуем mood
        $validMoods = ['positive', 'concerned', 'warning', 'serious', 'reflective'];
        if (!in_array($parsed['mood'], $validMoods)) {
            $parsed['mood'] = 'concerned';
        }

        return [
            'main_message' => $parsed['main_message'],
            'followup_message' => $parsed['followup_message'] ?? null,
            'mood' => $parsed['mood'],
            'recommendations' => $parsed['recommendations'] ?? [],
            'bible_verse' => $parsed['bible_verse'] ?? null,
        ];
    }

    /**
     * Получить из кеша
     */
    private function getCached(int $workspaceId, string $period, string $periodKey): ?array
    {
        $ttlMinutes = self::CACHE_TTL[$period] ?? 60;

        $cache = AiMirrorCache::where('workspace_id', $workspaceId)
            ->where('period', $period)
            ->where('period_key', $periodKey)
            ->where('generated_at', '>=', Carbon::now()->subMinutes($ttlMinutes))
            ->first();

        if ($cache) {
            $data = $cache->response_json;
            $data['is_fallback'] = false;
            $data['is_cached'] = true;
            return $data;
        }

        return null;
    }

    /**
     * Сохранить в кеш
     */
    private function saveToCache(int $workspaceId, string $period, string $periodKey, array $response): void
    {
        AiMirrorCache::updateOrCreate(
            [
                'workspace_id' => $workspaceId,
                'period' => $period,
                'period_key' => $periodKey,
            ],
            [
                'response_json' => $response,
                'generated_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Ключ периода для кеша
     */
    private function getPeriodKey(string $period): string
    {
        $now = Carbon::now();
        return match ($period) {
            'day' => $now->format('Y-m-d'),
            'week' => $now->startOfWeek()->format('Y-m-d'),
            'month' => $now->format('Y-m'),
            'year' => $now->format('Y'),
            default => $now->format('Y-m-d'),
        };
    }

    /**
     * Fallback-сообщение на основе правил
     */
    private function getFallbackMessage(string $period, array $data): array
    {
        $balanceIndex = $data['balance_index'] ?? 0;
        $missingSpheres = $data['missing_spheres'] ?? [];
        $missingCount = count($missingSpheres);
        $spheresActive = $data['summary']['spheres_active'] ?? 0;
        $spheresTotal = $data['summary']['spheres_total'] ?? 0;

        // Данные лежат на корневом уровне
        $completionRate = $data['summary']['completion_rate']
            ?? $data['planned_vs_done']['completion_rate']
            ?? 0;

        // Определяем mood
        $mood = 'concerned';
        if ($balanceIndex >= 70 && $completionRate >= 60) {
            $mood = 'positive';
        } elseif ($missingCount >= 4 || $balanceIndex < 30) {
            $mood = 'serious';
        } elseif ($missingCount >= 2 || $balanceIndex < 50) {
            $mood = 'warning';
        }
        if ($period === 'year') {
            $mood = 'reflective';
        }

        $mainMessage = match (true) {
            $spheresTotal === 0 => 'Создай сферы жизни в настройках пространства, чтобы увидеть аналитику.',
            $balanceIndex >= 70 => "Баланс {$balanceIndex}% — жизнь сбалансирована. {$spheresActive} из {$spheresTotal} сфер получают внимание.",
            $missingCount >= 4 => "{$missingCount} из {$spheresTotal} сфер без задач. Жизнь сужается до нескольких направлений.",
            $missingCount >= 2 => "{$missingCount} сферы без внимания. Баланс {$balanceIndex}%. Есть над чем поработать.",
            $completionRate >= 70 => "Отличная продуктивность — {$completionRate}% задач выполнено. Баланс: {$balanceIndex}%.",
            default => "Баланс {$balanceIndex}%. Задачи выполнены на {$completionRate}%.",
        };

        $recommendations = [];
        foreach (array_slice($missingSpheres, 0, 3) as $sphere) {
            $days = $sphere['days_without_attention'];
            $daysText = $days !== null ? "{$days} дн. без внимания" : 'Нет задач';
            $recommendations[] = [
                'sphere' => $sphere['name'],
                'sphere_id' => $sphere['id'],
                'sphere_color' => $sphere['color'],
                'action' => "Добавь хотя бы одну задачу в сферу «{$sphere['name']}»",
                'why' => $daysText,
            ];
        }

        return [
            'main_message' => $mainMessage,
            'followup_message' => null,
            'mood' => $mood,
            'recommendations' => $recommendations,
            'bible_verse' => null,
            'is_fallback' => true,
        ];
    }
}
