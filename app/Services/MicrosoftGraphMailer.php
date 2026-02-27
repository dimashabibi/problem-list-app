<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class MicrosoftGraphMailer
{
    public static function ensureAccessToken(): ?string
    {
        $token = Session::get('ms_access_token');
        $exp = Session::get('ms_token_expires_at');
        if ($token && $exp && now()->lt($exp)) {
            return $token;
        }
        $refresh = Session::get('ms_refresh_token');
        if (!$refresh) return null;

        $tenant = config('services.microsoft.tenant') ?? config('services.microsoft.tenant_id');
        $clientId = config('services.microsoft.client_id');
        $clientSecret = config('services.microsoft.client_secret');
        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        $resp = Http::asForm()->post($tokenUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh,
            'scope' => 'offline_access Mail.Send',
        ]);

        if (!$resp->ok()) return null;
        $data = $resp->json();
        $newToken = $data['access_token'] ?? null;
        $newRefresh = $data['refresh_token'] ?? $refresh;
        $expiresIn = $data['expires_in'] ?? 3600;
        if (!$newToken) return null;

        Session::put('ms_access_token', $newToken);
        Session::put('ms_refresh_token', $newRefresh);
        Session::put('ms_token_expires_at', now()->addSeconds($expiresIn)->toDateTimeString());
        return $newToken;
    }

    public static function sendMail(string $token, string $to, ?string $cc, string $subject, string $htmlMessage, ?array $attachments = null)
    {
        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $htmlMessage,
                ],
                'toRecipients' => [
                    ['emailAddress' => ['address' => $to]],
                ],
            ],
            'saveToSentItems' => true,
        ];

        if ($cc) {
            $payload['message']['ccRecipients'] = [
                ['emailAddress' => ['address' => $cc]],
            ];
        }

        if ($attachments && count($attachments) > 0) {
            $payload['message']['attachments'] = [];
            foreach ($attachments as $att) {
                $payload['message']['attachments'][] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $att['name'],
                    'contentType' => $att['contentType'],
                    'contentBytes' => base64_encode($att['bytes']),
                ];
            }
        }

        return Http::withToken($token)
            ->post('https://graph.microsoft.com/v1.0/me/sendMail', $payload);
    }
}
