<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('pet_name', 100)->nullable();
            $table->enum('pet_type', ['dog', 'cat', 'other'])->default('dog');
            $table->string('breed', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('collar_features', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->decimal('last_seen_lat', 10, 7);
            $table->decimal('last_seen_lng', 10, 7);
            $table->string('last_seen_address', 255);
            $table->timestamp('last_seen_at');
            $table->string('contact_phone', 20);
            $table->string('contact_name', 50)->nullable();
            $table->text('thank_you_note')->nullable();
            $table->enum('status', ['lost', 'found', 'closed'])->default('lost');
            $table->integer('view_count')->default(0);
            $table->integer('clue_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index(['last_seen_lat', 'last_seen_lng']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_pets');
    }
};
