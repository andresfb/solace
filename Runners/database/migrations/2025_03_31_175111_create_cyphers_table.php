<?php

use App\Libraries\Cyphers\AffineLibrary;
use App\Libraries\Cyphers\AtbashLibrary;
use App\Libraries\Cyphers\BaconLibrary;
use App\Libraries\Cyphers\RailFenceLibrary;
use App\Libraries\Cyphers\Rot13Library;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cyphers', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('active')->default(1);
            $table->string('name')->index();
            $table->string('clue');
            $table->text('class');
        });

        DB::connection(config('database.default'))
            ->table('cyphers')
            ->insert([
                ['name' => 'ROT13', 'clue' => "Halfway there, but flip again and you're home.", 'class' => Rot13Library::class],
                ['name' => 'Atbash', 'clue' => 'What was first shall be last.', 'class' => AtbashLibrary::class],
                ['name' => 'Affine', 'clue' => 'Math is the key, but can you solve for X?', 'class' => AffineLibrary::class],
                ['name' => 'Rail Fence', 'clue' => 'Step up, step down, follow the pattern.', 'class' => RailFenceLibrary::class],
            ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cyphers');
    }
};
