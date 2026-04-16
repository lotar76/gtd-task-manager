<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(): JsonResponse
    {
        $contacts = Contact::where('owner_id', Auth::id())
            ->withCount([
                'tasks as active_tasks_count' => fn ($q) => $q->whereNull('completed_at'),
                'tasks as completed_tasks_count' => fn ($q) => $q->whereNotNull('completed_at'),
            ])
            ->orderByDesc('is_favorite')
            ->orderBy('name')
            ->get();

        return ApiResponse::success($contacts, 'Список контактов получен');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules(isUpdate: false, isLinked: false));
        $validated['owner_id'] = Auth::id();

        $contact = Contact::create($validated);

        return ApiResponse::success($contact, 'Контакт создан', 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        if ($contact->owner_id !== Auth::id()) {
            return ApiResponse::error('Нет доступа', 403);
        }

        $contact->load(['tasks' => function ($query) {
            $query->whereNull('completed_at')->orderBy('created_at', 'desc');
        }]);

        return ApiResponse::success($contact, 'Контакт получен');
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        if ($contact->owner_id !== Auth::id()) {
            return ApiResponse::error('Нет доступа', 403);
        }

        $validated = $request->validate($this->rules(isUpdate: true, isLinked: $contact->isLinkedUser()));

        // Для связанных с пользователем контактов: базовые данные (имя/email/телефон) —
        // это данные другого пользователя, их менять нельзя. Только свои персональные поля.
        if ($contact->isLinkedUser()) {
            unset($validated['name'], $validated['email'], $validated['phone']);
        }

        $contact->update($validated);

        return ApiResponse::success($contact, 'Контакт обновлён');
    }

    public function destroy(Contact $contact): JsonResponse
    {
        if ($contact->owner_id !== Auth::id()) {
            return ApiResponse::error('Нет доступа', 403);
        }

        $contact->delete();

        return ApiResponse::success(null, 'Контакт удалён');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(bool $isUpdate, bool $isLinked): array
    {
        $nameRule = $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255';

        return [
            'name' => $isLinked ? 'sometimes|prohibited' : $nameRule,
            'email' => $isLinked ? 'sometimes|prohibited' : 'nullable|email|max:255',
            'phone' => $isLinked ? 'sometimes|prohibited' : 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_favorite' => 'sometimes|boolean',
            'contact_type' => ['sometimes', Rule::in(Contact::TYPES)],
            'specialization' => 'nullable|string|max:255',
            'personal_phone' => 'nullable|string|max:50',
            'personal_email' => 'nullable|email|max:255',
            'messengers' => 'nullable|array',
            'messengers.*' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ];
    }
}
