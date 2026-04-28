<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'yandex'];

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

        // Редирект на фронтенд с токеном
        $frontendUrl = config('app.frontend_url', '');

        return redirect($frontendUrl . '/auth/callback?token=' . $token);
    }

    /**
     * Страница с Telegram Login Widget.
     */
    public function telegramRedirect(): \Illuminate\Http\Response
    {
        $botUsername = config('services.telegram.bot_username');
        $callbackUrl = url('/api/v1/auth/telegram/callback');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Вход через Telegram</title>
    <style>
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; background:#f3f4f6; font-family:sans-serif; }
        .container { text-align:center; padding:2rem; background:white; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,.1); }
        h2 { color:#1f2937; margin-bottom:1.5rem; }
        p { color:#6b7280; margin-top:1rem; font-size:14px; }
        a { color:#2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Вход через Telegram</h2>
        <script async src="https://telegram.org/js/telegram-widget.js?22"
            data-telegram-login="{$botUsername}"
            data-size="large"
            data-auth-url="{$callbackUrl}"
            data-request-access="write">
        </script>
        <p><a href="javascript:history.back()">Назад</a></p>
    </div>
</body>
</html>
HTML;

        return response($html);
    }

    /**
     * Обработка Telegram Login Widget.
     */
    public function telegramCallback(Request $request): RedirectResponse
    {
        $data = $request->only(['id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash']);

        if (!$this->verifyTelegramAuth($data)) {
            return redirect(config('app.frontend_url', '/') . '/login?error=telegram_auth_failed');
        }

        // Проверяем что auth_date не старше 1 часа
        if ((time() - (int) $data['auth_date']) > 3600) {
            return redirect(config('app.frontend_url', '/') . '/login?error=telegram_auth_expired');
        }

        $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        if (empty($name)) {
            $name = $data['username'] ?? 'Telegram User';
        }

        $user = User::where('provider', 'telegram')
            ->where('provider_id', $data['id'])
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => null,
                'password' => null,
                'provider' => 'telegram',
                'provider_id' => (string) $data['id'],
                'avatar' => $data['photo_url'] ?? null,
            ]);

            $user->assignRole('user');

            $this->workspaceService->createPersonalWorkspace($user->id, $user->name);
        } else {
            $user->update([
                'name' => $name,
                'avatar' => $data['photo_url'] ?? $user->avatar,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $frontendUrl = config('app.frontend_url', '');

        return redirect($frontendUrl . '/auth/callback?token=' . $token);
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

    /**
     * Верификация данных от Telegram Login Widget.
     */
    private function verifyTelegramAuth(array $data): bool
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) {
            return false;
        }

        $checkHash = $data['hash'] ?? null;
        if (!$checkHash) {
            return false;
        }

        unset($data['hash']);

        // Сортируем и формируем строку
        ksort($data);
        $dataCheckString = collect($data)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $checkHash);
    }
}
