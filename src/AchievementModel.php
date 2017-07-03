<?php

namespace Laravel\Achievements;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AchievementModel
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $points
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|AchievementCriteriaModel[] $criterias
 */
class AchievementModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'achievements';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'points',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * Criterias list.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function criterias()
    {
        return $this->hasMany(config('achievements.models.criteria'), 'achievement_id');
    }

    /**
     * Determines if achievement was completed.
     * This works only in user-related achievements lists.
     *
     * @return bool
     */
    public function completed(): bool
    {
        return $this->relationLoaded('pivot') && !is_null($this->pivot->completed_at);
    }
}
