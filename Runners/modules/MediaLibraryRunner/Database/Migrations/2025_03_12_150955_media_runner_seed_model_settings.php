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
                ['module_name' => 'media_runner', 'task_name' => 'fulfilled', 'name' => 'schedule', 'value' => 'odd', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'fulfilled', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'untagged_videos', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'untagged_videos', 'name' => 'schedule', 'value' => 'h', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'untagged_videos', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-vision-ai', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-vision-ai', 'name' => 'schedule', 'value' => 'cm', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-vision-ai', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-chat-ai', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-chat-ai', 'name' => 'schedule', 'value' => 'ttd', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'post-chat-ai', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'lc-no-banded', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'lc-no-banded', 'name' => 'schedule', 'value' => 'td', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'lc-no-banded', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'lost-cause', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'lost-cause', 'name' => 'schedule', 'value' => 'ttw', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'media_runner', 'task_name' => 'lost-cause', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
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
