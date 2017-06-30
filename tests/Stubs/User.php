<?php

namespace Laravel\Achievements\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Laravel\Achievements\Traits\HasAchievements;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Model
{
    use HasAchievements;

    protected $table = 'users';
    protected $fillable = ['name'];
}
