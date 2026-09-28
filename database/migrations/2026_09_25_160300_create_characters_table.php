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
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('fandom')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->text('bio')->nullable();
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('abilities')->nullable();
            $table->string('debut')->nullable();
            $table->bigInteger('views_count')->default(0);
            $table->integer('popularity')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
