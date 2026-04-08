<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function vote(Request $request, Post $post) {
        $request->validate([
            'type'=>'required|in:up, down'
        ]);
        
        $voteType = $request->type === 'up' ? 1 : -1;

        // Check if user has already voted
        $existingVote = Vote::where('user_id', auth()->id())
            ->where('post_id', $post->id)
            ->first();
        
        if ($existingVote) {
            if ($existingVote->type === $voteType) {
                // Remove vote
                $existingVote->delete();

                // Update post counts
                if ($voteType === 1) {
                    $post->decrement('upvotes_count');
                }
                else {
                    $post->decrement('downvotes_count');
                }

                return response()->json([
                    'message' => 'Vote removed',
                    'upvotes' => $post->fresh()->upvotes_count,
                    'downvotes' => $post->fresh()->downvotes_count,
                    'user_vote' => null
                ]);
            }

            else {
                // Change vote
                $existingVote->update(['type' => $voteType]);

                // Update Post counts
                if ($voteType === 1) {
                    $post->increment('upvotes_count');
                    $post->decrement('downvotes_count');
                }

                else {
                    $post->increment('downvotes_count');
                    $post->decrement('upvotes_count');
                }

                return response()->json([
                    'message'=> 'Vote changed',
                    'upvotes'=> $post->fresh()->upvotes_count,
                    'downvotes'=> $post->fresh()->downvotes_count,
                    'user_vote'=> $request->type
                ]);
            }
        }

        else {
            // Create new vote
            Vote::create([
                'user_id' => auth()->id(),
                'post_id' => $post->id,
                'type' => $voteType
            ]);

            // Update post counts
            if ($voteType === 1) {
                $post->increment('upvotes_count');
            }

            else {
                $post->increment('downvotes_count');
            }

            // Check if post reached milestone for activity
            $post->refresh();
            if ($post->upvotes_count === 10 || $post->upvotes_count === 50) {
                Activity::create([
                    'user_id' => null,
                    'activitable_type' => Post::class,
                    'activitable_id' => $post->id,
                    'type' => 'milestone',
                    'description' => "Suggestion reached {$post->upvotes_count} upvotes!",
                    'metadata' => ['milestone' => $post->upvotes_count]
                ]);
            }

            return response()->json([
                'message' => 'Vote recorded',
                'upvotes' => $post->fresh()->upvotes_count,
                'downvotes' => $post->fresh()->downvotes_count,
                'user_vote' => $request->type
            ]);
        }
    }
}
