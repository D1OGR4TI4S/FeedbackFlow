<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $query = Post::with(['user', 'category', 'status', 'votes', 'comments']);
        
        // Apply date filters
        if ($request->range === 'last_week') {
            $query->where('created_at', '>=', now()->subWeek());
        } elseif ($request->range === 'last_month') {
            $query->where('created_at', '>=', now()->subMonth());
        } elseif ($request->range === 'custom' && $request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        $posts = $query->get();
        
        $data = $posts->map(function ($post) use ($request) {
            $row = [
                'ID' => $post->id,
                'Title' => $post->title,
                'Content' => $post->content,
                'Category' => $post->category->name,
                'Status' => $post->status->name,
                'Upvotes' => $post->votes->where('type', 1)->count(),
                'Downvotes' => $post->votes->where('type', -1)->count(),
                'Comments' => $post->comments->count(),
                'Created At' => $post->created_at->format('Y-m-d H:i:s'),
                'Author' => $post->anonymous ? 'Anonymous' : ($post->user->name ?? 'Deleted User'),
            ];
            
            if ($request->include_comments) {
                $row['Comments_List'] = $post->comments->map(function($comment) {
                    return $comment->content;
                })->implode(' | ');
            }
            
            return $row;
        });
        
        if ($format === 'csv') {
            return $this->exportCSV($data);
        } elseif ($format === 'pdf') {
            return $this->exportPDF($data);
        }
    }
    
    private function exportCSV($data): StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            if ($data->isNotEmpty()) {
                fputcsv($handle, array_keys($data->first()));
                
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            }
            
            fclose($handle);
        }, 'feedback-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
    
    private function exportPDF($data)
    {
        $pdf = Pdf::loadView('exports.feedback', ['data' => $data]);
        return $pdf->download('feedback-export-' . now()->format('Y-m-d') . '.pdf');
    }
}