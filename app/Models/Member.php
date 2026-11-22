<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    use HasFactory;

    public const TYPE_ADMIN = 1;
    public const TYPE_GENERAL = 2;

    protected $table = 'members';

    protected $fillable = [
        'member_code',
        'name',
        'name_kana',
        'password',
        'nickname',
        'avatar',
        'email',
        'member_type',
        'delete_flg',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'member_type' => 'integer',
        'delete_flg'  => 'boolean',
        'password'    => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->member_type === self::TYPE_ADMIN;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'member_id');
    }

    public function avatarInitial(): string
    {
        $name = trim((string) ($this->nickname ?: $this->name ?: 'U'));
        return mb_strtoupper(mb_substr($name, 0, 1));
    }

    public function avatarColor(): string
    {
        $palette = ['#ff6b35', '#7c3aed', '#06b6d4', '#22c55e', '#f59e0b', '#ec4899', '#3b82f6'];
        return $palette[((int) $this->id) % count($palette)];
    }
}
