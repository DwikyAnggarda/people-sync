<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Filament\Models\Contracts\FilamentUser;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Disable auto-incrementing for UUID.
     */
    public $incrementing = false;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Hidden attributes for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getJWTIdentifier()
    {
        return $this->id; // UUID
    }

    public function getJWTCustomClaims(): array
    {
        return []; // identity-only
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Use Spatie's hasRole method for authorization
        // Allow admin and hr roles to access the panel
        return $this->hasRole(['admin', 'hr']);
    }

    /**
     * Linked employee (optional).
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
