<?php

namespace Laravel\Achievements;

use Zurbaev\Achievements\AchievementsManager;

class LaravelAchievements
{
    /**
     * @var AchievementsManager
     */
    protected $manager;

    /**
     * LaravelAchievements constructor.
     *
     * @param AchievementsManager $manager
     */
    public function __construct(AchievementsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns underlying achievements manager.
     *
     * @return AchievementsManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Sends criteria update notification to achievements manager.
     * Returns number of updated criterias.
     *
     * @param mixed  $owner
     * @param string $type
     * @param mixed  $data  = null
     *
     * @return int
     */
    public function criteriaUpdated($owner, string $type, $data = null)
    {
        return $this->manager->updateAchievementCriteria($owner, $type, $data);
    }

    /**
     * Registers criteria handler classes.
     *
     * @param array $criterias
     */
    public function registerCriterias(array $criterias)
    {
        foreach ($criterias as $criteria) {
            if (!is_string($criteria)) {
                continue;
            }

            $instance = app($criteria);

            AchievementsManager::registerHandler($instance->type(), [$instance, 'handle']);
        }
    }
}
