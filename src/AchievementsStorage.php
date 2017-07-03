<?php

namespace Laravel\Achievements;

use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Zurbaev\Achievements\Achievement;
use Zurbaev\Achievements\AchievementCriteria;
use Zurbaev\Achievements\AchievementCriteriaProgress;
use Zurbaev\Achievements\Contracts\AchievementsStorageInterface;

class AchievementsStorage implements AchievementsStorageInterface
{
    const MODEL_ACHIEVEMENT = 'achievement';
    const MODEL_CRITERIA = 'criteria';

    /**
     * @var Repository
     */
    protected $config;

    /**
     * AchievementsStorage constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Returns actual class name from config.
     *
     * @param string $section
     * @param string $type
     *
     * @return string
     */
    protected function getConfigurableClassName(string $section, string $type)
    {
        return $this->config->get('achievements.'.$section.'.'.$type);
    }

    /**
     * Injects model class name into given callback.
     *
     * @param string   $type
     * @param callable $callback
     *
     * @return mixed|string
     */
    protected function getModelClass(string $type, callable $callback)
    {
        return call_user_func($callback, $this->getConfigurableClassName('models', $type));
    }

    /**
     * Dispatches new Achievements event.
     *
     * @param string $type
     * @param array $args
     * @return mixed
     */
    protected function dispatchEvent(string $type, array $args = [])
    {
        $className = $this->getConfigurableClassName('events', $type);

        return Event::dispatch(new $className(...$args));
    }

    /**
     * Fetches given owner's criterias by given type.
     *
     * @param mixed  $owner
     * @param string $type
     *
     * @return array
     */
    public function getOwnerCriteriasByType($owner, string $type)
    {
        $criterias = $this->getCriteriasByType($type);

        if (!count($criterias)) {
            return [];
        }

        $ownerCriteriaProgress = $this->getOwnerCriteriasProgress($owner, function ($query) use ($type) {
            $query->where('type', $type);
        });

        return $criterias->map(function ($criteria) use ($ownerCriteriaProgress) {
            /** @var AchievementCriteriaModel $criteria */

            return $this->transformCriteriaWithProgress($criteria, $ownerCriteriaProgress->get($criteria->id));
        })->toArray();
    }

    /**
     * Loads criteria progress for given owner, applies given query callback
     * and returns array of AchievementCriteria objects transformed from AchievementCriteriaModels.
     *
     * @param mixed    $owner
     * @param callable $callback
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getOwnerCriteriasProgress($owner, callable $callback)
    {
        $query = $owner->achievementCriterias();

        call_user_func_array($callback, [$query]);

        return $this->transformOwnerCriteriasToProgress($query->get());
    }

    /**
     * Loads achievement criterias with given type.
     *
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getCriteriasByType(string $type)
    {
        return $this->getModelClass(static::MODEL_CRITERIA, function (string $className) use ($type) {
            return $className::where('type', $type)->get();
        });
    }

    /**
     * Transforms AchievementCriteriaModel collection int AchievementCriteriaProgress collection.
     *
     * @param Collection $criterias
     *
     * @return \Illuminate\Support\Collection
     */
    protected function transformOwnerCriteriasToProgress(Collection $criterias)
    {
        return $criterias->keyBy('id')->map(function ($criteria) {
            /** @var AchievementCriteriaModel $criteria */

            return new AchievementCriteriaProgress(
                intval($criteria->pivot->value),
                false,
                intval($criteria->pivot->completed) === 1,
                is_string($criteria->pivot->progress_data) ? json_decode($criteria->pivot->progress_data, true) : []
            );
        });
    }

    /**
     * Transforms AchievementCriteriaModel to AchievementCriteria object and attaches progress data.
     *
     * @param AchievementCriteriaModel         $criteria
     * @param AchievementCriteriaProgress|null $progress
     *
     * @return AchievementCriteria
     */
    protected function transformCriteriaWithProgress($criteria, AchievementCriteriaProgress $progress = null)
    {
        $data = [
            'id' => $criteria->id,
            'achievement_id' => $criteria->achievement_id,
            'type' => $criteria->type,
            'name' => $criteria->name,
            'requirements' => $criteria->requirements ?? [],
            'max_value' => $criteria->max_value,
        ];

        if (!is_null($progress)) {
            $data['progress'] = [
                'value' => $progress->value,
                'changed' => false,
                'completed' => $progress->completed,
                'data' => $progress->data,
            ];
        }

        return new AchievementCriteria($data);
    }

    /**
     * Returns list of criterias' achievements.
     *
     * @param array $criterias
     *
     * @return array
     */
    public function getAchievementsByCriterias(array $criterias)
    {
        $achievementIds = array_map(function (AchievementCriteria $criteria) {
            return $criteria->achievementId();
        }, $criterias);

        $achievements = $this->getAchievementsByIds(array_unique($achievementIds));

        if (!count($achievements)) {
            return [];
        }

        return $this->transformAchievements($achievements, $criterias)->toArray();
    }

    /**
     * Loads collection of achievements by IDs.
     *
     * @param array $achievementIds
     *
     * @return mixed
     */
    public function getAchievementsByIds(array $achievementIds)
    {
        return $this->getModelClass(static::MODEL_ACHIEVEMENT, function (string $className) use ($achievementIds) {
            return $className::whereIn('id', array_unique($achievementIds))->get();
        });
    }

    /**
     * Converts collection of AchievementModel objects to array of Achievement objects.
     *
     * @param Collection $achievements
     * @param bool       $reloadCriteriasRelation = true
     *
     * @return array
     */
    public function convertAchievementModelsWithCriterias(Collection $achievements, bool $reloadCriteriasRelation = true)
    {
        if ($reloadCriteriasRelation) {
            $achievements->load('criterias');
        }

        return $achievements->map([$this, 'convertAchievementModelWithCriterias'])->toArray();
    }

    /**
     * Converts single AchievementModel to Achievement object.
     *
     * @param AchievementModel $achievement
     *
     * @return Achievement
     */
    public function convertAchievementModelWithCriterias($achievement)
    {
        $criterias = $achievement->criterias->map(function ($criteria) {
            /** @var AchievementCriteriaModel $criteria */

            return $this->transformCriteriaWithProgress($criteria);
        });

        return $this->transformSingleAchievement($achievement, $criterias->toArray());
    }

    /**
     * Transforms AchievementModel collection to Achievement collection and applies appropriate criterias.
     *
     * @param Collection $achievements
     * @param array      $criterias
     *
     * @return \Illuminate\Support\Collection
     */
    protected function transformAchievements(Collection $achievements, array $criterias)
    {
        return $achievements->map(function ($achievement) use ($criterias) {
            /** @var AchievementModel $achievement */

            return $this->transformSingleAchievement($achievement, $criterias);
        });
    }

    /**
     * Transforms single AchievementModel object to Achievement object with appropriate criterias.
     *
     * @param AchievementModel $achievement
     * @param array            $criterias
     *
     * @return Achievement
     */
    protected function transformSingleAchievement($achievement, array $criterias)
    {
        $achievementCriterias = array_filter($criterias, function (AchievementCriteria $criteria) use ($achievement) {
            return $criteria->achievementId() === $achievement->id;
        });

        // Since we're dealing with owner-related criterias (progress exists if owner has any value),
        // we can simply count completed criterias & determine if achievement has been completed.
        $completedCriteriasCount = array_sum(
            array_map(function (AchievementCriteria $criteria) {
                return $criteria->completed() ? 1 : 0;
            }, $achievementCriterias)
        );

        return new Achievement([
            'id' => $achievement->id,
            'name' => $achievement->name,
            'description' => $achievement->description,
            'points' => $achievement->points,
            'completed' => count($achievementCriterias) === $completedCriteriasCount,
            'criterias' => $achievementCriterias,
        ]);
    }

    /**
     * Extracts single Achievement from given list for given criteria.
     *
     * @param AchievementCriteria $criteria
     * @param array               $achievements
     *
     * @return Achievement
     */
    public function getAchievementForCriteria(AchievementCriteria $criteria, array $achievements)
    {
        $collection = collect($achievements);

        $index = $collection->search(function (Achievement $achievement) use ($criteria) {
            return $achievement->id() === $criteria->achievementId();
        });

        if ($index === false) {
            throw new \InvalidArgumentException('Achievement for criteria #'.$criteria->id().' was not found.');
        }

        return $collection->get($index);
    }

    /**
     * Loads achievements with progresses for given owner.
     *
     * @param mixed $owner
     * @param array $achievementIds
     *
     * @return array
     */
    public function getAchievementsWithProgressFor($owner, array $achievementIds)
    {
        $criterias = $this->getCriteriasByAchievementIds($achievementIds);

        if (!count($criterias)) {
            return [];
        }

        $ownerCriteriaProgress = $this->getOwnerCriteriasProgress($owner, function ($query) use ($criterias) {
            $query->whereIn('achievement_criteria_model_id', $criterias->pluck('id'));
        });

        $achievementsCriterias = $criterias->map(function ($criteria) use ($ownerCriteriaProgress) {
            /** @var AchievementCriteriaModel $criteria */

            return $this->transformCriteriaWithProgress($criteria, $ownerCriteriaProgress->get($criteria->id));
        });

        return $this->getAchievementsByCriterias($achievementsCriterias->toArray());
    }

    /**
     * Loads criterias by achievement IDs.
     *
     * @param array $achievementIds
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCriteriasByAchievementIds(array $achievementIds)
    {
        return $this->getModelClass(static::MODEL_CRITERIA, function (string $className) use ($achievementIds) {
            return $className::whereIn('achievement_id', $achievementIds)->get();
        });
    }

    /**
     * Saves criteria progress for given owner.
     *
     * @param mixed                       $owner
     * @param AchievementCriteria         $criteria
     * @param Achievement                 $achievement
     * @param AchievementCriteriaProgress $progress
     *
     * @return mixed
     */
    public function setCriteriaProgressUpdated($owner, AchievementCriteria $criteria, Achievement $achievement, AchievementCriteriaProgress $progress)
    {
        $owner->achievementCriterias()->syncWithoutDetaching([
            $criteria->id() => [
                'value' => $progress->value,
                'completed' => $progress->completed,
                'progress_data' => json_encode($progress->data),
            ],
        ]);

        $this->dispatchEvent('criteria_updated', [$owner, $criteria, $achievement, $progress]);

        return true;
    }

    /**
     * Saves given achievements completeness state for given owner.
     *
     * @param mixed $owner
     * @param array $achievements
     *
     * @return mixed
     */
    public function setAchievementsCompleted($owner, array $achievements)
    {
        $now = Carbon::now();
        $patch = [];

        foreach ($achievements as $achievement) {
            $patch[$achievement->id()] = ['completed_at' => $now];
        }

        $owner->achievements()->syncWithoutDetaching($patch);

        $this->dispatchEvent('achievements_completed', [$owner, $achievements]);

        return true;
    }
}
