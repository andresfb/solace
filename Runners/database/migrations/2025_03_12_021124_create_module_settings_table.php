<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_settings', static function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('task_name');
            $table->string('name');
            $table->text('value');
            $table->timestamps();

            $table->index(['module_name', 'task_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_settings');
    }
};
