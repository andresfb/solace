<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->insert([
                ['module_name' => 'news_feed_runner', 'task_name' => 'imaged-article-importer', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'news_feed_runner', 'task_name' => 'imaged-article-importer', 'name' => 'schedule', 'value' => 'td', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'news_feed_runner', 'task_name' => 'imaged-article-importer', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'news_feed_runner', 'task_name' => 'ai-article-importer', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'news_feed_runner', 'task_name' => 'ai-article-importer', 'name' => 'schedule', 'value' => 'od', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'news_feed_runner', 'task_name' => 'ai-article-importer', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ]);
    }

    public function down(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->where('module_name', 'news_feed_runner')
            ->delete();
    }
};
