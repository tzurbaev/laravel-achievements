<?php

namespace Laravel\Achievements\Events;

class AchievementsCompleted extends AbstractEvent
{
    /**
     * Completed achievements list.
     *
     * @var array
     */
    public $achievements = [];

    /**
     * AchievementsCompleted constructor.
     *
     * @param mixed $owner
     * @param array $achievements
     */
    public function __construct($owner, array $achievements)
    {
        $this->owner = $owner;
        $this->achievements = $achievements;
    }
}
