# Changelog

All notable changes to **Blend** will be documented in this file.

<br />

## [[1.0.9] - 2021-10-06](https://github.com/MarwanAlsoltany/blend/compare/v1.0.8...v1.0.9)
- Update `blend.php`:
    - Add a check for `PHAR` extension to make it optional.
- Update `setup` executable:
    - Fix wrong setup file path in script log.

<br />

## [[1.0.8] - 2021-09-15](https://github.com/MarwanAlsoltany/blend/compare/v1.0.7...v1.0.8)
- Update `TaskRunner` class:
    - Add new parameter `$escape` to `exec()` method to control whether command(s) should be escaped or not.
    - Fix an issue in `checkEnvironment()` method with Windows absolute paths.

<br />

## [[1.0.7] - 2021-09-13](https://github.com/MarwanAlsoltany/blend/compare/v1.0.6...v1.0.7)
- Update `TaskRunner` class:
    * Add new `$results` property.
    * Add `getExecResult()` method.
    * Refactor `exec()` method to cache executed commands results.
    - Fix wrong `addTask()` method call in `makeTask()` method.
    - Bump package version.
- Update `TaskRunnerTest` class:
    - Add `getExecResult()` method test.

<br />

## [[1.0.6] - 2021-09-11](https://github.com/MarwanAlsoltany/blend/compare/v1.0.5...v1.0.6)
- Update `TaskRunner` class:
    - Add `makeTask()` method.
    - Update `runTask()` method.
    - Update `exec()` method.
    - Fix some typos in methods DocBlocks.
    - Bump package version.
- Update `TaskRunnerTest` class:
    - Add `makeTask()` method test.

<br />

## [[1.0.5] - 2021-08-25](https://github.com/MarwanAlsoltany/blend/compare/v1.0.4...v1.0.5)
- Update `TaskRunner` class:
    - Refactor `exec()` method.
    - Bump package version.
- Update `blend.config.php`:
    - Fix formatting and update comments.
    - Remove some keys that were introduced by mistake.

<br />

## [[1.0.4] - 2021-08-24](https://github.com/MarwanAlsoltany/blend/compare/v1.0.3...v1.0.4)
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
