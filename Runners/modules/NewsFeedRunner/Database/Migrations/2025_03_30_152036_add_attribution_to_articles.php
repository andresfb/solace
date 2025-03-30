<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder;

return new class extends Migration
{
    protected Builder $schema;

    public function __construct()
    {
        $this->schema = Schema::connection(config('database.news_feed_runner_connection'));
    }

    public function up(): void
    {
        if ($this->schema->hasColumn('articles', 'attribution')) {
            return;
        }

        $this->schema->table('articles', function (Blueprint $table): void {
            $table->string('attribution')
                ->after('thumbnail')
                ->nullable();
        });
    }

    public function down(): void
    {
        if (! $this->schema->hasColumn('articles', 'attribution')) {
            return;
        }

        $this->schema->table('articles', function (Blueprint $table): void {
            $table->dropColumn('attribution');
        });
    }
};
