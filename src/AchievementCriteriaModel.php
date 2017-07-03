<?php

namespace Laravel\Achievements;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AchievementCriteriaModel
 *
 * @property int $id
 * @property int $achievement_id
 * @property string $type
 * @property string $name
 * @property int $max_value
 * @property array $requirements
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property AchievementModel $achievement
 */
class AchievementCriteriaModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'achievement_criterias';

    /**
     * @var array
     */
    protected $fillable = [
        'achievement_id', 'type', 'name',
        'max_value', 'requirements',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'achievement_id' => 'integer',
        'max_value' => 'integer',
        'requirements' => 'array',
    ];

    /**
     * Achievement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function achievement()
    {
        return $this->belongsTo(config('achievements.models.achievement'));
    }
}
