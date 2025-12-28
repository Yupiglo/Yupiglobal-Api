<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->count() > 0) {
            return;
        }

        Product::factory()->count(24)->create();

        // Ensure we always have visible items in the "special" tabs.
        Product::query()->inRandomOrder()->limit(6)->update(['is_new' => true]);
        Product::query()->inRandomOrder()->limit(6)->update(['is_sale' => true, 'discount' => 15]);
    }
}
