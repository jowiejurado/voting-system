<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasEncryptedAttributes, HasFactory;

    protected $fillable = [
        'name',
    ];

    protected array $encryptable = [
        'name',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
