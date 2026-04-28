<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    /**
     * Подтвердить email по коду.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email уже подтверждён');
        }

        $record = DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return ApiResponse::error('Неверный или просроченный код', 422);
        }

        $user->markEmailAsVerified();

        DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->delete();

        return ApiResponse::success(null, 'Email подтверждён');
    }

    /**
     * Отправить код повторно.
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email уже подтверждён');
        }

        // Rate limit: не чаще раза в 60 секунд
        $lastCode = DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        if ($lastCode && now()->diffInSeconds($lastCode->created_at) < 60) {
            return ApiResponse::error('Подождите минуту перед повторной отправкой', 429);
        }

        $this->sendVerificationCode($user);

        return ApiResponse::success(null, 'Код отправлен на ' . $user->email);
    }

    /**
     * Сгенерировать и отправить код верификации.
     */
    public static function sendVerificationCode($user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('email_verification_codes')
            ->where('user_id', $user->id)
            ->delete();

        DB::table('email_verification_codes')->insert([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail(
            code: $code,
            userName: $user->name,
        ));
    }
}
