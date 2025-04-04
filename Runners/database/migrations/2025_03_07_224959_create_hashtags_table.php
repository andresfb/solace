<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtags', static function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('post_hashtags', static function (Blueprint $table): void {
            $table->foreignId('post_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('hashtag_id')
                ->constrained()
                ->onDelete('cascade');
            $table->primary(['post_id', 'hashtag_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_hashtags');
        Schema::dropIfExists('hashtags');
    }
};
