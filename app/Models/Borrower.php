<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Borrower extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'phone',
        'trust_score',
        'portal_access',
        'photo_url',
    ];
}
