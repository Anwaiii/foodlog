<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'category',
        'description',
        'address',
        'image',
        'emoji',
        'created_at',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
