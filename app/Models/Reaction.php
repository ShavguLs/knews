<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    public const TYPES = [
        'like' => '👍',
        'love' => '❤️',
        'fire' => '🔥',
        'wow' => '😮',
        'angry' => '😡',
    ];

    protected $fillable = [
        'news_id',
        'user_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
