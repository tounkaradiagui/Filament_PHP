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
            $table->string("name");
            $table->string("slug")->unique();
            $table->longText("description")->nullable();
            $table->string("image")->nullable();
            $table->boolean("is_visible")->default(false);
            $table->boolean("is_featured")->default(false);
            $table->foreignId("brand_id")
                    ->constrained('brands')
                    ->onDelete('cascade');
            $table->string("sku")->unique(); //sku => stock keeping unit // This line defines a sku column of type string, which will hold the unit identifier for the product used in inventory management. This column is unique (no two products can have the same sku).
            $table->unsignedBigInteger("quantity");
            $table->decimal("price");
            $table->enum("type", ['deliverable', 'downloadable'])->default("deliverable");
            $table->date("published_at");
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
