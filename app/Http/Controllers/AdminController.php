<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Status;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function updateStatus(Request $request, Post $post)
    {
        $request->validate([
            'status_id' => 'required|exists:statuses,id'
        ]);

        $oldStatus = $post->status->name;
        $post->update(['status_id' => $request->status_id]);

        // Create activity for status change
        Activity::create([
            'user_id' => auth()->id(),
            'activitable_type' => Post::class,
            'activitable_id' => $post->id,
            'type' => 'status_changed',
            'description' => "Status changed from {$oldStatus} to {$post->status->name}",
            'metadata' => [
                'old_status' => $oldStatus,
                'new_status' => $post->status->name
            ]
        ]);

        return response()->json([
            'message' => 'Status updated',
            'post' => $post->load('status')
        ]);
    }

    public function markOfficial(Request $request, Comment $comment)
    {
        $comment->update(['is_official' => true]);

        // Create activity
        Activity::create([
            'user_id' => auth()->id(),
            'activitable_type' => Comment::class,
            'activitable_id' => $comment->id,
            'type' => 'official_reply',
            'description' => "Official response added to: {$comment->post->title}",
            'metadata' => ['post_id' => $comment->post_id]
        ]);

        return response()->json([
            'message' => 'Comment marked as official',
            'comment' => $comment
        ]);
    }

    public function stats()
    {
        return response()->json([
            'total_posts' => Post::count(),
            'total_comments' => Comment::count(),
            'pending_posts' => Post::where('status_id', 1)->count(),
            'total_upvotes' => Vote::where('type', 1)->count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
            'posts_by_category' => Category::withCount('posts')->get(),
            'posts_by_status' => Status::withCount('posts')->get()
        ]);
    }

    public function deletePost(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
