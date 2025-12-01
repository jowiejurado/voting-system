<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSecurityQuestion extends Model
{
	use HasFactory, HasEncryptedAttributes;

  protected $fillable = [
    'user_id',
    'question',
    'answer_hash',
  ];

	protected array $encryptable = [
    'question',
	];

  public function user(): BelongsTo {
    return $this->belongsTo(User::class);
  }

  public static function normalizeAnswer(?string $answer): string {
    return mb_strtolower(trim((string)$answer));
  }
}
