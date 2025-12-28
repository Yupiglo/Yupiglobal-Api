<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Computers', 'image' => '/images/categories/01.png'],
            ['name' => 'Electronics', 'image' => '/images/categories/02.png'],
            ['name' => 'Accessories', 'image' => '/images/categories/03.png'],
            ['name' => 'Cameras', 'image' => '/images/categories/04.png'],
            ['name' => 'Headphones', 'image' => '/images/categories/05.png'],
            ['name' => 'Smartphones', 'image' => '/images/categories/06.png'],
            ['name' => 'Wearables', 'image' => '/images/categories/07.png'],
            ['name' => 'Gaming', 'image' => '/images/categories/08.png'],
            ['name' => 'Home', 'image' => '/images/categories/09.png'],
        ];

        foreach ($items as $item) {
            Category::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'slug' => Str::slug($item['name']),
                    'image' => $item['image'],
                ]
            );
        }
    }
}
