<?php

namespace Laravel\Achievements\Tests;

use Laravel\Achievements\Facades\Achievements;
use Laravel\Achievements\LaravelAchievements;
use Laravel\Achievements\Tests\Stubs\ReachLevelCriteria;
use Laravel\Achievements\Tests\Stubs\User;

class LaravelAchievementsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->seedAchievements();
    }

    public function testInstance()
    {
        /**
         * @var User                $user
         * @var LaravelAchievements $achievements
         */
        $user = User::create(['name' => 'John Doe']);
        $achievements = app(LaravelAchievements::class);
        $achievements->registerCriterias([
            ReachLevelCriteria::class,
        ]);

        $count = $achievements->criteriaUpdated($user, 'reach_level', ['value' => 10]);

        $this->assertSame(3, $count);
        $this->assertSame(3, count($user->achievementCriterias));
    }

    public function testFacade()
    {
        /**
         * @var User $user
         */
        $user = User::create(['name' => 'John Doe']);
        Achievements::registerCriterias([
            ReachLevelCriteria::class,
        ]);

        $count = Achievements::criteriaUpdated($user, 'reach_level', ['value' => 10]);

        $this->assertSame(3, $count);
        $this->assertSame(3, count($user->achievementCriterias));
    }
}
