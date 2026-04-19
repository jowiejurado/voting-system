<?php

namespace App\Mail;

use App\Models\Election;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VoteReceiptMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public User $user,
		public Election $election,
		/** @var list<array{position: string, choices: list<string>}> */
		public array $receiptRows,
		public string $submittedAtDisplay,
	) {}

	public function build(): self
	{
		$title = (string) $this->election->title;

		return $this
			->subject('Vote receipt — '.$title)
			->view('emails.vote-receipt', [
				'user' => $this->user,
				'election' => $this->election,
				'receiptRows' => $this->receiptRows,
				'submittedAtDisplay' => $this->submittedAtDisplay,
				'logoUrl' => asset('logo.png'),
			]);
	}
}
