<?php

namespace Laravel\Achievements;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Laravel\Achievements\Events\AchievementsCompleted;
use Laravel\Achievements\Events\CriteriaUpdated;
use Zurbaev\Achievements\Achievement;
use Zurbaev\Achievements\AchievementCriteria;
use Zurbaev\Achievements\AchievementCriteriaProgress;
use Zurbaev\Achievements\Contracts\AchievementsStorageInterface;

class AchievementsStorage implements AchievementsStorageInterface
{
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

        return $criterias->map(function (AchievementCriteriaModel $criteria) use ($ownerCriteriaProgress) {
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
        return AchievementCriteriaModel::where('type', $type)->get();
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
        return $criterias->keyBy('id')->map(function (AchievementCriteriaModel $criteria) {
            return new AchievementCriteriaProgress(
                intval($criteria->pivot->value),
                false,
                intval($criteria->pivot->completed) === 1
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
    protected function transformCriteriaWithProgress(AchievementCriteriaModel $criteria, AchievementCriteriaProgress $progress = null)
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

        $achievements = AchievementModel::whereIn('id', array_unique($achievementIds))->get();

        if (!count($achievements)) {
            return [];
        }

        return $this->transformAchievements($achievements, $criterias)->toArray();
    }

    /**
     * Converts collection of AchievementModel objects to array of Achievement objects.
     *
     * @param Collection $achievements
     *
     * @return array
     */
    public function convertAchievementModelsWithCriterias(Collection $achievements)
    {
        $achievements->load('criterias');

        return $achievements->map([$this, 'convertAchievementModelWithCriterias'])->toArray();
    }

    /**
     * Converts single AchievementModel to Achievement object.
     *
     * @param AchievementModel $achievement
     *
     * @return Achievement
     */
    public function convertAchievementModelWithCriterias(AchievementModel $achievement)
    {
        if (!$achievement->relationLoaded('criterias')) {
            $achievement->load('criterias');
        }

        $criterias = $achievement->criterias->map(function (AchievementCriteriaModel $criteria) {
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
        return $achievements->map(function (AchievementModel $achievement) use ($criterias) {
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
    protected function transformSingleAchievement(AchievementModel $achievement, array $criterias)
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
        /**
         * @var \Illuminate\Database\Eloquent\Collection $criterias
         */
        $criterias = AchievementCriteriaModel::whereIn('achievement_id', $achievementIds)->get();

        if (!count($criterias)) {
            return [];
        }

        $ownerCriteriaProgress = $this->getOwnerCriteriasProgress($owner, function ($query) use ($criterias) {
            $query->whereIn('achievement_criteria_model_id', $criterias->pluck('id'));
        });

        $achievementsCriterias = $criterias->map(function (AchievementCriteriaModel $criteria) use ($ownerCriteriaProgress) {
            return $this->transformCriteriaWithProgress($criteria, $ownerCriteriaProgress->get($criteria->id));
        });

        return $this->getAchievementsByCriterias($achievementsCriterias->toArray());
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
            ],
        ]);

        Event::dispatch(new CriteriaUpdated($owner, $criteria, $achievement, $progress));

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

        Event::dispatch(new AchievementsCompleted($owner, $achievements));

        return true;
    }
}
