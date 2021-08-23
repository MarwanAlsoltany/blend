<h1 align="center"><i>Blend</i></h1>

<div align="center">

A versatile and lightweight PHP task runner, designed with simplicity in mind.


[![PHP Version][php-icon]][php-href]
[![Latest Version on Packagist][version-icon]][version-href]
[![Total Downloads][downloads-icon]][downloads-href]
[![License][license-icon]][license-href]
[![Maintenance][maintenance-icon]][maintenance-href]
[![Travis Build Status][travis-icon]][travis-href]

[![Open in Visual Studio Code][vscode-icon]][vscode-href]

[![Tweet][tweet-icon]][tweet-href]



<details>
<summary>Table of Contents</summary>
<p>

[About Blend](#about-blend)<br/>
[Installation](#installation)<br/>
[Config](#config)<br/>
[Examples](#examples)<br/>
[Changelog](./CHANGELOG.md)

</p>
</details>

<br/>
<small>If you like this project and would like to support its development, giving it a :star: would be appreciated!</small>

</div>


---


<div align="center">

<img src="./blend-demo.gif" width="850" />

</div>


---


## Key Features

1. Blazing fast
2. Easy to configure
3. Dependency free


---


## About Blend

Blend is a versatile and lightweight PHP task runner, designed with simplicity in mind. Blend was created out of frustration of trying to manage multiple CLI Tools and/or Scripts with different interfaces and trying to remember their names, locations, syntax, and options/arguments. With Blend and its intuitive and powerful API you will have to do this only once, it will, well, as the name suggests *blend* it for you. Blend will present those CLI Tools and/or Scripts the way you like and give you the possibility to access all of them in the form of tasks from a single, beautiful, and insightful interface that will assist you, give you feedback, and suggestions throughout the process.

To keep it as portable and as simple as it can be, the Blend package consists of a single class ([`TaskRunner`](./src/TaskRunner.php)), this class does all the magic for you.

Blend was created to be flexible, it can be installed in many ways, each way has its benefits and drawbacks. Choose the installation method that suits you and your needs best. Check out the [installation](#installation) section for more details.

#### Why does Blend exist?

Blend was developed for personal use in the first place. However, it has come so far that it deserves to be published. It may not be what you are looking for, so check it out carefully.

---


## Installation

#### Using Composer:

Require Blend through Composer using:

```sh
composer require marwanalsoltany/blend
```

This is the recommended way to install Blend. With this installation method, Blend will be installed just like any normal Composer package. You can interact with it using either the `vendor/bin/blend` executable with a config file in the current working directory, or by requiring it in a stand-alone file and supplying the config programmatically after creating you own executable file. You can of course install it globally and let it be system-wide accessible.

#### Using PHAR:

Download Blend PHAR file form the [releases](https://github.com/MarwanAlsoltany/blend/releases) page or using the command down below (do not forget to adjust the version segment):

```sh
php -r "copy('https://github.com/MarwanAlsoltany/blend/releases/download/vX.X.X/blend.phar', 'blend');"
```

With this installation method, you will get Blend as a portable PHAR file, you can place it anywhere you want or even include it in your `PATH` for easy access. With this installation method, you have to supply a config file in order to configure/customize Blend. This installation method exists for portability and where Blend is not bound to a specific project.

#### Using Installer:

Download Blend Setup directly from the repository, or using the command down below:

```sh
php -r "copy('https://raw.githubusercontent.com/MarwanAlsoltany/blend/master/bin/setup', 'setup');" && php setup
```

Using this method, the Blend executable and source will be installed in the current working directory (to take advantage of IDEs Intellisense). With this installation method, you can configure Blend programmatically using the `blend` executable file or by supplying a config file in the current working directory. This installation method exists merely for legacy projects, where Composer is not an option.


---


## Config

Blend can be configured using either of the two available configuration formats:

#### PHP Config `blend.config.php`

```php
<?php return [

    // (string|null) The autoload file to use (useful when using PHP Callables as tasks executables).
    'autoload' => null,

    // (bool|null) Whether or not to merge the supplied executables/translations with the default ones.
    'merge' => true,

    // (array[]|null) The executables to load.
    'executables' => [
        'php' => [
            './bin/*',
        ],
    ],

    // (string[]|null) The translations to apply.
    'translations' => [
        'abc' => 'xyz',
    ],

    // (bool|null) Whether or not to turn on ANSI colors for the output.
    'ansi' => true,

    // (bool|null) Whether or not to turn on the output.
    'quiet' => false,

    // (array[]|null) The tasks to add.
    'tasks' => [

        // (array|null) Task definition (key = Task fallback name, value = Task parameters).
        'some:task' => [

            // (string|null) If not specified, the key of the containing array will be used instead.
            'name' => 'some:task',

            // (string|null) If not specified a fallback will be used instead.
            'description' => 'Some task',

            // (string) Valid values are 'shell', 'callback', or any available program (PHP for example).
            'executor' => 'shell',

            // (string|callable) Depending on the executor, either a string containing command name or a path to a file, or a valid php callable.
            'executable' => 'ls',

            // (string|array|null) Depending on the executor, either a string containing command options/arguments, or an array of arguments to pass to the callback.
            'arguments' => '-lash',

            // (bool|null) Whether or not to hide the task from being listed.
            'hidden' => false,

            // (bool|null) Whether or not to prevent the task from being ran.
            'disabled' => false,

        ],

    ],

];
```

#### JSON Config `blend.config.json` (Recommended)

```jsonc
{
    // Check out the PHP config example for data types

    "autoload": null,
    "merge": true,
    "executables": {
        "php": [
            "./bin/*"
        ]
    },
    "translations": {
        "abc": "xyz"
    },
    "ansi": true,
    "quiet": false,
    "tasks": {
        "some:task": {
            "name": "some:task",
            "description": "Some task",
            "executor": "shell",
            "executable": "ls",
            "arguments": "-lash",
            "hidden": false,
            "disabled": false
        }
    }
}
```

#### How Does Config Loading Work?

Blend will try to load the config from the current working directory, if nothing is to be found, it will go one level upwards and look in the parent directory and so on until it reaches the root directory. If it does not find anything there either, Blend will start without config.

![#1e90ff](https://via.placeholder.com/11/1e90ff/000000?text=+) **Fact:** *Although JSON config format is recommended, PHP config has precedence. This means, if the two config formats are to be found in the same working, the PHP config will get loaded instead of the JSON one. This is merely because the PHP config can be executed and is, therefore, more powerful.*


---


## Examples

#### A basic Blend executable:

```php
<?php

use MAKS\Blend\TaskRunner as Blend;


$blend = new Blend();
$blend->start();
```

#### A more advanced Blend executable:

```php
<?php

use MAKS\Blend\TaskRunner as Blend;


$blend = new Blend([
    // files in "./php/bin" will be loaded as tasks and get executed using PHP
    'php' => [
        './php/bin/*',
    ],
    // files in "./js/bin" with the JS extension will be loaded as tasks and get executed using Node
    'node' => [
        './js/bin/*.js',
    ],
]);

$blend->setName('My Task Runner');
$blend->setVersion('vX.X.X');

// these tasks are for demonstration purposes only

$blend->addShellTask('ls', 'Lists content of the current directory or the passed one.', 'ls', '-lash');

$blend->addCallbackTask('whoami', null, function () {
    /** @var Blend $this */
    $this->say('@task'); // using the @task placeholder
});
$blend->disableTask('whoami');

$blend->addCallbackTask('server:start', 'Starts a PHP Development Server in CWD', function () {
    /** @var Blend $this */
    if (file_exists(getcwd() . '/.pid.server')) {
        $this->say('An already started PHP Development Server has been found.');

        return 1;
    }

    $pid = $this->exec('php -S localhost:8000 -t ' . getcwd(), true);
    $this->say('Started a PHP Development Server in the background with PID: ' . $pid);

    file_put_contents(getcwd() . '/.pid.server', $pid);

    return 0;
});

$blend->addCallbackTask('server:stop', 'Stops a started PHP Development Server in CWD', function () {
    /** @var Blend $this */
    if (!file_exists(getcwd() . '/.pid.server')) {
        $this->say('No started PHP Development Server has been found.');

        return 1;
    }

    $pid = trim(file_get_contents(getcwd() . '/.pid.server'));

    $this->exec((PHP_OS === 'WINNT' ? 'tskill ' : 'kill -15 ') . $pid);
    $this->say('Stopped PHP Development Server with PID: ' . $pid);

    unlink(getcwd() . '/.pid.server');
});

$blend->addCallbackTask('server:restart', 'Restarts the started PHP Development Server in CWD', function () {
    /** @var Blend $this */
    $this
        ->run('server:stop') // use the runTask() method instead to get the return value of the called task
        ->run('server:start')
        ->say('Restarted PHP Development Server');
});

$blend->sort();
$blend->start();
```

![#ff6347](https://via.placeholder.com/11/f03c15/000000?text=+) **Note:** *Blend gets its ID from the executable name that contains it (`$argv[0]`). So if you were to rename the file that contains it to something else, all Blend output will reflect this new change (help message, suggestions, etc...). The environment variable and the config file name will also be expected to match the new name.*

![#32cd32](https://via.placeholder.com/11/32cd32/000000?text=+) **Advice:** *The [`TaskRunner`](./src/TaskRunner.php) class is well documented, if you have any questions about Blend API, refer to the DocBlocks of its methods, you will probably find your answer there.*


---


## License

Blend is an open-source project licensed under the [**MIT**](./LICENSE) license.
<br/>
Copyright (c) 2021 Marwan Al-Soltany. All rights reserved.
<br/>










[php-icon]: https://img.shields.io/badge/php-%3D%3C7.4-yellow?style=flat&logo=php
[version-icon]: https://img.shields.io/packagist/v/marwanalsoltany/blend.svg?style=flat&logo=packagist
[downloads-icon]: https://img.shields.io/packagist/dt/marwanalsoltany/blend.svg?style=flat&logo=packagist
[license-icon]: https://img.shields.io/badge/license-MIT-red.svg?style=flat&logo=github
[maintenance-icon]: https://img.shields.io/badge/maintained-yes-orange.svg?style=flat&logo=github
[travis-icon]: https://img.shields.io/travis/com/MarwanAlsoltany/blend/master.svg?style=flat&logo=travis
[vscode-icon]: https://open.vscode.dev/badges/open-in-vscode.svg
[tweet-icon]: https://img.shields.io/twitter/url/http/shields.io.svg?style=social

[php-href]: https://github.com/MarwanAlsoltany/blend/search?l=php
[version-href]: https://packagist.org/packages/marwanalsoltany/blend
[downloads-href]: https://packagist.org/packages/marwanalsoltany/blend/stats
[license-href]: ./LICENSE
[maintenance-href]: https://github.com/MarwanAlsoltany/blend/graphs/commit-activity
[travis-href]: https://travis-ci.com/MarwanAlsoltany/blend
[vscode-href]: https://open.vscode.dev/MarwanAlsoltany/blend
[tweet-href]: https://twitter.com/intent/tweet?url=https%3A%2F%2Fgithub.com%2FMarwanAlsoltany%2Fblend&text=A%20versatile%20and%20lightweight%20PHP%20task%20runner%2C%20designed%20with%20simplicity%20in%20mind.
