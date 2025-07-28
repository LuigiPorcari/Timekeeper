<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable
{
    public function races()
    {
        return $this->belongsToMany(Race::class)->withPivot('is_leader')->withTimestamps();
    }
    public function isLeaderOf(Race $race)
    {
        return $this->races()
            ->where('race_id', $race->id)
            ->wherePivot('is_leader', true)
            ->exists();
    }
    public function availabilities()
    {
        return $this->belongsToMany(Availability::class);
    }
    public function records()
    {
        return $this->hasMany(Record::class);
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'date_of_birth',
        'residence',
        'domicile',
        'transfer',
        'auto',
        'password',
        'is_timekeeper',
        'is_admin',
        'is_secretariat'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'specialization' => 'array',
        ];
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
}
