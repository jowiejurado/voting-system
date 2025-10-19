<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory, Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'admin_id',
		'member_id',
		'last_name',
		'first_name',
		'password',
		'phone_number',
		'organization_name',
		'type',
		'has_voted',
		'is_active',
		'last_signed_in',
		'last_signed_out',
		'face_descriptor'
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var list<string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'face_descriptor' => 'array',
			'password' => 'hashed',
			'has_voted' => 'boolean',
			'is_active' => 'boolean',
			'last_signed_in' => 'datetime',
    	'last_signed_out' => 'datetime',
		];
	}

	public function votes()
	{
		return $this->hasMany(\App\Models\Vote::class);
	}

	public function otpCodes()
	{
		return $this->hasMany(\App\Models\OtpCode::class);
	}
}
