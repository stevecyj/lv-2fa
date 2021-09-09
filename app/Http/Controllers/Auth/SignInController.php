<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Google2FA;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignInController extends Controller
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function __invoke(Request $request)
    {
        if (!$token = auth()->attempt($request->only('email', 'password'))) {
            return response([
                'reason' => 'INCORRECT_CREDENTIALS'
            ], 401);
        }

        $user = $request->user();

        if ($user->google2fa_enabled && !$request->otp) {
            return response([
                'reason' => 'REQUIRES_OTP'
            ], 401);
        }

        if ($user->google2fa_enabled && $request->otp) {
            if (!Google2FA::verifyKey($user->google2fa_secret, $request->otp)) {
                return response([
                    'reason' => 'INCORRECT_OTP'
                ], 401);
            }
        }

        return response()->json([
            'token' => $token
        ]);
    }
}
