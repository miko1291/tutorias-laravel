<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    public const STATUS_DELETED = 0;

    public const STATUS_INACTIVE = 9;

    public const STATUS_ACTIVE = 10;

    protected $table = 'user';

    public $timestamps = true;

    protected $fillable = [
        'username',
        'auth_key',
        'password_hash',
        'password_reset_token',
        'email',
        'status',
        'verification_token',
    ];

    protected $hidden = [
        'password_hash',
        'auth_key',
        'password_reset_token',
        'verification_token',
    ];

    public function freshTimestampString(): string
    {
        return (string) now()->timestamp;
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getRememberTokenName()
    {
        // La tabla heredada usa auth_key en lugar de remember_token.
        return 'auth_key';
    }

    public function setRememberToken($value)
    {
        $this->attributes[$this->getRememberTokenName()] = substr($value, 0, 32);
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function hasRole(string $role): bool
    {
        return DB::table('auth_assignment')
            ->where('item_name', $role)
            ->where('user_id', (string) $this->id)
            ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return DB::table('auth_assignment')
            ->whereIn('item_name', $roles)
            ->where('user_id', (string) $this->id)
            ->exists();
    }

    public function roles(): array
    {
        return DB::table('auth_assignment')
            ->where('user_id', (string) $this->id)
            ->pluck('item_name')
            ->all();
    }

    public function alumno()
    {
        return $this->hasOne(Alumno::class, 'id_user');
    }

    public function tutor()
    {
        return $this->hasOne(Tutor::class, 'id_user');
    }

    public static function findActiveByUsername(string $username): ?self
    {
        return static::where('username', $username)
            ->where('status', self::STATUS_ACTIVE)
            ->first();
    }

    public static function findActiveAdminByUsername(string $username): ?self
    {
        return static::query()
            ->from('user as U')
            ->join('auth_assignment as AA', 'U.id', '=', 'AA.user_id')
            ->where('U.username', $username)
            ->where('U.status', self::STATUS_ACTIVE)
            ->whereIn('AA.item_name', ['admin', 'psicologo'])
            ->first();
    }
}
