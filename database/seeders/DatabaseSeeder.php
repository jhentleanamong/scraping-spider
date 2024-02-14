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
        $user = User::create([
            'name' => 'Super Administrator',
            'email' => 'super.administrator@example.net',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);

        // Log a message to the console indicating that the super admin has been created.
        $this->command->info('Super admin created.');

        // Generate an API token for the super admin.
        // This token allows the super admin to make authenticated API requests.
        $token = $user->createToken('api-key');

        // Update the user's record to include the API token.
        $user->update(['api_key' => $token->plainTextToken]);

        // Log a message to the console indicating that the API token for super admin has been created.
        $this->command->info('Super admin API access token created.');
    }
}
