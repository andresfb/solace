<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.news_feed_runner_connection'))
            ->table('articles')
            ->update([
                'runner_status' => 0,
            ]);
    }
};
