<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'category',
        'author',
        'body',
        'image_url',
        'image_path',
        'published_at',
        'status',
        'is_hero',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_hero' => 'boolean',
    ];

    public static function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'category' => 'required|max:100',
            'author' => 'required|max:100',
            'body' => 'required',
            'image_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
            'status' => 'required|in:pending,done',
            'is_hero' => 'nullable|boolean',
        ];
    }

    public function setAsHero(): void
    {
        static::where('is_hero', true)
            ->where('id', '!=', $this->id)
            ->update(['is_hero' => false]);

        $this->update(['is_hero' => true]);
    }

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

    public function imageSource(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return '/storage/' . ltrim($this->image_path, '/');
        }

        return $this->image_url;
    }
}
