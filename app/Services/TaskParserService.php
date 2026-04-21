<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaskParserService
{
    public function parse(string $text, array $contacts, array $lifeSpheres = []): array
    {
        $apiKey = config('openrouter.api_key');
        $baseUrl = config('openrouter.base_url', 'https://openrouter.ai/api/v1');
        $model = config('openrouter.model', 'anthropic/claude-sonnet-4.5');

        if (!$apiKey) {
            Log::warning('TaskParserService: API key not configured');
            return ['title' => $text];
        }

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $contactsList = collect($contacts)
            ->map(fn ($c) => "ID:{$c['id']} — {$c['name']}")
            ->implode("\n");

        $spheresList = collect($lifeSpheres)
            ->map(fn ($s) => "ID:{$s['id']} — {$s['name']}")
            ->implode("\n");

        $systemPrompt = <<<PROMPT
Ты — помощник для парсинга задач. Пользователь описывает задачу свободным текстом на русском языке.
Извлеки структурированные данные и верни ТОЛЬКО валидный JSON (без markdown, без пояснений).

Поля:
- title (string, обязательное) — краткое название задачи
- description (string|null) — описание, детали, если есть
- due_date (string|null) — дата в формате YYYY-MM-DD. "сегодня" = {$today}, "завтра" = {$tomorrow}. Для относительных дат ("через 3 дня", "в пятницу") вычисли от {$today}
- priority (string|null) — low/medium/high/urgent. Проставляй ТОЛЬКО если пользователь явно говорит о срочности/важности. Если не упомянуто — ставь null, не угадывай
- status (string|null) — определи по контексту: если дата сегодня → "today", завтра → "tomorrow", есть дата в будущем → "scheduled", иначе → "inbox"
- contact_ids (array) — массив ID контактов из списка ниже, если упоминаются по имени. Если не упоминаются — пустой массив
- life_sphere_id (int|null) — ID сферы жизни из списка ниже, если задача явно относится к одной из них. Если не можешь уверенно определить — ставь null, не угадывай
- checklist (array|null) — если задача содержит несколько шагов/пунктов/перечислений, разбей в массив строк. Если задача простая и без перечислений — не создавай чеклист

Список контактов пользователя:
{$contactsList}

Сферы жизни пользователя:
{$spheresList}

Главное правило: если не уверен в значении поля — ставь null. Лучше не заполнить, чем заполнить неправильно.
PROMPT;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $text],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 512,
                ]);

            if (!$response->successful()) {
                Log::warning('TaskParserService: OpenRouter error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['title' => $text];
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                return ['title' => $text];
            }

            // Strip markdown code blocks if present
            $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
            $content = preg_replace('/```\s*$/m', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (!is_array($parsed) || empty($parsed['title'])) {
                return ['title' => $text];
            }

            // Validate and filter fields
            $result = ['title' => substr($parsed['title'], 0, 255)];

            if (!empty($parsed['description'])) {
                $result['description'] = $parsed['description'];
            }

            if (!empty($parsed['due_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $parsed['due_date'])) {
                $result['due_date'] = $parsed['due_date'];
            }

            if (!empty($parsed['priority']) && in_array($parsed['priority'], ['low', 'medium', 'high', 'urgent'])) {
                $result['priority'] = $parsed['priority'];
            }

            if (!empty($parsed['status']) && in_array($parsed['status'], ['inbox', 'today', 'tomorrow', 'scheduled'])) {
                $result['status'] = $parsed['status'];
            }

            if (!empty($parsed['life_sphere_id']) && is_int($parsed['life_sphere_id'])) {
                $validSphereIds = collect($lifeSpheres)->pluck('id')->all();
                if (in_array($parsed['life_sphere_id'], $validSphereIds)) {
                    $result['life_sphere_id'] = $parsed['life_sphere_id'];
                }
            }

            if (!empty($parsed['contact_ids']) && is_array($parsed['contact_ids'])) {
                $validIds = collect($contacts)->pluck('id')->all();
                $result['contact_ids'] = array_values(array_intersect($parsed['contact_ids'], $validIds));
            }

            if (!empty($parsed['checklist']) && is_array($parsed['checklist'])) {
                $result['checklist'] = array_values(array_filter(
                    array_map(fn ($item) => is_string($item) ? substr(trim($item), 0, 255) : null, $parsed['checklist'])
                ));
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('TaskParserService: Exception', ['message' => $e->getMessage()]);
            return ['title' => $text];
        }
    }
}
