<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description');
            $table->string('type')->nullable();
            $table->string('brand')->nullable();
            $table->string('category');
            $table->decimal('price', 12, 2);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_sale')->default(false);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('img_cover')->nullable();
            $table->json('variants')->nullable();
            $table->json('images')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
