<?php

namespace App\Support;

final class LoginVerificationLimits
{
	public const FACE_ATTEMPTS = 5;

	public const OTP_ATTEMPTS = 3;
}
