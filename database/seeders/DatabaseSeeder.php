<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'thisiskazi@gmail.com'],
            [
                'name' => 'Kazi Rayhan Reza',
                'email' => 'thisiskazi@gmail.com',
                'password' => Hash::make('kazi@20042002'),
                'email_verified_at' => now(),
            ]
        );

        // Seed categories, exams, and questions
        $this->call([
            CategorySeeder::class,
            ExamSeeder::class,
            // QuestionSeeder::class,
        ]);
    }
}
