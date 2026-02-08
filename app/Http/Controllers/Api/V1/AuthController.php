<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private readonly WorkspaceService $workspaceService
    ) {
    }

    /**
     * Регистрация нового пользователя.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Назначение роли по умолчанию
        $user->assignRole('user');

        // Создание персонального workspace
        $personalWorkspace = $this->workspaceService->createPersonalWorkspace(
            $user->id,
            $user->name
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
            'personal_workspace' => $personalWorkspace,
        ], 'User registered successfully', 201);
    }

    /**
     * Вход пользователя.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * Выход пользователя.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Получение информации о текущем пользователе.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Обновление профиля пользователя.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $oldName = $user->name;
        $user->update(['name' => $request->name]);

        // Обновить имя личного workspace
        $user->ownedWorkspaces()
            ->where('name', $oldName . ' - Личные задачи')
            ->update(['name' => $request->name . ' - Личные задачи']);

        return ApiResponse::success([
            'user' => $user->fresh(),
        ], 'Profile updated successfully');
    }

    /**
     * Изменение пароля пользователя.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error('Текущий пароль указан неверно', 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return ApiResponse::success(null, 'Пароль успешно изменён');
    }
}

