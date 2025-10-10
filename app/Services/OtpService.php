<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
	public function sendOTP(User $user): ?OtpCode
	{
		if (!$user->phone_number) {
			Log::warning("User {$user->first_name} {$user->last_name} has no phone number.");
			return null;
		}

		try {
			// Configure these in config/services.php and .env (see note below)
			$apiKey    = config('services.iprog_sms.api_key');

			$expires   = now('Asia/Manila')->addMinutes(5);
			$code      = (string) random_int(100000, 999999);   // safer than rand()
			// $message   = 'PASEI - Secured Online Voting System: Your one-time passcode is ' . $code . '. Valid in 5 minutes. Please do not share this code to anyone. If you didn`t request this, please ignore this message.';
			$message   = 'PASEI - Secured Online Voting System: Your one-time passcode is ' . $code;

			$postQuery = 'https://sms.iprogtech.com/api/v1/sms_messages?api_token='. $apiKey .'&message='. $message .'&phone_number=' . $user->phone_number . '&sms_provider=2';

			$response = Http::asForm()->post($postQuery);

			if ($response->failed()) {
				Log::error('Sending OTP failed', [
					'status' => $response->status(),
					'body'   => $response->body(),
				]);
				return null;
			}

			// Persist the OTP
			$otp = OtpCode::create([
				'user_id'      => $user->id,
				'phone_number' => $user->phone_number,
				'code'         => $code,  // store as string to avoid leading-zero issues
				'expires_at'   => $expires,
			]);

			return $otp;
		} catch (\Throwable $e) {
			Log::error('OTP send failed', ['error' => $e->getMessage()]);
			return null;
		}
	}

	public function verifyOtp(User $user, string $code): bool
	{
		$otp = OtpCode::where('user_id', $user->id)
			->where('used', false)
			// ->where('expires_at', '>=', now())
			->latest()
			->first();

		if (!$otp) {
			return false;
		}

		$valid = hash_equals($otp->code, trim($code));

		if ($valid) {
			$otp->used = true;
			$otp->save();
		}

		return $valid;
	}
}
