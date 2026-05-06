<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistanceType extends Model
{
    use HasFactory;

    protected $table = 'assistance_types';

    protected $guarded = [];
}
