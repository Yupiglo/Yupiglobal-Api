<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $imagePool = [
            '/images/layout-1/laptop.jpg',
            '/images/layout-1/gaming.jpg',
            '/images/layout-1/tablet.jpg',
            '/images/layout-1/collection-banner/1.jpg',
            '/images/layout-1/collection-banner/7.jpg',
        ];

        $isSale = $this->faker->boolean(35);
        $discount = $isSale ? $this->faker->randomFloat(0, 5, 35) : 0;

        $price = $this->faker->randomFloat(2, 49, 1299);

        $img = $this->faker->randomElement($imagePool);

        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement(['electronics', 'accessories', 'computing']),
            'brand' => $this->faker->randomElement(['Apple', 'Samsung', 'Sony', 'Nikon', 'Dell', 'Lenovo', 'Logitech']),
            'category' => $this->faker->randomElement(['ELECTRONICS', 'COMPUTERS', 'ACCESSORIES']),
            'price' => $price,
            'is_new' => $this->faker->boolean(40),
            'is_sale' => $isSale,
            'discount' => $discount,
            'img_cover' => $img,
            'images' => [$img],
            'variants' => [
                [
                    'color' => $this->faker->randomElement(['Black', 'White', 'Silver', 'Blue']),
                    'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                    '_id' => (string) Str::uuid(),
                    'id' => (string) Str::uuid(),
                ],
            ],
            'quantity' => $this->faker->numberBetween(0, 50),
            'sold' => $this->faker->numberBetween(0, 200),
        ];
    }
}
