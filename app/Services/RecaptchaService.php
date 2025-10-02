<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(string $token, string $action = 'login'): bool
    {
        $secret = config('services.recaptcha.secret_key');
        if (!$secret || !$token) {
            return true; // allow in dev if not configured
        }
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
        ]);
        if (!$response->ok()) {
            return false;
        }
        $data = $response->json();
        if (!($data['success'] ?? false)) {
            return false;
        }
        // For simplicity, don't check action/score strictly in student project
        return true;
    }
}


