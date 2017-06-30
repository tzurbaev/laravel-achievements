<?php

namespace Laravel\Achievements\Facades;

use Illuminate\Support\Facades\Facade;
use Laravel\Achievements\LaravelAchievements;

class Achievements extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return LaravelAchievements::class;
    }
}
