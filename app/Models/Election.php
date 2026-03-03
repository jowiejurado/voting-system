<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
	use HasFactory, HasEncryptedAttributes;

	protected $fillable = [
		'title',
		'start_date',
		'end_date',
		'start_time',
		'end_time',
		'status',
	];

	protected array $encryptable = [
		'title',
		'start_date',
		'end_date',
		'start_time',
		'end_time',
		'status'
	];

	public function candidates()
	{
		return $this->hasMany(Candidate::class);
	}

	public function votes()
	{
		return $this->hasMany(Vote::class);
	}
}
