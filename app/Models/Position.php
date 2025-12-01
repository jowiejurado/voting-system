<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
	use HasFactory, HasEncryptedAttributes;

	protected $fillable = [
		'name',
		'maximum_votes',
	];

	protected array $encryptable = [
		'name',
	];

	public function election()
	{
		return $this->belongsTo(Election::class);
	}

	public function candidates()
	{
		return $this->hasMany(Candidate::class);
	}
}
