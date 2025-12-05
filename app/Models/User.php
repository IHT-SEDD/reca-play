<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Master\Venue;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public const Searchable = ['name', 'username', 'email', 'role_id', 'venue_id'];
    public const Unsearchable = ['id', 'email_verified_at', 'password', 'remember_token', 'google_id', 'created_at', 'updated_at'];

    protected $with = ['role', 'venue'];
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'venue_id',
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'google_id'
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
        ];
    }

    /**
     * Send the password reset notification */

    public function sendPasswordResetNotification($token)
    {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $this->email,
            ], false));

            Mail::to($this->email)->send(new \App\Mail\ResetPasswordMail($url));
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class); # Role model from Spatie\Permission\Models\Role
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isVenueManagement(): bool
    {
        return $this->hasAnyRole(['owner', 'cashier']);
    }
}
