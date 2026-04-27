<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Mail\OtpCodeMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
	public function sendOTP(User $user, string $context = 'login', string $channel = 'sms'): ?OtpCode
	{
		$channel = in_array($channel, ['sms', 'email'], true) ? $channel : 'sms';

		try {
			$expires   = now('Asia/Manila')->addMinutes(5);
			$code      = (string) random_int(100000, 999999);

			$message   = 'Your one-time passcode is ' . $code;

			if ($channel === 'sms') {
				if (!$user->phone_number) {
					Log::warning("User {$user->first_name} {$user->last_name} has no phone number.");
					return null;
				}

				// Configure these in config/services.php and .env (see note below)
				$apiKey    = config('services.iprog_sms.api_key');
				// $postQuery = 'https://sms.iprogtech.com/api/v1/sms_messages?api_token='. $apiKey .'&message='. $message .'&phone_number=' . $user->phone_number . '&sms_provider=2';

				$postQuery = 'https://www.iprogsms.com/api/v1/sms_messages?api_token='. $apiKey .'&message='. $message .'&phone_number='. $user->phone_number .'&sms_provider=2';


				$response = Http::asForm()->post($postQuery);

				if ($response->failed()) {
					Log::error('Sending OTP failed', [
						'status' => $response->status(),
						'body'   => $response->body(),
					]);
					return null;
				}
			} else {
				// email channel
				if (!$user->email) {
					Log::warning("User {$user->first_name} {$user->last_name} has no email.");
					return null;
				}
				Mail::mailer(config('mail.otp_mailer', 'resend'))
					->to($user->email)
					->send(new OtpCodeMail($user, $code, $context));
			}

			// Persist the OTP
			$otp = OtpCode::create([
				'user_id'      => $user->id,
				// Reuse column to store recipient (phone for sms, email for email) for now
				'phone_number' => $channel === 'sms' ? (string) $user->phone_number : (string) $user->email,
				'code'         => $code,  // store as string to avoid leading-zero issues
				'expires_at'   => $expires,
			]);

			return $otp;
		} catch (\Throwable $e) {
			Log::error('OTP send failed', ['error' => $e->getMessage()]);
			return null;
		}
	}

	public function verifyOtp(User $user, string $code, ?string $context = null): bool
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
