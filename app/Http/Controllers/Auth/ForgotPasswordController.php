<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLink(Request $request)
    {
        // Validate email format
        $request->validate([
            'email' => 'required|email|max:255'
        ]);

        $email = $request->input('email');

        try {
            // Always return neutral response to prevent user enumeration
            // Send reset link using Laravel's Password Broker
            $status = Password::sendResetLink(['email' => $email]);

            // Log for debugging but never log the token
            Log::info('Password reset requested', ['email' => $email]);

            // Always return the same response regardless of email existence
            return response()->json([
                'ok' => true,
                'message' => 'Jika email terdaftar, kami sudah mengirim link reset.'
            ]);

        } catch (\Exception $e) {
            // Log error for debugging but don't expose to user
            Log::error('Password reset error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            // Still return neutral response
            return response()->json([
                'ok' => true,
                'message' => 'Jika email terdaftar, kami sudah mengirim link reset.'
            ]);
        }
    }
}