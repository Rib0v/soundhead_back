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
            $table->string('name');
            $table->integer('price'); // TODO добавить поле discount_price
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('min_frequency')->nullable(); // TODO вынести эти параметры в отдельную таблицу
            $table->string('max_frequency')->nullable(); // эти
            $table->string('sensitivity')->nullable(); // и эти
            $table->string('image');
            $table->timestamps();
            $table->softDeletes();
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
