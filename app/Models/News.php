<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'category',
        'author',
        'body',
        'image_url',
        'published_at',
        'status',
        'is_hero',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_hero' => 'boolean',
    ];
}
