<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.media_runner_connection'))
            ->table('posts')
            ->where('status', '>', 2)
            ->update([
                'status' => DB::raw('
                    CASE
                        WHEN status = 3 THEN 0
                        WHEN status = 4 THEN 1
                        WHEN status = 5 THEN 2
                        ELSE status
                    END
                '),
            ]);
    }
};
