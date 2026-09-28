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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('dark');
            $table->decimal('font_scale', 3, 2)->default(1);
            $table->boolean('reduced_motion')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('content_notifications')->default(true);
            $table->boolean('event_notifications')->default(true);
            $table->boolean('newsletter')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
