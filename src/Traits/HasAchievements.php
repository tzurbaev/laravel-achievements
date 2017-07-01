<?php

namespace Laravel\Achievements\Traits;

use Laravel\Achievements\AchievementCriteriaModel;
use Laravel\Achievements\AchievementModel;

/**
 * Trait HasAchievements
 *
 * @property \Illuminate\Database\Eloquent\Collection|AchievementModel[] $achievements
 * @property \Illuminate\Database\Eloquent\Collection|AchievementCriteriaModel[] $achievementCriterias
 */
trait HasAchievements
{
    /**
     * Completed achievements list.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function achievements()
    {
        return $this->morphToMany(AchievementModel::class, 'achievementable')
            ->withPivot(['completed_at']);
    }

    /**
     * Total achievement points.
     *
     * @return int
     */
    public function achievementPoints()
    {
        return intval($this->achievements()->sum('points'));
    }

    /**
     * Achievement criterias progress.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function achievementCriterias()
    {
        return $this->morphToMany(AchievementCriteriaModel::class, 'achievement_criteriable')
            ->withPivot(['value', 'completed', 'progress_data', 'updated_at']);
    }
}
