# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.9.5] - 2016-07-03
### Changed
- Package now uses model names from config file instead of hardcoded names;
- Some `AchievementsStorage` methods were refactored to make it more easier to override default Storage behaviour;
- `php artisan vendor:publish --provider="Laravel\Achievements\Providers\AchievementsServiceProvider" --tag=config` is required to be run after this update.

## [0.9.4] - 2016-07-02
### Changed
- `LaravelAchievements::criteriaUpdated` renamed to `updateCriterias`.

## [0.9.3] - 2017-07-02
### Added
- `progress_data` column to `achievement_criteriables` table;
- `AchievementModel::completed` method to determine if achievement was completed;
- Pass `progress_data` field to `AchievementCriteriaProgress` constructor;
- Save `AchievementCriteriaProgress::$data` value to `achievement_criteriables` in `AchievementsStorage::setCriteriaProgressUpdated`.

## [0.9.2] - 2017-06-30
### Changed
- `achievement_criteriables.completed` column type was changed from `boolean` to `tinyInteger`;
- `achievement_criterias.requirements` column type was changed from `json` to `text`;
- Code updated according to these changes.

## [0.9.1] - 2017-06-30
### Fixed
- Criterias registation bug.

## [0.9.0] - 2017-06-30
### Initial release.

[Unreleased]: https://github.com/tzurbaev/laravel-achievements/compare/0.9.5...HEAD
[0.9.5]: https://github.com/tzurbaev/laravel-achievements/compare/0.9.4...0.9.5
[0.9.4]: https://github.com/tzurbaev/laravel-achievements/compare/0.9.3...0.9.4
[0.9.3]: https://github.com/tzurbaev/laravel-achievements/compare/0.9.2...0.9.3
[0.9.2]: https://github.com/tzurbaev/laravel-achievements/compare/0.9.1...0.9.2
[0.9.1]: https://github.com/tzurbaev/laravel-achievements/compare/0.9.0...0.9.1
[0.9.0]: https://github.com/tzurbaev/laravel-achievements/releases/tag/0.9.0
