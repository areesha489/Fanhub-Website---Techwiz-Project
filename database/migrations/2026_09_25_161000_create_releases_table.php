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
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('release_type', 20)->default('anime');
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('release_date')->nullable();
            $table->text('description')->nullable();
            $table->string('poster')->nullable();
            $table->string('status', 20)->default('upcoming');
            $table->foreignId('content_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('popularity')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'release_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
