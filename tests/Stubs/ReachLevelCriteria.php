<?php

namespace Laravel\Achievements\Tests\Stubs;

use Zurbaev\Achievements\Achievement;
use Zurbaev\Achievements\AchievementCriteria;
use Zurbaev\Achievements\AchievementCriteriaChange;

class ReachLevelCriteria
{
    /**
     * Get criteria type.
     *
     * @return string
     */
    public function type()
    {
        return 'reach_level';
    }

    /**
     * Handle criteria update.
     *
     * @param mixed               $owner
     * @param AchievementCriteria $criteria
     * @param Achievement         $achievement
     * @param mixed               $data        = null
     *
     * @return AchievementCriteriaChange
     */
    public function handle($owner, AchievementCriteria $criteria, Achievement $achievement, $data = null)
    {
        return new AchievementCriteriaChange(
            intval($data['value'] ?? 1),
            AchievementCriteriaChange::PROGRESS_ACCUMULATE
        );
    }
}
