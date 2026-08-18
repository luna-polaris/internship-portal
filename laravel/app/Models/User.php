<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password',
        'phone',
        'profile_picture',
        'role',
        'status',
    ];

    protected $appends = ['profile_picture_url'];

    protected $hidden = [
        'password',
        'remember_token',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'token_expires_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'user_id');
    }

    public function employer()
    {
        return $this->hasOne(Employer::class, 'user_id', 'user_id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    protected function profilePictureUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_picture ? Storage::disk('public')->url($this->profile_picture) : null,
        );
    }

    /** Shared by the JSON and browser-facing verify-email endpoints so both use the same activation rule. */
    public static function activateByToken(string $token): ?self
    {
        $user = static::where('token', $token)->where('token_expires_at', '>=', now())->first();

        if (! $user) {
            return null;
        }

        $user->forceFill(['status' => 'Active', 'token' => null, 'token_expires_at' => null])->save();

        return $user;
    }
}
