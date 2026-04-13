<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with(['user', 'votes'])
            ->whereNull('parent_id')
            ->withCount(['votes as upvotes' => function($q) {
                $q->where('type', 1);
            }])
            ->orderBy('created_at', 'asc')
            ->get();

        return CommentResource::collection($comments);
    }

    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:2',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null
        ]);

        // Increment post comments count
        $post->increment('comments_count');

        // Create activity
        Activity::create([
            'user_id' => auth()->id(),
            'activitable_type' => Comment::class,
            'activitable_id' => $comment->id,
            'type' => 'commented',
            'description' => "Commented on: {$post->title}",
            'metadata' => ['post_id' => $post->id, 'post_title' => $post->title]
        ]);

        return new CommentResource($comment->load('user'));
    }

    public function vote(Request $request, Comment $comment)
    {
        // Similar to post voting logic
        $request->validate(['type' => 'required|in:up,down']);
        
        $voteType = $request->type === 'up' ? 1 : -1;
        
        $existingVote = $comment->votes()->where('user_id', auth()->id())->first();
        
        if ($existingVote) {
            if ($existingVote->type === $voteType) {
                $existingVote->delete();
                $comment->decrement('upvotes_count');
            } else {
                $existingVote->update(['type' => $voteType]);
                if ($voteType === 1) {
                    $comment->increment('upvotes_count');
                    $comment->decrement('downvotes_count');
                } else {
                    $comment->increment('downvotes_count');
                    $comment->decrement('upvotes_count');
                }
            }
        } else {
            $comment->votes()->create([
                'user_id' => auth()->id(),
                'type' => $voteType
            ]);
            
            if ($voteType === 1) {
                $comment->increment('upvotes_count');
            } else {
                $comment->increment('downvotes_count');
            }
        }
        
        return response()->json([
            'upvotes' => $comment->fresh()->upvotes_count,
            'downvotes' => $comment->fresh()->downvotes_count
        ]);
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $comment->delete();
        
        return response()->json(['message' => 'Comment deleted']);
    }

    public function all(Request $request)
    {
        $comments = Comment::with(['user', 'post'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));
        
        return CommentResource::collection($comments);
    }
}