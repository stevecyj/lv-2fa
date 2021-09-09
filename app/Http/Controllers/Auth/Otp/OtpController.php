<?php

namespace App\Http\Controllers\Auth\Otp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google2FA;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $user->update([
            'google2fa_secret' => $secret = Google2FA::generateSecretKey()
        ]);

        return response(
            Google2FA::getQRCodeInline(
                'codecourse',
                $user->email,
                $user->google2fa_secret
            )
        );
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'otp' => 'required',
        ]);

        $user = $request->user();

        if (!Google2FA::verifyKey($user->google2fa_secret, $request->otp)) {
            return response(null, 401);
        }

        $user->update([
            'google2fa_enabled' => true,
        ]);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'password' => [
                'required',
                function ($attribute, $value, $fail) use ($request, $user) {
                    if (!Hash::check($request->password, $user->password)) {
                        $fail('Incorrect password');
                    }
                }
            ]
        ]);

        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ]);
    }
}
