<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "activatable_type",
        "activatable_id",
        "type",
        "description",
        "metadata"
    ];

    protected $casts = [
        "metadata" => 'array'
    ];

    public function user () {
        return $this->belongsTo(User::class);
    }

    public function activatable () {
        return $this->morphTo();
    } 
}
