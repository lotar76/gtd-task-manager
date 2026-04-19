<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Contact;
use App\Models\ContactInvite;
use App\Models\User;
use App\Notifications\ContactInviteReceived;
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
     * Search system users by email or phone (for linking contacts).
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:3|max:255']);

        $q = $request->input('query');
        $userId = Auth::id();

        $users = User::where('id', '!=', $userId)
            ->where(function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'email')
            ->take(10)
            ->get()
            ->map(function ($user) use ($userId) {
                $alreadyLinked = Contact::where('owner_id', $userId)
                    ->where('contact_user_id', $user->id)
                    ->exists();
                $pendingInvite = ContactInvite::where('sender_id', $userId)
                    ->where('receiver_id', $user->id)
                    ->where('status', 'pending')
                    ->exists();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'already_linked' => $alreadyLinked,
                    'pending_invite' => $pendingInvite,
                ];
            });

        return response()->json($users);
    }

    /**
     * Send an invite to link contact with a system user.
     */
    public function sendInvite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'receiver_id' => 'required|exists:users,id',
        ]);

        $contact = Contact::findOrFail($validated['contact_id']);
        if ($contact->owner_id !== Auth::id()) {
            return ApiResponse::error('Нет доступа', 403);
        }

        if ($validated['receiver_id'] === Auth::id()) {
            return ApiResponse::error('Нельзя отправить приглашение самому себе', 422);
        }

        if ($contact->contact_user_id) {
            return ApiResponse::error('Контакт уже привязан к пользователю', 422);
        }

        // Check existing invite
        $existing = ContactInvite::where('sender_id', Auth::id())
            ->where('receiver_id', $validated['receiver_id'])
            ->where('contact_id', $contact->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return ApiResponse::error('Приглашение уже отправлено', 422);
        }

        $invite = ContactInvite::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'contact_id' => $contact->id,
        ]);

        $invite->load('sender');

        // Send in-app notification
        $receiver = User::find($validated['receiver_id']);
        $receiver->notify(new ContactInviteReceived($invite));

        return ApiResponse::success($invite, 'Приглашение отправлено', 201);
    }

    /**
     * List pending invites for current user.
     */
    public function pendingInvites(): JsonResponse
    {
        $invites = ContactInvite::where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->with('sender:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($invites);
    }

    /**
     * Accept or decline an invite.
     */
    public function respondInvite(Request $request, ContactInvite $invite): JsonResponse
    {
        if ($invite->receiver_id !== Auth::id()) {
            return ApiResponse::error('Нет доступа', 403);
        }

        if ($invite->status !== 'pending') {
            return ApiResponse::error('Приглашение уже обработано', 422);
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,decline',
        ]);

        if ($validated['action'] === 'accept') {
            $invite->update(['status' => 'accepted']);

            // Link the contact to this user
            $contact = $invite->contact;
            $contact->update([
                'contact_user_id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ]);

            // Create mutual contact (receiver adds sender)
            Contact::firstOrCreate(
                ['owner_id' => Auth::id(), 'contact_user_id' => $invite->sender_id],
                ['name' => $invite->sender->name, 'email' => $invite->sender->email],
            );

            return ApiResponse::success($invite, 'Приглашение принято');
        }

        $invite->update(['status' => 'declined']);

        return ApiResponse::success($invite, 'Приглашение отклонено');
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
