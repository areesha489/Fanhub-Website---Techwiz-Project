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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 30)->default('Other');
            $table->string('genre')->nullable();
            $table->text('description')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->date('release_date')->nullable();
            $table->string('status', 20)->default('published');
            $table->decimal('rating', 3, 1)->default(0);
            $table->bigInteger('views_count')->default(0);
            $table->integer('popularity')->default(0);
            $table->boolean('featured')->default(false);
            $table->string('trailer_url')->nullable();
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
        Schema::dropIfExists('contents');
    }
};
