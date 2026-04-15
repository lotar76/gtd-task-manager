<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index(): JsonResponse
    {
        $contacts = Contact::where('owner_id', Auth::id())
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return ApiResponse::success($contacts, 'Список контактов получен');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

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

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

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
}
