<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.media_runner_connection'))
            ->table('posts')
            ->update([
                'runner_status' => 0,
            ]);
    }
};
