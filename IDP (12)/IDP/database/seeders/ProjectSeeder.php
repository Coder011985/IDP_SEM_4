<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::where('role', 'client')->first();
        $designer = User::where('role', 'designer')->first();

        Project::create([
            'title' => 'Luxury Villa Interior',
            'description' => 'Complete luxury villa interior design.',
            'budget_min' => 50000,
            'budget_max' => 100000,
            'property_type' => 'residential',
            'client_id' => $client->id,
            'designer_id' => $designer->id, // ✅ FIXED HERE
            'status' => 'completed',
            'approval_status' => 'approved',
            'progress' => 100,
        ]);

        Project::create([
            'title' => 'Office Space Design',
            'description' => 'Modern corporate office setup.',
            'budget_min' => 30000,
            'budget_max' => 60000,
            'property_type' => 'office',
            'client_id' => $client->id,
            'status' => 'pending',
            'approval_status' => 'approved',
        ]);
    }
}
