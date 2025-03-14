<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->insert([
                ['module_name' => 'media_runner', 'task_name' => 'fulfilled', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'fulfilled', 'name' => 'schedule', 'value' => 'eth', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'fulfilled', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'untagged_videos', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'untagged_videos', 'name' => 'schedule', 'value' => 'eth', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'untagged_videos', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-via_ai', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-via_ai', 'name' => 'schedule', 'value' => 'eth', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-via_ai', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ]);
    }

    public function down(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->where('module_name', 'media_runner')
            ->delete();
    }
};
