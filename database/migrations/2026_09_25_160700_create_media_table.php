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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('media_type', 20)->default('video');
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('url')->nullable();
            $table->string('embed_url')->nullable();
            $table->string('duration')->nullable();
            $table->json('gallery')->nullable();
            $table->bigInteger('views_count')->default(0);
            $table->decimal('rating', 3, 1)->default(0);
            $table->boolean('featured')->default(false);
            $table->string('status', 20)->default('published');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
