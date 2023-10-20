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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('url', 2048)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('author')->nullable();
            $table->string('image', 2048)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('country')->nullable();
            $table->string('language')->nullable();
            $table->unsignedBigInteger('source_id');
            $table->foreign('source_id')->references('id')->on('news_sources');
            $table->foreign('category')->references('name')->on('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
