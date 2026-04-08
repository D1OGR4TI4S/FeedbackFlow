<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@feedbackflow.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
        
        // Create categories
        $categories = [
            ['name' => 'Feature Request', 'slug' => 'feature-request', 'icon' => 'mdi-star', 'description' => 'Suggest new features or improvements'],
            ['name' => 'Bug Report', 'slug' => 'bug-report', 'icon' => 'mdi-bug', 'description' => 'Report issues or problems'],
            ['name' => 'General', 'slug' => 'general', 'icon' => 'mdi-forum', 'description' => 'General feedback or suggestions'],
            ['name' => 'UI/UX', 'slug' => 'ui-ux', 'icon' => 'mdi-palette', 'description' => 'Suggestions for user interface improvements'],
            ['name' => 'Performance', 'slug' => 'performance', 'icon' => 'mdi-speedometer', 'description' => 'Feedback about speed and performance'],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
        
        // Create statuses
        $statuses = [
            ['name' => 'Pending', 'slug' => 'pending', 'color' => 'grey', 'order' => 1],
            ['name' => 'Under Review', 'slug' => 'under-review', 'color' => 'warning', 'order' => 2],
            ['name' => 'Planned', 'slug' => 'planned', 'color' => 'info', 'order' => 3],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color' => 'primary', 'order' => 4],
            ['name' => 'Completed', 'slug' => 'completed', 'color' => 'success', 'order' => 5],
            ['name' => 'Declined', 'slug' => 'declined', 'color' => 'error', 'order' => 6],
        ];
        
        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
