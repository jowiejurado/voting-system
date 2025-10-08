<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QrToken extends Model
{
	use HasFactory;

	protected $fillable = ['user_id', 'code', 'purpose', 'expires_at', 'used_at', 'meta'];

	protected $casts = [
		'expires_at' => 'datetime',
		'used_at'    => 'datetime',
		'meta'       => 'array',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function isUsable(): bool
	{
		return is_null($this->used_at) && (is_null($this->expires_at) || now()->lt($this->expires_at));
	}
}
