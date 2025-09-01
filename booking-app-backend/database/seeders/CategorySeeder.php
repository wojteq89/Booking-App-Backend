<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Fryzjerstwo',
            'Kosmetyka',
            'Medycyna',
            'Stomatologia',
            'Fitness',
            'Restauracje',
            'Mechanika pojazdowa',
            'Usługi budowlane',
            'Fotografia',
            'Edukacja',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insertOrIgnore([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}