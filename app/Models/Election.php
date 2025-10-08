<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
	use HasFactory;

	protected $fillable = [
		'title',
		'date',
		'start_time',
		'end_time',
		'is_active',
	];

	protected $casts = [
		'is_active' => 'boolean',
	];

	public function positions()
	{
		return $this->hasMany(Position::class);
	}

	public function candidates()
	{
		return $this->hasMany(Candidate::class);
	}

	public function votes()
	{
		return $this->hasMany(Vote::class);
	}
}
