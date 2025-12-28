<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public User $user,
		public string $code,
		public string $context = 'login'
	) {}

	public function build(): self
	{
		return $this
			->subject('Your One-Time Passcode (OTP)')
			->view('emails.otp', [
				'user' => $this->user,
				'code' => $this->code,
				'context' => $this->context,
			]);
	}
}


