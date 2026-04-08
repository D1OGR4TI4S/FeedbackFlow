<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'anonymous' => $this->anonymous,
            'upvotes' => $this->upvotes_count ?? $this->votes()->where('type', 1)->count(),
            'downvotes' => $this->downvotes_count ?? $this->votes()->where('type', -1)->count(),
            'comments_count' => $this->comments_count ?? $this->comments()->count(),
            'user_vote' => $this->when(auth()->check(), $this->getUserVoteAttribute()),
            'user' => $this->when(!$this->anonymous, function() {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                ];
            }),
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
            ],
            'status' => [
                'id' => $this->status->id,
                'name' => $this->status->name,
                'color' => $this->status->color,
            ],
            'created_at' => $this->created_at->diffForHumans(),
            'created_at_raw' => $this->created_at,
            'updated_at' => $this->updated_at->diffForHumans(),
        ];
    }
}