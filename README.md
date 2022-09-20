<h1 align="center"><i>Blend</i></h1>

<div align="center">

A versatile and lightweight PHP task runner, designed with simplicity in mind.


[![PHP Version][php-icon]][php-href]
[![Latest Version on Packagist][version-icon]][version-href]
[![Packagist Downloads][downloads-icon]][downloads-href]
[![GitHub Downloads][github-downloads-icon]][github-downloads-href]
[![License][license-icon]][license-href]
[![Maintenance][maintenance-icon]][maintenance-href]
[![Travis Build Status][travis-icon]][travis-href]
[![codecov][codecov-icon]][codecov-href]

[![Open in Visual Studio Code][vscode-icon]][vscode-href] [![Run on Repl.it][replit-icon]][replit-href]

[![Tweet][tweet-icon]][tweet-href] [![Star][github-icon]][github-href]


<details>
<summary>Table of Contents</summary>
<p>

[About Blend](#about-blend)<br/>
[Installation](#installation)<br/>
[Config](#config)<br/>
[Examples](#examples)<br/>
[API](#api)<br/>
[Changelog](./CHANGELOG.md)

</p>
</details>

<br/>

<sup>If you like this project and would like to support its development, giving it a :star: would be appreciated!</sup>

<br/>

![Blend Demo](./blend-demo.gif)

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

This is the recommended way to install Blend. With this installation method, Blend will be installed just like any normal Composer package. You can interact with it using either the `vendor/bin/blend` executable with a config file in the current working directory, or by requiring it in a stand-alone file and supplying the config programmatically. You can of course install it globally and let it be system-wide accessible.

#### Using PHAR:

Download Blend PHAR archive form the [releases](https://github.com/MarwanAlsoltany/blend/releases) page or using one of the commands down below:

```sh
php -r "copy('https://git.io/JEseO', 'blend');"
```

```sh
php -r "copy('https://github.com/MarwanAlsoltany/blend/releases/latest/download/blend.phar', 'blend');"
```

Or use the following command to download a specific version (replace `vX.X.X` with the wished version):

```sh
php -r "copy('https://github.com/MarwanAlsoltany/blend/releases/download/vX.X.X/blend.phar', 'blend');"
```

With this installation method, you will get Blend as a portable PHAR archive, you can place it anywhere you want or even include it in your `PATH` for easy access. With this installation method, you have to supply a config file in order to configure/customize Blend. This installation method exists for portability where Blend is not bound to a specific project and a config file is sufficient. Starting from `v1.0.3`, the PHAR installation method is distinguished from other methods with the task `update` (used to be called ~~`phar:update`~~ before `v1.1.0`) that will update your PHAR to the latest release available in this repository.

#### Using Installer:

Download Blend Setup directly from the [repository](./bin/setup), or using one of the commands down below:

```sh
php -r "copy('https://git.io/JEseR', 'setup');" && php setup
```

```sh
php -r "copy('https://raw.githubusercontent.com/MarwanAlsoltany/blend/master/bin/setup', 'setup');" && php setup
```

Using this method, the Blend executable and source will be installed in the current working directory (to take advantage of IDEs Intellisense when configuring Blend programmatically). With this installation method, you can configure Blend programmatically using the `blend` executable file or by supplying a config file in CWD. This installation method exists merely for legacy projects, where Composer is not an option and programmable config is required.


---


## Config

Blend can be configured using either of the two available config formats:

#### PHP Config [`blend.config.php`](./config/blend.config.php):

```php

<?php return [
    'autoload' => null,
    'merge' => true,
    'executables' => [
        'php' => [
            './bin/*',
        ],
    ],
    'translations' => [
        'abc' => 'xyz',
    ],
    'ansi' => true,
    'quiet' => false,
    'tasks' => [
        'some:task' => [
            'name' => 'some:task',
            'description' => 'Some task',
            'executor' => 'shell',
            'executable' => 'ls',
            'arguments' => '-lash',
            'hidden' => false,
            'disabled' => false,
        ],
    ],
];

```

#### JSON Config [`blend.config.json`](./config/blend.config.json): (Recommended)

```json

{
    "$schema": "https://raw.githubusercontent.com/MarwanAlsoltany/blend/master/config/schema.json",
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

![■](https://user-images.githubusercontent.com/7969982/182090864-09a2573a-59e3-4c82-bf9f-e2b9cd360c27.png) **Note:** *Refer to [`config/blend.config.php`](./config/blend.config.php) and [`config/schema.json`](./config/schema.json) to learn more about the expected data types. Note that JSON config has some limitations (callback tasks for example) so check out to both files.*

#### How Does Config Loading Work?

Blend will try to load the config from the current working directory, if nothing is to be found there, it will go one level upwards and look in the parent directory and so on until it reaches the root directory, if it does not find anything there either, Blend will start without config.

![■](https://user-images.githubusercontent.com/7969982/182090858-f98dc83e-da1c-4f14-a538-8ac0a9bc43c3.png) **Fact:** *Although JSON config format is recommended, PHP config has precedence. This means, if the two config formats are to be found in the same directory, the PHP config will get loaded instead of the JSON one. This is merely because the PHP config can be executed and is, therefore, more powerful.*


---


## Examples

#### A basic Blend executable:

```php

#!/usr/bin/env php
<?php

use MAKS\Blend\TaskRunner as Blend;


$blend = new Blend();
$blend->start();
```

#### A more advanced Blend executable:

```php
#!/usr/bin/env php
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
]); // available arguments: $executables, $translations, $config, $ansi, $quiet

$blend->setName('My Task Runner');
$blend->setVersion('vX.X.X');

// NOTE: these tasks are for demonstration purposes only

// adding a shell task
$blend->addShellTask('ls', 'Lists content of CWD or the passed one.', 'ls', '-lash');

// adding a callback task
$blend->addCallbackTask('whoami', null, function () {
    /** @var Blend $this */
    $this->say('@task'); // using the @task placeholder to get a string representation of the task object
});
$blend->disableTask('whoami'); // preventing the task from being ran
$blend->hideTask('whoami'); // preventing the task from being listed

// alternatively, you can use the makeTask() method to pass all arguments at once
$blend->makeTask([
    'name'        => 'runner',
    'description' => null,
    'executor'    => Blend::CALLBACK_TASK,
    'executable'  => static function ($runner) {
        // functions that can't be bound, get the runner as the first argument
        $runner->say('@runner');
    },
    'arguments'   => null,
    'hidden'      => true,
    'disabled'    => true,
]);

// extending the task runner with an additional method
// NOTE: this implementation is for demonstration purposes only
$blend->extend('pipe', function (string ...$tasks) {
    $results = [];

    static $previous = null;

    foreach ($tasks as $task) {
        $current = $this->getTask($task);

        // skip if task does not exist
        if (!$current) {
            continue;
        }

        // pass the result as an argument if the next task is a callback task
        if ($current->executor === Blend::CALLBACK_TASK) {
            $current->arguments[] = $previous;

            $previous = $results[$task] = $this->runTask($task);

            continue;
        }

        // if not a callback task, then it is a shell task (CLI command)
        // execute the task and cache the result for the next task
        $this->runTask($task);
        // with shell tasks, we're interested in the whole result of the task (command)
        // and not only its exit code, that's why we're using getExecResults() method instead
        $previous = $results[$task] = $this->getExecResult();
    }

    return $results;
});

// now you can use the newly created method to pipe tasks together
$blend->addCallbackTask('piped:tasks:run', 'Executes piped tasks.', function () {
    /** @var Blend $this */
    $this->say('Running piped tasks ...');
    $this->pipe(
        'example:task:1',
        'example:task:2',
        'example:task:3'
        // ...
    );
    $this->say('Finished piping!');
});

$blend->sort();
$blend->start();

```

#### A real life example of a Blend executable (PHP Development Server):

```php

#!/usr/bin/env php
<?php

use MAKS\Blend\TaskRunner as Blend;


$blend = new Blend();

$cwd = getcwd();

$blend->addCallbackTask(
    'server:start',
    'Starts a PHP Development Server in CWD',
    function ($cwd) {
        /** @var Blend $this */
        if (file_exists("{$cwd}/.pid.server")) {
            $this->say('An already started PHP Development Server has been found.');

            return Blend::FAILURE;
        }

        $pid = $this->exec("php -S localhost:8000 -t {$cwd}", true); // passing true runs the command asynchronously
        // you can use $this->getExecResult() method to get all additional info about the executed command.
        $this->say("Started a PHP Development Server in the background with PID: [{$pid}]");

        file_put_contents("{$cwd}/.pid.server", $pid);

        return Blend::SUCCESS;
    },
    [$cwd] // passing arguments to tasks callback
);

$blend->addCallbackTask(
    'server:stop',
    'Stops a started PHP Development Server in CWD',
    function ($cwd) {
        /** @var Blend $this */
        if (!file_exists("{$cwd}/.pid.server")) {
            $this->say('No started PHP Development Server has been found.');

            return Blend::FAILURE;
        }

        $pid = trim(file_get_contents("{$cwd}/.pid.server"));

        $this->exec(PHP_OS === 'WINNT' ? "tskill {$pid}" : "kill -15 {$pid}");
        $this->say("Stopped PHP Development Server with PID: [{$pid}]");

        unlink("{$cwd}/.pid.server");

        return Blend::SUCCESS;
    },
    [$cwd]
);

$blend->addCallbackTask(
    'server:restart',
    'Restarts the started PHP Development Server in CWD',
    function () {
        /** @var Blend $this */
        $this->say('Restarting the PHP Development Server');

        $this
            ->setQuiet(true) // disable output temporarily
            ->run('server:stop')
            ->run('server:start')
            ->setQuiet(false); // enable output again

        // use the runTask() method instead to get the return value of the called task
        // return $this->runTask('server:stop') & $this->runTask('server:start');
    }
);

$blend->addCallbackTask(
    'server:cleanup',
    'Removes ".pid.server" file from CWD if available',
    function ($cwd) {
        /** @var Blend $this */
        if (file_exists($file = "{$cwd}/.pid.server")) {
            if (unlink($file)) {
                $this->say('Removed ".pid.server" file successfully.');
            } else {
                $this->say('Failed to remove ".pid.server" file!');

                return Blend::FAILURE;
            }
        } else {
            $this->say('Nothing to clean up!');
        }

        return Blend::SUCCESS;
    },
    [$cwd]
);

$blend->start();

```

![■](https://user-images.githubusercontent.com/7969982/182090864-09a2573a-59e3-4c82-bf9f-e2b9cd360c27.png) **Note:** *Blend gets its ID from the executable name that contains it (`$argv[0]`). So if you were to rename the file that contains it to something else, all Blend output will reflect this new change (help message, suggestions, etc...). The environment variable and the config file name will also be expected to match the new name.*

![■](https://user-images.githubusercontent.com/7969982/182090863-c6bf7159-7056-4a00-bc97-10a5d296c797.png) **Hint:** *The [`TaskRunner`](./src/TaskRunner.php) class is well documented, if you have any questions about Blend API, refer to the DocBlocks of its methods, you will probably find your answer there.*


---


## API

Here is the full API of Blend ([`TaskRunner`](./src/TaskRunner.php) class).

![■](https://user-images.githubusercontent.com/7969982/182090864-09a2573a-59e3-4c82-bf9f-e2b9cd360c27.png) **Note:** *The full API of the `TaskRunner::class` —including private and protected members— is listed here as you mostly want to extend Blend by using the `TaskRunner::extend()` method which has access to the private scope.*

#### Constants

| Constant | Description |
|-|-|
| `VERSION` | Package version. (public) |
| `EXECUTABLES` | Default executables. (public) |
| `TRANSLATIONS` | Default task name translations. (public) |
| `CONFIG` | Default config. (public) |
| `SUCCESS` | Task success code. (public) |
| `FAILURE` | Task failure code. (public) |
| `CALLBACK_TASK` | Task type callback. (public) |
| `SHELL_TASK` | Task type shell. (public) |
| `INTERNAL_TASK` | Task type internal. (protected) |

#### Properties

| Property | Description |
|-|-|
| `$argc` | A reference to the `$argc` global variable. (public) |
| `$argv` | A reference to the `$argv` global variable. (public) |
| `$args` | An array of the arguments that could be passed to the executed task. (public) |
| `$envVar` | Environment variable. (private) |
| `$path` | Task runner path. (protected) |
| `$id` | Task runner ID. (protected) |
| `$name` | Task runner name. (protected) |
| `$version` | Task runner version. (protected) |
| `$task` | The current task name passed to the task runner. (protected) |
| `$tasks` | Task runner tasks. (protected) |
| `$methods` | Magic methods added via `self::extend()`. (protected) |
| `$results` | The results of commands executed via `self::exec()`. (protected) |
| `$executables` | The executables that will be loaded. (protected) |
| `$translations` | The translations that will be applied to tasks names. (protected) |
| `$config` | The currently loaded configuration. (protected) |
| `$ansi` | Whether or not to turn on ANSI colors for the output. (protected) |
| `$quiet` | Whether or not to turn on the output. (protected) |

#### Public Methods

| Method | Description |
|-|-|
| `extend()` | Extends the class with a magic method using the passed callback. |
| `passthru()` | Executes a shell command using `passthru()`. |
| `exec()` | Executes a shell command synchronously or asynchronous and prints out its result if possible. |
| `getExecResult()` | Returns the result of a command executed via `self::exec()`. |
| `addCallbackTask()` | Adds a task that executes the passed callback. |
| `addShellTask()` | Adds a task that can be executed by the used shell (Bash for example). |
| `addTask()` | Adds a new task. |
| `makeTask()` | Makes a task from array representation of a task object and adds it to the available tasks. |
| `removeTask()` | Removes a task from the available tasks. |
| `hideTask()` | Hides a task by preventing it from being listed. The task can still get ran though. |
| `disableTask()` | Disables a task by preventing it from being ran. The task will still get listed, but will be obfuscated. |
| `getTask()` | Returns a task. |
| `getTasks()` | Returns all tasks. |
| `runTask()` | Runs a task. |
| `run()` | Runs a task or starts the runner if no parameter is specified or the task is not found. |
| `say()` | Writes a message out to the console. |
| `sort()` | Sorts the tasks alphabetically. |
| `start()` | Starts the task runner. |
| `getName()` | Returns the task runner name. |
| `setName()` | Sets the task runner name. |
| `getVersion()` | Returns the task runner version. |
| `setVersion()` | Sets the task runner version. |
| `isAnsi()` | Returns whether the task runner output is currently using ANSI colors or not. |
| `setAnsi()` | Sets the task runner ANSI output value. |
| `isQuiet()` | Returns whether the task runner output is currently quiet or not. |
| `setQuiet()` | Sets the task runner quiet output value. |

#### Protected Methods

| Method | Description |
|-|-|
| `terminate()` | Terminates the task runner by exiting the script. |
| `bootstrap()` | Bootstraps the task runner by adding predefined tasks. |
| `load()` | Loads tasks from the specified executables array. |
| `translate()` | Translates the passed string using the specified translations. |
| `format()` | Formats a string like `*printf()` functions with the ability to add ANSI colors. |
| `write()` | Writes out a formatted text block from the specified lines and format value. |
| `displayHelp()` | Prints out a help message listing all tasks of the task runner. |
| `displayHint()` | Prints out a hint message listing tasks matching the current task of the task runner. |
| `displayList()` | Prints out a list of all available tasks of the task runner. |
| `displayExec()` | Prints out the result of executing the current argument of the task runner. |
| `listTasks()` | Prints out a list of the passed tasks. |
| `getUser()` | Returns the task runner user. |

#### Private Methods

| Method | Description |
|-|-|
| `registerHandlers()` | Registers error handler, exception handler, and shutdown function. |
| `restoreHandlers()` | Restores the error handler and the exception handler. |
| `checkEnvironment()` | Checks the environment for TR_* variable, validates its pattern and updates class internal state. |
| `checkConfiguration()` | Checks the CWD or its parent(s) for a configuration file, validates its entries and updates class internal state. |

#### Magic Methods

| Method | Description |
|-|-|
| `handleError()` | Error handler function. (public) |
| `handleException()` | Exception handler function. (public) |
| `shutdown()` | Shutdown function. This method is abstract, implement it using `self::extend()`. (public) |


---


## License

Blend is an open-source project licensed under the [**MIT**](./LICENSE) license.
<br/>
Copyright (c) 2021 Marwan Al-Soltany. All rights reserved.
<br/>










[php-icon]: https://img.shields.io/badge/php-%3D%3C7.4-yellow?style=flat&logo=php
[version-icon]: https://img.shields.io/packagist/v/marwanalsoltany/blend.svg?style=flat&logo=packagist
[downloads-icon]: https://img.shields.io/packagist/dt/marwanalsoltany/blend.svg?style=flat&logo=packagist
[github-downloads-icon]: https://img.shields.io/github/downloads/MarwanAlsoltany/blend/total?logo=github&label=downloads
[license-icon]: https://img.shields.io/badge/license-MIT-red.svg?style=flat&logo=github
[maintenance-icon]: https://img.shields.io/badge/maintained-yes-orange.svg?style=flat&logo=github
[travis-icon]: https://img.shields.io/travis/com/MarwanAlsoltany/blend/master.svg?style=flat&logo=travis
[codecov-icon]: https://codecov.io/gh/MarwanAlsoltany/blend/branch/master/graph/badge.svg?token=NWAB1UV4TT
[vscode-icon]: https://img.shields.io/static/v1?logo=visualstudiocode&label=&message=Open%20in%20VS%20Code&labelColor=2c2c32&color=007acc&logoColor=007acc
[replit-icon]: https://img.shields.io/static/v1?logo=replit&label=&message=Run%20on%20Replit&labelColor=0e1525&color=ffffff&logoColor=ffffff
[tweet-icon]: https://img.shields.io/twitter/url/http/shields.io.svg?style=social
[github-icon]: https://img.shields.io/github/stars/MarwanAlsoltany/blend.svg?style=social&label=Star

[php-href]: https://github.com/MarwanAlsoltany/blend/search?l=php
[version-href]: https://packagist.org/packages/marwanalsoltany/blend
[downloads-href]: https://packagist.org/packages/marwanalsoltany/blend/stats
[github-downloads-href]: https://github.com/MarwanAlsoltany/blend/releases
[license-href]: ./LICENSE
[maintenance-href]: https://github.com/MarwanAlsoltany/blend/graphs/commit-activity
[travis-href]: https://travis-ci.com/github/MarwanAlsoltany/blend
[codecov-href]: https://codecov.io/gh/MarwanAlsoltany/blend
[vscode-href]: https://open.vscode.dev/MarwanAlsoltany/blend
[replit-href]: https://replit.com/@marwanalsoltany/blend
[tweet-href]: https://twitter.com/intent/tweet?text=A%20versatile%20and%20lightweight%20PHP%20task%20runner%2C%20designed%20with%20simplicity%20in%20mind.%20&hashtags=%23PHP%20%23CLI
[github-href]: https://GitHub.com/MarwanAlsoltany/blend/stargazers
