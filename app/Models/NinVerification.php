<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NinVerification extends Model
{
    use HasFactory;

        protected $fillable = [
            'nin',
            'title',
            'first_name',
            'last_name',
            'middle_name',
            'full_name',
            'date_of_birth',
            'gender',
            'phone',
            'alternate_phone',
            'email',
            'state_of_origin',
            'state_of_residence',
            'lga',
            'city',
            'address',
            'nationality',
            'marital_status',
            'profession',
            'kin_first_name',
            'kin_last_name',
            'kin_phone',
            'kin_email',
            'kin_address',
            'business_name',
            'job_title',
            'company_email',
            'company_phone',
            'company_address',
            'rc_number',
            'photo',
            'raw_response',
            'source',
        ];


    protected $casts = [
        'raw_response' => 'array',
        'date_of_birth' => 'date',
    ];

}
