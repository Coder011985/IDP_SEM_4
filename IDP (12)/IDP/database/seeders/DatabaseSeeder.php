<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Project;
use App\Models\Bid;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        $admin = User::create([
            'name' => 'Main Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 1,
        ]);

        // CLIENTS
        $client1 = User::create([
            'name' => 'Client One',
            'email' => 'client1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 1,
        ]);

        $client2 = User::create([
            'name' => 'Client Two',
            'email' => 'client2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'status' => 1,
        ]);

        // DESIGNERS
        $designer1 = User::create([
            'name' => 'Designer Residential',
            'email' => 'designer1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'specialization' => 'residential',
            'status' => 1,
        ]);

        $designer2 = User::create([
            'name' => 'Designer Commercial',
            'email' => 'designer2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'specialization' => 'commercial',
            'status' => 1,
        ]);

        // PROJECT 1
        $project1 = Project::create([
            'client_id' => $client1->id,
            'title' => 'Luxury Villa Interior',
            'description' => 'Need modern luxury interior design with premium materials.',
            'budget_min' => 500000,
            'budget_max' => 800000,
            'type' => 'full',
            'property_type' => 'residential',
            'status' => 'pending',   // FIXED
            'approval_status' => 'approved',
            'progress' => 0,
        ]);

        // PROJECT 2
        $project2 = Project::create([
            'client_id' => $client2->id,
            'designer_id' => $designer2->id,
            'title' => 'Office Space Design',
            'description' => 'Modern corporate office interior with smart workspace planning.',
            'budget_min' => 300000,
            'budget_max' => 600000,
            'type' => 'office',
            'property_type' => 'commercial',
            'status' => 'completed',
            'approval_status' => 'approved',
            'progress' => 100,
            'rating' => 5,
            'review' => 'Excellent work and timely delivery.',
        ]);

        // BID
        Bid::create([
            'project_id' => $project2->id,
            'designer_id' => $designer2->id,
            'amount' => 450000,
            'proposal' => 'We will complete this office interior with full 3D visualization, material planning, and execution support within 30 days.',
            'status' => 'accepted',
        ]);

        // PAYMENT
        Payment::create([
            'project_id' => $project2->id,
            'client_id' => $client2->id,
            'designer_id' => $designer2->id,
            'amount' => 450000,
            'status' => 'released',
            'released_at' => now(),
        ]);
    }
}
