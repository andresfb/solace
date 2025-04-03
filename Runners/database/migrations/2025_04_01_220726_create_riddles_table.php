<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riddles', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('cypher_id')->constrained('cyphers')
                ->cascadeOnDelete();
            $table->string('hash')->unique();
            $table->string('category');
            $table->text('question');
            $table->text('encoded');
            $table->string('answer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riddles');
    }
};
