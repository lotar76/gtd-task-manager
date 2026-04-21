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
- assignee_ids (array) — массив ID контактов-исполнителей. Если в тексте кто-то должен что-то сделать ("пусть Настя", "поручить Ивану", "Петя сделает") — это исполнитель
- watcher_ids (array) — массив ID контактов-наблюдателей. Если кого-то просто упоминают в контексте, но задача не на них
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

            $validIds = collect($contacts)->pluck('id')->all();

            if (!empty($parsed['assignee_ids']) && is_array($parsed['assignee_ids'])) {
                $result['assignee_ids'] = array_values(array_intersect($parsed['assignee_ids'], $validIds));
            }

            if (!empty($parsed['watcher_ids']) && is_array($parsed['watcher_ids'])) {
                $result['watcher_ids'] = array_values(array_intersect($parsed['watcher_ids'], $validIds));
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

    public function transcribe(string $audioBase64, string $mimeType = 'audio/webm'): ?string
    {
        $apiKey = config('openrouter.api_key');
        $baseUrl = config('openrouter.base_url', 'https://openrouter.ai/api/v1');

        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/chat/completions", [
                    'model' => 'google/gemini-2.0-flash-001',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Транскрибируй это аудио. Верни ТОЛЬКО текст того что сказано, без пояснений, кавычек и форматирования. Если речь на русском — пиши на русском.',
                                ],
                                [
                                    'type' => 'input_audio',
                                    'input_audio' => [
                                        'data' => $audioBase64,
                                        'format' => $this->audioFormat($mimeType),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 1024,
                ]);

            if (!$response->successful()) {
                Log::warning('TaskParserService: Transcription error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');
            return $content ? trim($content) : null;
        } catch (\Exception $e) {
            Log::error('TaskParserService: Transcription exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function audioFormat(string $mimeType): string
    {
        return match (true) {
            str_contains($mimeType, 'wav') => 'wav',
            str_contains($mimeType, 'mp3') || str_contains($mimeType, 'mpeg') => 'mp3',
            str_contains($mimeType, 'ogg') => 'ogg',
            str_contains($mimeType, 'mp4') || str_contains($mimeType, 'm4a') => 'mp3',
            default => 'wav',
        };
    }
}
