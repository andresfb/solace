<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->insert([
                ['module_name' => 'user_generator', 'task_name' => 'generate_users', 'name' => 'is_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'user_generator', 'task_name' => 'generate_users', 'name' => 'schedule', 'value' => 'h', 'created_at' => now(), 'updated_at' => now()],
                ['module_name' => 'user_generator', 'task_name' => 'generate_users', 'name' => 'empty_runs_count', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ]);
    }

    public function down(): void
    {
        DB::connection(config('database.default'))
            ->table('module_settings')
            ->where('module_name', 'user_generator')
            ->delete();
    }
};
