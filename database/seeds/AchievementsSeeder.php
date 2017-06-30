<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementsSeeder extends Seeder
{
    public function run()
    {
        $createdAt = Carbon::now()->format('Y-m-d H:i:s');

        $achievements = [
            [
                'id' => 1,
                'name' => 'Level 10',
                'description' => 'Reach Level 10.',
                'points' => 10,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'criterias' => [
                    [
                        'type' => 'reach_level',
                        'name' => 'Reach level 10',
                        'max_value' => 10,
                    ]
                ],
            ],
            [
                'id' => 2,
                'name' => 'Level 20',
                'description' => 'Reach Level 20.',
                'points' => 10,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'criterias' => [
                    [
                        'type' => 'reach_level',
                        'name' => 'Reach level 20',
                        'max_value' => 20,
                    ]
                ],
            ],
            [
                'id' => 3,
                'name' => 'Level 30',
                'description' => 'Reach Level 30.',
                'points' => 10,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'criterias' => [
                    [
                        'type' => 'reach_level',
                        'name' => 'Reach level 30',
                        'max_value' => 30,
                    ]
                ],
            ],
            [
                'id' => 4,
                'name' => '50 Quests Completed',
                'description' => 'Complete 50 quests.',
                'points' => 10,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'criterias' => [
                    [
                        'type' => 'complete_quests',
                        'name' => 'Complete 50 quests',
                        'max_value' => 50,
                    ]
                ],
            ],
        ];

        foreach ($achievements as $achievement) {
            $this->createAchievement($achievement);
        }
    }

    protected function createAchievement(array $achievement)
    {
        DB::table('achievements')->insert(
            array_only($achievement, ['id', 'name', 'description', 'points', 'created_at', 'updated_at'])
        );

        foreach ($achievement['criterias'] as $criteria) {
            DB::table('achievement_criterias')->insert(
                array_merge($criteria, ['achievement_id' => $achievement['id']])
            );
        }
    }
}
