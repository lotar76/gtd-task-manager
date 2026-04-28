<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'yandex', 'mailru'];

    public function __construct(
        private readonly WorkspaceService $workspaceService
    ) {
    }

    /**
     * Редирект на провайдер OAuth.
     */
    public function redirect(string $provider): RedirectResponse|JsonResponse
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            return response()->json(['message' => 'Unsupported provider'], 422);
        }

        // Mail.ru требует параметр state даже в stateless-режиме
        if ($provider === 'mailru') {
            return Socialite::driver($provider)
                ->with(['state' => bin2hex(random_bytes(16))])
                ->redirect();
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Обработка callback от провайдера OAuth.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            return redirect(config('app.frontend_url', '/') . '/login?error=unsupported_provider');
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect(config('app.frontend_url', '/') . '/login?error=oauth_failed');
        }

        $user = $this->findOrCreateUser($socialUser, $provider);

        $token = $user->createToken('auth_token')->plainTextToken;

        $frontendUrl = config('app.frontend_url', '');

        return redirect($frontendUrl . '/auth/callback?token=' . urlencode($token));
    }

    /**
     * Найти или создать пользователя по данным OAuth.
     */
    private function findOrCreateUser($socialUser, string $provider): User
    {
        // 1. Ищем по provider + provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            $user->update([
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
            ]);
            return $user;
        }

        // 2. Ищем по email (привязка существующего аккаунта)
        $email = $socialUser->getEmail();
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                ]);
                return $user;
            }
        }

        // 3. Создаём нового пользователя
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $email,
            'password' => null,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
        ]);

        $user->assignRole('user');

        $this->workspaceService->createPersonalWorkspace($user->id, $user->name);

        return $user;
    }
}
