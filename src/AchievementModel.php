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
        return $this->hasMany(AchievementCriteriaModel::class, 'achievement_id');
    }
}
