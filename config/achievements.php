<?php

return [
    /**
     * Here you can override models & events class names.
     *
     * This may be useful, if you want to add more fields
     * to achievements/criterias models, or enable the
     * broadcasting feature for achievements events.
     */
    'models' => [
        'achievement' => \Laravel\Achievements\AchievementModel::class,
        'criteria' => \Laravel\Achievements\AchievementCriteriaModel::class,
    ],
    'events' => [
        'criteria_updated' => \Laravel\Achievements\Events\CriteriaUpdated::class,
        'achievements_completed' => \Laravel\Achievements\Events\AchievementsCompleted::class,
    ],
];
