<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Bina extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'binalar';

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $user->isSuperuser()
            ? $query
            : $query->where('manager_id', $user->id);
    }


    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }


    public function sakinler()
    {
        return $this->hasMany(User::class)->where('is_sakin', true);
    }

    public function active_sakinler()
    {
        return $this->hasMany(User::class)
            ->where('is_sakin', true)
            ->where('is_active', true)
            ->orderBy('door_no', 'asc')
            ->orderBy('id', 'asc');
    }





    public function kalemler()
    {
        return $this->hasMany(Kalem::class);
    }

    public function bedeller()
    {
        return $this->hasMany(Bedel::class);
    }


    protected function carbonCreatedAt(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => Carbon::parse(
                $attributes['created_at']
            )->diffForHumans(),
        );
    }
}
