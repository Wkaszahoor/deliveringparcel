<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Permissions\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'facebook_id',
        'password',
        'number',
        'avatar',
        'type',
        'status', // NULL = active (UsersMgmt admin module)
        'fcm_tokens', // JSON array of FCM device tokens for push notifications
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'fcm_tokens'        => 'array',
    ];


    use HasPermissionsTrait; //Import The Trait

    /** Wallet (PM-014) — one balance row per user, lazily created. */
    public function wallet()
    {
        return $this->hasOne(\App\Models\Wallet::class);
    }
}
