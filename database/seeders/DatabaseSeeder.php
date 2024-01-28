<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default super admin.
        User::create([
            'name' => 'Super Administrator',
            'email' => 'super.administrator@example.net',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);

        $this->command->info('Super admin created.');
    }
}
