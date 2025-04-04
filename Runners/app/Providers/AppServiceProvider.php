<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Posts\Post;
use App\Models\Profiles\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('tasks', fn ($app) => collect());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Model::shouldBeStrict($this->app->isLocal());

        DB::prohibitDestructiveCommands($this->app->isProduction());

        Relation::enforceMorphMap([
            'post' => Post::class,
            'profile' => Profile::class,
        ]);
    }
}
