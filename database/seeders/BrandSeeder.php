<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Apple', 'image' => '/images/icon/1.png'],
            ['name' => 'Samsung', 'image' => '/images/icon/2.png'],
            ['name' => 'Sony', 'image' => '/images/icon/3.png'],
            ['name' => 'LG', 'image' => '/images/icon/4.png'],
            ['name' => 'Nikon', 'image' => '/images/icon/5.png'],
            ['name' => 'Canon', 'image' => '/images/icon/6.png'],
        ];

        foreach ($items as $item) {
            Brand::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'slug' => Str::slug($item['name']),
                    'image' => $item['image'],
                ]
            );
        }
    }
}
