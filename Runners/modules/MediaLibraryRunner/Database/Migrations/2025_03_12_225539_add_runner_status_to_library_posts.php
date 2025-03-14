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
        $this->schema = Schema::connection(config('database.media_runner_connection'));
    }

    public function up(): void
    {
        if ($this->schema->hasColumn('posts', 'runner_status')) {
            return;
        }

        $this->schema->table('posts', function (Blueprint $table) {
            $table->unsignedTinyInteger('runner_status')
                ->after('used')
                ->default(0);

            $table->index('runner_status');
        });
    }

    public function down(): void
    {
        if (! $this->schema->hasColumn('posts', 'runner_status')) {
            return;
        }

        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropColumn('runner_status');
        });
    }
};
