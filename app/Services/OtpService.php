<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class OtpService
{
	public function generateAndSend(User $user, string $purpose = 'login'): OtpCode
	{
		$sid = config('services.twilio.sid');
		$aid = config('services.twilio.aid');
		$token = config('services.twilio.token');
		$from = config('services.twilio.from');

		if ($aid && $sid && $token && $from && $user->phone) {
			try {
				$expiresAt = now()->addMinutes(5);
				$twilio = new TwilioClient($aid, $token);

				$verification = $twilio->verify->v2->services($sid)
					->verifications
					->create($user->phone, "sms");

				$otp = OtpCode::create([
					'user_id' => $user->id,
					'phone' => $user->phone ?? '',
					'purpose' => $purpose,
					'code' => $verification->code,
					'expires_at' => $expiresAt,
				]);
			} catch (\Throwable $e) {
				Log::warning('Twilio send failed: ' . $e->getMessage());
			}
		}

		return $otp;
	}

	public function verify(User $user, string $code, string $purpose = 'login'): bool
	{
		$otp = OtpCode::where('user_id', $user->id)
			->where('purpose', $purpose)
			->where('used', false)
			->where('expires_at', '>=', now())
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
