<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Math', 'slug' => 'math', 'is_active' => true],
            ['name' => 'Bangla', 'slug' => 'bangla', 'is_active' => true],
            ['name' => 'English', 'slug' => 'english', 'is_active' => true],
            ['name' => 'ICT', 'slug' => 'ict', 'is_active' => true],
            ['name' => 'General Knowledge', 'slug' => 'general-knowledge', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
