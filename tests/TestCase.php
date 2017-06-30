<?php

namespace Laravel\Achievements\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Achievements\Providers\AchievementsServiceProvider;
use Laravel\Achievements\Tests\Migrations\CreateUsersTable;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseMigrations;

    protected function getPackageProviders($app)
    {
        return [
            AchievementsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function setUp()
    {
        parent::setUp();

        (new CreateUsersTable())->up();
    }

    public function seedAchievements()
    {
        (new \AchievementsSeeder())->run();
    }
}
