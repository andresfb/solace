<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected Builder $schema;

    public function __construct()
    {
        $this->schema = Schema::connection(config('database.default'));
    }

    public function up(): void
    {
        $this->schema->create('module_settings', static function (Blueprint $table): void {
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
        $this->schema->dropIfExists('module_settings');
    }
};
