<?php

namespace App\Enums;

enum UserType: string
{
	case ADMIN = 'admin';
	case VOTER = 'voter';
	case SYSTEM_ADMIN = 'system-admin';

	public const DEFAULT = self::VOTER->value;

	public function getLabel(): ?string
	{
		return $this->name;
	}
}
