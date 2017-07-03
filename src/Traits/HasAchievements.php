<?php

namespace Laravel\Achievements\Traits;

/**
 * Trait HasAchievements
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Achievements\AchievementModel[] $achievements
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Achievements\AchievementCriteriaModel[] $achievementCriterias
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
        return $this->morphToMany(
            config('achievements.models.achievement'),
            'achievementable',
            'achievementables',
            null,
            'achievement_model_id'
        )->withPivot(['completed_at']);
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
        return $this->morphToMany(
            config('achievements.models.criteria'),
            'achievement_criteriable',
            'achievement_criteriables',
            null,
            'achievement_criteria_model_id'
        )->withPivot(['value', 'completed', 'progress_data', 'updated_at']);
    }
}
