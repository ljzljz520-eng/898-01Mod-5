<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_clues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lost_pet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('address', 255)->nullable();
            $table->timestamp('seen_at');
            $table->text('description')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->boolean('is_private')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('lost_pet_id');
            $table->index('user_id');
            $table->index(['lat', 'lng']);
            $table->index('is_private');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_clues');
    }
};
