<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('id_from_provider');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('country')->nullable();
            $table->string('language')->nullable();
            $table->string('category')->nullable();
            $table->string('url')->nullable();
            $table->foreign('category')->references('name')->on('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
