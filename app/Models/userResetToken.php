<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userResetToken extends Model
{
    use HasFactory;

    protected $table = 'user_reset_tokens';

    protected $fillable = [
        'email',
        'type',
        'token',
        'expires_at',
    ];
    protected $dates = [
        'expires_at',
        'created_at',
        'updated_at',
    ];
}
