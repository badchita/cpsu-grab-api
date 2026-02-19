<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin already exists
        $admin = User::where('email', 'admin')->first();
        if (!$admin) {
            User::create([
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'user_type' => 'Admin',
                'status' => 'Active', // or 'Pending' if you want
            ]);

            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
