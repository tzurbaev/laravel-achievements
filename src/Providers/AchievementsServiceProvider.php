<?php

namespace Laravel\Achievements\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Achievements\AchievementsStorage;
use Zurbaev\Achievements\AchievementsManager;
use Zurbaev\Achievements\Contracts\AchievementsStorageInterface;

class AchievementsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(
            realpath(__DIR__.'/../../database/migrations')
        );
    }

    /**
     * Register application services.
     */
    public function register()
    {
        $this->app->bind(AchievementsStorageInterface::class, AchievementsStorage::class);

        $this->app->singleton(AchievementsManager::class, function () {
            $storage = app(AchievementsStorageInterface::class);

            return new AchievementsManager($storage);
        });
    }
}
