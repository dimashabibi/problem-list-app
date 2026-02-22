<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     * Show the reset password form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request)
    {
        $token = $request->route('token') ?: $request->query('token');
        $email = $request->query('email');

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        // Validate input with strong password requirements
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => [
                'required',
                'confirmed',
                'string',
                PasswordRule::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ]
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'token' => $request->input('token')
        ];

        try {
            // Attempt to reset the password using Laravel's Password Broker
            $status = Password::reset($credentials, function ($user, $password) {
                // Update password with proper hashing
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                // Fire the password reset event
                event(new PasswordReset($user));

                // Log the password reset (without sensitive data)
                Log::info('Password reset successful', [
                    'user_id' => $user->id_user,
                    'email' => $user->email
                ]);

                // Send password change notification
                $user->notify(new \App\Notifications\PasswordChangedNotification());
            });

            if ($status === Password::PASSWORD_RESET) {
                // Find the user to perform session management
                $user = User::where('email', $credentials['email'])->first();

                if ($user) {
                    // Regenerate session to prevent session fixation
                    session()->regenerate();

                    // Logout other devices (requires the new password)
                    Auth::logoutOtherDevices($credentials['password']);
                }

                return response()->json([
                    'ok' => true,
                    'message' => 'Password berhasil diubah. Silakan login kembali.'
                ]);
            }

            // Invalid token or other error
            return response()->json([
                'ok' => false,
                'message' => 'Token reset password tidak valid atau sudah kadaluarsa.'
            ], 422);

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Password reset error', [
                'email' => $credentials['email'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan saat mereset password. Silakan coba lagi.'
            ], 422);
        }
    }
}