<?php

namespace Laravel\Achievements\Events;

use Zurbaev\Achievements\Achievement;
use Zurbaev\Achievements\AchievementCriteria;
use Zurbaev\Achievements\AchievementCriteriaProgress;

class CriteriaUpdated extends AbstractEvent
{
    /**
     * @var AchievementCriteria
     */
    public $criteria;

    /**
     * @var Achievement
     */
    public $achievement;

    /**
     * @var AchievementCriteriaProgress
     */
    public $progress;

    /**
     * CriteriaUpdated constructor.
     *
     * @param mixed                       $owner
     * @param AchievementCriteria         $criteria
     * @param Achievement                 $achievement
     * @param AchievementCriteriaProgress $progress
     */
    public function __construct($owner, AchievementCriteria $criteria, Achievement $achievement, AchievementCriteriaProgress $progress)
    {
        $this->owner = $owner;
        $this->criteria = $criteria;
        $this->achievement = $achievement;
        $this->progress = $progress;
    }
}
