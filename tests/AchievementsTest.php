<?php

namespace Laravel\Achievements\Tests;

use Laravel\Achievements\AchievementCriteriaModel;
use Laravel\Achievements\AchievementModel;
use Laravel\Achievements\Tests\Stubs\User;

class AchievementsTest extends TestCase
{
    public function testRelations()
    {
        /**
         * @var AchievementModel $achievement
         */
        $achievement = AchievementModel::create([
            'name' => 'Level 10',
            'description' => 'Reach level 10.',
            'points' => 10,
        ]);

        /**
         * @var AchievementCriteriaModel $criteria
         */
        $criteria = $achievement->criterias()->create([
            'type' => 'reach_level',
            'name' => 'Reach level 10.',
            'max_value' => 10,
        ]);

        $this->assertSame(1, count($achievement->criterias));
        $achievementCriteria = $achievement->criterias()->first();

        /**
         * @var AchievementCriteriaModel $achievementCriteria
         */
        $this->assertInstanceOf(AchievementCriteriaModel::class, $achievementCriteria);
        $this->assertSame($criteria->id, $achievementCriteria->id);
        $this->assertSame($criteria->type, $achievementCriteria->type);
        $this->assertSame($criteria->name, $achievementCriteria->name);
        $this->assertSame($criteria->max_value, $achievementCriteria->max_value);

        $this->assertInstanceOf(AchievementModel::class, $achievementCriteria->achievement);
        $this->assertSame($achievement->id, $achievementCriteria->achievement->id);
    }

    public function testAchievementsTrait()
    {
        /**
         * @var AchievementModel $achievement
         */
        $achievement = AchievementModel::create([
            'name' => 'Level 10',
            'description' => 'Reach level 10.',
            'points' => 10,
        ]);

        /**
         * @var AchievementCriteriaModel $criteria
         */
        $criteria = $achievement->criterias()->create([
            'type' => 'reach_level',
            'name' => 'Reach level 10.',
            'max_value' => 10,
        ]);

        /**
         * @var User $user
         * @var User $user2
         */
        $user = User::create(['name' => 'John Doe']);
        $user2 = User::create(['name' => 'Jane Doe']);

        $this->assertSame(0, count($user->achievements));
        $this->assertSame(0, count($user->achievementCriterias));
        $this->assertSame(0, $user->achievementPoints());

        $user->achievements()->attach($achievement->id);

        $user = $user->fresh();
        $this->assertSame(1, count($user->achievements));
        $this->assertSame(10, $user->achievementPoints());

        $user->achievementCriterias()->attach($criteria->id, ['value' => 5]);
        $user = $user->fresh();
        $this->assertSame(1, count($user->achievementCriterias));

        $this->assertSame(0, count($user2->achievements));
        $this->assertSame(0, count($user2->achievementCriterias));
        $this->assertSame(0, $user2->achievementPoints());
    }
}
