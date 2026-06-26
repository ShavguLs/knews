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

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }
}
