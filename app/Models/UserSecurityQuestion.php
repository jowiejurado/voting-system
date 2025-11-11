<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSecurityQuestion extends Model
{
	use HasFactory;

  protected $fillable = [
    'user_id',
    'question',
    'answer_hash',
  ];

  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }

  public static function normalizeAnswer(?string $answer): string {
    return mb_strtolower(trim((string)$answer));
  }
}
