<?php

return [

    // (string|null) The autoload file to use (useful when using PHP callable as task executable).
    'autoload' => null,

    // (bool|null) Whether or not to merge the supplied executables/translations with the default ones.
    'merge' => true,

    // (array[]|null) The executables to load as tasks ('key' = executor, 'value' = an array of executables).
    'executables' => [
        'php' => [
            './bin/*',
        ],
    ],

    // (string[]|null) The translations to apply to tasks names.
    // Starting from v1.1.0 the key can also be a pattern (regex).
    'translations' => [
        'abc' => 'xyz',
    ],

    // (bool|null) Whether or not to turn on ANSI colors for the output.
    'ansi' => true,

    // (bool|null) Whether or not to turn on the output.
    'quiet' => false,

    // (array[]|null) The tasks to add.
    'tasks' => [

        // (array|null) Task definition (key = task fallback name, value = task parameters).
        'some:task' => [

            // (string|null) If not specified, the key of the containing array will be used instead.
            'name' => 'some:task',

            // (string|null) If not specified a fallback will be used instead.
            'description' => 'Some task',

            // (string) Valid values are 'internal', 'shell', 'callback', or any available program executable (for example 'php' is used for PHP).
            'executor' => 'shell',

            // (string|callable) Depending on the executor, either a string containing a shell command, a path to an executable file, or a valid PHP callable.
            'executable' => 'ls',

            // (string|array|null) Depending on the executor, either a string containing shell command options/arguments, or an array of arguments to pass to the callback.
            'arguments' => '-lash',

            // (bool|null) Whether or not to hide the task from being listed.
            'hidden' => false,

            // (bool|null) Whether or not to prevent the task from being ran.
            'disabled' => false,

        ],

    ],
];
