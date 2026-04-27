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
	private const IPROG_SMS_URL = 'https://www.iprogsms.com/api/v1/sms_messages';

	public function sendOTP(User $user, string $context = 'login', string $channel = 'sms'): ?OtpCode
	{
		$channel = in_array($channel, ['sms', 'email'], true) ? $channel : 'sms';

		try {
			$expires   = now('Asia/Manila')->addMinutes(5);
			$code      = (string) random_int(100000, 999999);

			$message   = 'Your one-time passcode is ' . $code;

			if ($channel === 'sms') {
				if (! $user->phone_number) {
					Log::warning("User {$user->first_name} {$user->last_name} has no phone number.");

					return null;
				}

				$apiKey = config('services.iprog_sms.api_key');
				if (! is_string($apiKey) || $apiKey === '') {
					Log::error('SMS OTP skipped: IPROG_SMS_API_TOKEN is not set.');

					return null;
				}

				$phoneForApi = $this->canonicalPhoneForSms((string) $user->phone_number);
				if ($phoneForApi === '') {
					Log::warning('SMS OTP skipped: phone number has no digits.', ['user_id' => $user->id]);

					return null;
				}

				$payload = [
					'api_token' => $apiKey,
					'phone_number' => $phoneForApi,
					'message' => $message,
				];
				$provider = config('services.iprog_sms.sms_provider');
				if ($provider !== null && $provider !== '') {
					$payload['sms_provider'] = (int) $provider;
				}

				$response = Http::acceptJson()
					->timeout(15)
					->asForm()
					->post(self::IPROG_SMS_URL, $payload);

				if ($response->failed()) {
					Log::error('IProg SMS HTTP error', [
						'status' => $response->status(),
						'body' => $response->body(),
					]);

					return null;
				}

				$body = $response->json();
				if (! is_array($body) || (int) ($body['status'] ?? 0) !== 200) {
					Log::error('IProg SMS API rejected the message', [
						'response' => $body,
						'http_status' => $response->status(),
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

			// Persist the OTP (SMS uses same canonical format as sent to the gateway for verify matching)
			$otp = OtpCode::create([
				'user_id'      => $user->id,
				// Reuse column to store recipient (phone for sms, email for email) for now
				'phone_number' => $channel === 'sms'
					? $this->canonicalPhoneForSms((string) $user->phone_number)
					: (string) $user->email,
				'code'         => $code,  // store as string to avoid leading-zero issues
				'expires_at'   => $expires,
			]);

			return $otp;
		} catch (\Throwable $e) {
			Log::error('OTP send failed', ['error' => $e->getMessage()]);
			return null;
		}
	}

	/**
	 * @param  string|null  $deliveryChannel  "sms"|"email" when verifying login OTP (must match the channel the code was sent on); null uses latest unused across channels.
	 */
	public function verifyOtp(User $user, string $code, ?string $context = null, ?string $deliveryChannel = null): bool
	{
		$normalized = preg_replace('/\D+/', '', trim($code));
		if ($normalized === '') {
			return false;
		}

		$query = OtpCode::query()
			->where('user_id', $user->id)
			->where('used', false)
			->where('expires_at', '>=', now('Asia/Manila'));

		if (in_array($deliveryChannel, ['sms', 'email'], true)) {
			$recipient = $deliveryChannel === 'email'
				? strtolower((string) $user->email)
				: $this->canonicalPhoneForSms((string) $user->phone_number);

			$otp = $query->orderByDesc('id')
				->get()
				->first(function (OtpCode $row) use ($recipient, $deliveryChannel) {
					$stored = $deliveryChannel === 'email'
						? strtolower((string) $row->phone_number)
						: $this->canonicalPhoneForSms((string) $row->phone_number);

					return $stored === $recipient;
				});
		} else {
			$otp = $query->latest('id')->first();
		}

		if (! $otp) {
			return false;
		}

		$storedCode = (string) $otp->code;
		if (strlen($normalized) !== strlen($storedCode)) {
			return false;
		}

		$valid = hash_equals($storedCode, $normalized);

		if ($valid) {
			$otp->used = true;
			$otp->save();
		}

		return $valid;
	}

	/**
	 * Normalize stored / API phone numbers (Philippines: 09… / 9… → 639… digits only).
	 */
	private function canonicalPhoneForSms(string $raw): string
	{
		$digits = preg_replace('/\D+/', '', $raw) ?? '';

		if ($digits === '') {
			return '';
		}

		if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
			return '63'.substr($digits, 1);
		}

		if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
			return '63'.$digits;
		}

		if (str_starts_with($digits, '63')) {
			return strlen($digits) > 12 ? substr($digits, 0, 12) : $digits;
		}

		return $digits;
	}
}
