<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistanceApplication extends Model
{
    use HasFactory;

    protected $table = 'assistance_applications';

    protected $guarded = [];

    public function program()
    {
        return $this->belongsTo(Programme::class, 'program_id');
    }

    public function member()
    {
        return $this->belongsTo(HouseholdMember::class, 'member_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id');
    }
}
