<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')
                ->cascadeOnDelete();
            $table->string('slug', 12)->unique();
            $table->string('hash')->unique();
            $table->string('status', 1)->default('C');
            $table->string('privacy', 1)->default('V');
            $table->text('content');
            $table->string('generator');
            $table->json('responses')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
