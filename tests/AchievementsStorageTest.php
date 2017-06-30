<?php

namespace Laravel\Achievements\Tests;

use Illuminate\Support\Facades\Event;
use Laravel\Achievements\AchievementCriteriaModel;
use Laravel\Achievements\AchievementModel;
use Laravel\Achievements\AchievementsStorage;
use Laravel\Achievements\Events\AchievementsCompleted;
use Laravel\Achievements\Events\CriteriaUpdated;
use Laravel\Achievements\Tests\Stubs\User;
use Zurbaev\Achievements\Achievement;
use Zurbaev\Achievements\AchievementCriteria;
use Zurbaev\Achievements\AchievementCriteriaProgress;

class AchievementsStorageTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var AchievementsStorage
     */
    protected $storage;

    public function setUp()
    {
        parent::setUp();

        $this->seedAchievements();
        $this->user = User::create(['name' => 'John Doe']);
        $this->storage = app(AchievementsStorage::class);

        Event::fake();
    }

    public function testGetOwnerCriteriasByType()
    {
        $criterias = $this->storage->getOwnerCriteriasByType($this->user, 'reach_level');
        $this->assertInternalType('array', $criterias);
        $this->assertSame(3, count($criterias));

        foreach ($criterias as $criteria) {
            $this->assertInstanceOf(AchievementCriteria::class, $criteria);
            /**
             * @var AchievementCriteria $criteria
             */
            $this->assertSame('reach_level', $criteria->type());
        }
    }

    public function testGetAchievementsByCriterias()
    {
        $criterias = $this->storage->getOwnerCriteriasByType($this->user, 'reach_level');
        $this->assertInternalType('array', $criterias);
        $this->assertSame(3, count($criterias));

        $achievements = $this->storage->getAchievementsByCriterias($criterias);
        $this->assertInternalType('array', $achievements);
        $this->assertSame(3, count($achievements));

        foreach ($achievements as $achievement) {
            $this->assertInstanceOf(Achievement::class, $achievement);
            /**
             * @var Achievement $achievement
             */
            $this->assertFalse($achievement->completed());
            $this->assertSame(1, count($achievement->criterias()));
        }
    }

    public function testGetAchievementForCriteria()
    {
        $criterias = $this->storage->getOwnerCriteriasByType($this->user, 'reach_level');
        $achievements = $this->storage->getAchievementsByCriterias($criterias);

        /**
         * @var Achievement $expected
         * @var Achievement $actual
         */
        $expected = $achievements[0];
        $actual = $this->storage->getAchievementForCriteria($expected->criterias()[0], $achievements);

        $this->assertInstanceOf(Achievement::class, $actual);
        $this->assertSame($expected->id(), $actual->id());
    }

    public function testGetAchievementsWithProgressFor()
    {
        /**
         * @var AchievementCriteriaModel $criteria
         */
        $criteria = AchievementCriteriaModel::find(1); // Reach level 10.
        $this->user->achievementCriterias()->attach($criteria->id, ['value' => 6]);
        $this->user = $this->user->fresh();

        $achievements = $this->storage->getAchievementsWithProgressFor($this->user, [$criteria->achievement_id]);
        $this->assertInternalType('array', $achievements);
        $this->assertSame(1, count($achievements));

        /**
         * @var Achievement         $achievement
         * @var AchievementCriteria $achievementCriteria
         */
        $achievement = $achievements[0];
        $achievementCriteria = $achievement->criterias()[0];

        $this->assertInstanceOf(AchievementCriteria::class, $achievementCriteria);
        $this->assertTrue($achievementCriteria->hasProgress());
        $this->assertSame(6, $achievementCriteria->progress()->value);
    }

    public function testSetCriteriaProgressUpdated()
    {
        $achievement = $this->storage->convertAchievementModelWithCriterias(
            AchievementModel::find(1)
        );
        /**
         * @var AchievementCriteria $criteria
         */
        $criteria = $achievement->criterias()[0];

        $this->assertSame(0, count($this->user->achievementCriterias));

        $result = $this->storage->setCriteriaProgressUpdated(
            $this->user, $criteria, $achievement, new AchievementCriteriaProgress(6, true, false)
        );

        $this->assertTrue($result);

        $this->user = $this->user->fresh();
        $this->assertSame(1, count($this->user->achievementCriterias));
        $userCriteria = $this->user->achievementCriterias()->find($criteria->id());
        $this->assertInstanceOf(AchievementCriteriaModel::class, $userCriteria);
        $this->assertSame($criteria->id(), $userCriteria->id);
        $this->assertSame(6, intval($userCriteria->pivot->value));

        Event::assertDispatched(CriteriaUpdated::class);
    }

    public function testSetAchievementsCompleted()
    {
        $this->assertSame(0, count($this->user->achievements));
        $this->assertSame(0, $this->user->achievementPoints());

        $achievements = $this->storage->convertAchievementModelsWithCriterias(
            AchievementModel::whereIn('id', [1, 2, 3])->get()
        );

        $this->storage->setAchievementsCompleted($this->user, $achievements);

        $this->user = $this->user->fresh();
        $this->assertSame(3, count($this->user->achievements));
        $this->assertSame(30, $this->user->achievementPoints());

        Event::assertDispatched(AchievementsCompleted::class);
    }
}
