<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'image' => '/images/home-banner/african-1.png',
                'title1' => 'New Collection',
                'title2' => 'Just Arrived',
                'sub_title1' => 'Discover the latest picks',
                'btn' => 'Shop Now',
                'category' => 'COMPUTERS',
                'is_active' => true,
                'top_banner' => true,
                'promotional_banner' => false,
                'sort_order' => 1,
            ],
            [
                'image' => '/images/home-banner/african-2.png',
                'title1' => 'Hot Deals',
                'title2' => 'Best Prices',
                'sub_title1' => 'Limited time offers',
                'btn' => 'Explore',
                'category' => 'ELECTRONICS',
                'is_active' => true,
                'top_banner' => true,
                'promotional_banner' => false,
                'sort_order' => 2,
            ],
            [
                'image' => '/images/home-banner/african-3.png',
                'title1' => 'Accessories',
                'title2' => 'Essentials',
                'sub_title1' => 'Complete your setup',
                'btn' => 'See More',
                'category' => 'ACCESSORIES',
                'is_active' => true,
                'top_banner' => false,
                'promotional_banner' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($items as $item) {
            Banner::query()->updateOrCreate(
                ['sort_order' => $item['sort_order']],
                $item
            );
        }
    }
}
