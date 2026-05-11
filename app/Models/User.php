<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guard_name = 'sanctum';
    
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'registration_type',
        'nin',
        'is_active',
        'created_by',
        'phone',
        'has_completed_onboarding',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function household()
    {
        return $this->hasOne(Household::class, 'user_id');
    }
    public function householduser()
    {
        return $this->hasOne(Household::class, 'household_id');
    }
    public function program()
    {
        return $this->hasOne(Programme::class, 'program_id');
    }
    public function member()
    {
        return $this->hasOne(Household::class, 'member_id');
    }

}
