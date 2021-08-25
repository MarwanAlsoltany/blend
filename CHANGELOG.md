# Changelog

All notable changes to **Blend** will be documented in this file.

<br />

## [[1.0.4] - 2021-08-25](https://github.com/MarwanAlsoltany/blend/compare/v1.0.4...v1.0.5)
- Update `TaskRunner` class:
    - Refactor `exec()` method.
    - Bump package version.
- Update `blend.config.php`:
    - Fix formatting and update comments.
    - Remove some keys that were introduced by mistake.

<br />

## [[1.0.3] - 2021-08-24](https://github.com/MarwanAlsoltany/blend/compare/v1.0.3...v1.0.4)
- Update `TaskRunner` class:
    - Add new `$path` property.
    - Add `__toString()` magic method.
    - Add `@runner` placeholder.
    - Remove fallback values of config tasks in `bootstrap()` method.
    - Update properties and methods DocBlocks.
    - Bump package version.
- Update `blend.php`:
    - Update `config:generate` task.
- Update `TaskRunnerTest` class:
    - Add `__toString()` magic method test.

<br />

## [[1.0.3] - 2021-08-24](https://github.com/MarwanAlsoltany/blend/compare/v1.0.2...v1.0.3)
- Update `TaskRunner` class:
    - Update `TRANSLATIONS` class constant to prevent unexpected name translation.
    - Bump package version.
- Update `blend.php`:
    - Add `phar:update` task.
    - Add `declare(strict_types=1)` declaration.
    - Add namespace to the file.

<br />

## [[1.0.2] - 2021-08-23](https://github.com/MarwanAlsoltany/blend/compare/v1.0.1...v1.0.2)
- Update `TaskRunner` class:
    - Add `SUCCESS` class constant.
    - Add `FAILURE` class constant.
    - Update class some DocBlocks.
    - Update class methods to make use of the new constants.
    - Update value of `$id` property to reflect only executable name (used to reflect the entire path).
    - Remove unnecessary operations from `$name` and `$envVar` properties.
    - Remove unused parameter from `load()` method.
    - Make `$config` property not nullable.
    - Bump package version.

<br />

## [[1.0.1] - 2021-08-23](https://github.com/MarwanAlsoltany/blend/compare/v1.0.0...v1.0.1)
- Update `composer.json`:
    - Change package type form `project` to `library`.
- Update `TaskRunner` class:
    - Bump package version.

<br />

## [[1.0.0] - 2021-08-23](https://github.com/MarwanAlsoltany/blend/commits/v1.0.0)
- Initial release.

<br />

## [Unreleased]

<br />
