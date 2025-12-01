<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
	use HasFactory, HasEncryptedAttributes;

	protected $fillable = [
		'user_id',
		'phone_number',
		'code',
		'expires_at',
		'used',
	];

	protected array $encryptable = [
		'phone_number',
		'code',
	];

	protected $casts = [
		'expires_at' => 'datetime',
		'used' => 'boolean',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
