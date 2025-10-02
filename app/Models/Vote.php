<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
	use HasFactory;

	protected $fillable = [
		'election_id',
		'position_id',
		'candidate_id',
		'user_id',
	];

	public function election()
	{
		return $this->belongsTo(Election::class);
	}

	public function position()
	{
		return $this->belongsTo(Position::class);
	}

	public function candidate()
	{
		return $this->belongsTo(Candidate::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
