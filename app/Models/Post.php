<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "title",
        "content",
        "category_id",
        "status_id",
        "anonymous"
    ];

    protected $casts = [
        "anonymous" => boolean,
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
    public function status () 
    {
        return $this->belongsTo(Status::class);
    }

    public function votes() 
    {
        return $this->hasMany(Vote::class);
    }

    public function comments() 
    {
        return $this->hasMany(Comment::class);
    }

    public function activities() 
    {
        return $this->morphMany(Activity::class, "activatable");
    }

    public function getUserVoteAttribute()
    {
        if (!auth()->check()) return null;
        
        $vote = $this->votes()->where('user_id', auth()->id())->first();
        return $vote ? ($vote->type === 1 ? 'up' : 'down') : null;
    }
}
