<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'bina_id',
        'door_no',
        'is_evsahibi',
        'phone',
        'payratio',
        'giris_tarihi',
        'building_quota',
        'remarks',
        'is_sakin',
        'is_manager',
        'is_superuser',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_sakin' => 'boolean',
        'is_manager' => 'boolean',
        'is_superuser' => 'boolean',
        'is_evsahibi' => 'boolean',
        'building_quota' => 'integer',
        'payratio' => 'float',
    ];

    public function isSuperuser(): bool
    {
        return (bool) $this->is_superuser;
    }

    public function isManager(): bool
    {
        return (bool) $this->is_manager;
    }

    public function isSakin(): bool
    {
        return (bool) $this->is_sakin;
    }

    public function canCreateBuilding(): bool
    {
        return $this->isSuperuser()
            || \App\Models\Bina::query()->where('user_id', $this->id)->count() < $this->building_quota;
    }

    public function binalar()
    {
        return $this->hasMany(Bina::class);
    }
}
