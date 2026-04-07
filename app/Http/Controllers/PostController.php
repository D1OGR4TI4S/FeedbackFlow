<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'category', 'status'])
            ->withCount(['votes as upvotes' => function($q) {
                $q->where('type', 1);
            }])
            ->withCount(['votes as downvotes' => function($q) {
                $q->where('type',-1);
            }])
            ->withCount('comments');

        // Filters
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        if ($request->status) {
            $query->where('status_id', $request->status);
        }

        // Sorting using switch case statements
        switch ($request->sort) {
            case 'most_upvoted':
                $query->orderBy('upvotes_count','desc');
                break;
            case 'most_comments':
                $query->orderBy('comments_count','desc');
                break;
            case 'recent_activity':
                $query->orderBy('updated_at','desc');
                break;
            default:
                $query->orderBy('created_at','desc');

        }

        $posts = $query->paginate(15);

        return PostResource::collection($posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Activity::create([
            'user_id' => auth()->id(),
            'activitable_type' => Post::class,
            'activitable_id' => $post->id,
            'type' => 'created_post',
            'description' => "New suggestion: {$post->title}"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'anonymous' => 'boolean'
        ]);

        $post = Post::create([
            'user_id' => $request->anonymous ? null : auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'status_id' => 1, // Default, Pending Status
            'anonymous' => $request
        ]);

        // Create Activity
        Activity::create([
            'user_id' => auth()->id(),
            'activatable_type' => Post::class,
            'activatable_id' => $post->id,
            'type' => 'created_post',
            'description' => "New suggestion: {$post->title}",
            'metadata' => ['post_title' => $post->title]
        ]);

        return new PostResource($post->load(['user', 'category', 'status']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostResource($post->load(['user', 'category', 'status', 'comments.user', 'comments.votes']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        // Only allow updating if user owns the post or is admin
        if ($post->user_id !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'category_id' => 'exists:categories,id'
        ]);

        $post->update($validated);

        return new PostResource($post->load(['user', 'category', 'status']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json(['message'=> 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function similar(Post $post)
    {
        $similar = Post::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->with(['user', 'category', 'status'])
            ->withCount(['votes as upvotes' => function($q) {
                $q->where('type', 1);
            }])
            ->orderBy('upvotes_count', 'desc')
            ->limit(5)
            ->get();

        return PostResource::collection($similar);
    }
}
