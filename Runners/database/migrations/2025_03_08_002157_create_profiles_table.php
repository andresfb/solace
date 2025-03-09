<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->date('birthday')->nullable();
            $table->string('gender', 1)->default('P');
            $table->boolean('private')->default(false);
            $table->boolean('verified')->default(false);
            $table->boolean('humanoid')->default(false);
            $table->unsignedInteger('posts_count')->default(0);
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'verified', 'humanoid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
