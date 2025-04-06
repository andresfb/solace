<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->insert([
                ['module_name' => 'emby_media_runner', 'task_name' => 'index-media', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'emby_media_runner', 'task_name' => 'index-media', 'name' => 'schedule', 'value' => 'tm', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'emby_media_runner', 'task_name' => 'index-media', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],

                ['module_name' => 'emby_media_runner', 'task_name' => 'generate-movie-post', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'emby_media_runner', 'task_name' => 'generate-movie-post', 'name' => 'schedule', 'value' => 'od', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'emby_media_runner', 'task_name' => 'generate-movie-post', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],

            ]);
    }

    public function down(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->where('module_name', 'emby_media_runner')
            ->delete();
    }
};
