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
        $this->schema = Schema::connection(config('database.news_feed_runner_connection'));
    }

    public function up(): void
    {
        if ($this->schema->hasColumn('articles', 'runner_status')) {
            return;
        }

        $this->schema->table('articles', function (Blueprint $table): void {
            $table->unsignedTinyInteger('runner_status')
                ->after('thumbnail')
                ->default(0);

            $table->index('runner_status');
        });
    }

    public function down(): void
    {
        if (! $this->schema->hasColumn('articles', 'runner_status')) {
            return;
        }

        $this->schema->table('articles', function (Blueprint $table): void {
            $table->dropColumn('runner_status');
        });
    }
};
