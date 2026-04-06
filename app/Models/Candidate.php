<?php

namespace App\Models;

use App\Models\Traits\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasEncryptedAttributes, HasFactory;

    protected $fillable = [
        'election_id',
        'position_id',
        'organization_id',
        'last_name',
        'first_name',
    ];

    protected array $encryptable = [
        'last_name',
        'first_name',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
