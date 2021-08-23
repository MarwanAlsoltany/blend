<?php

return [

    // (string) Config format version.
    'version'      => '1',

    // (string|null) The autoload file to use.
    'autoload'     => null,

    // (array|null) Whether or not to merge the supplied executables/translations with the default ones.
    'strategy'     => [
        'mergeExecutables'  => true,
        'mergeTranslations' => true,
    ],

    // (array[]|null) The executables to load.
    'executables'  => [
        'php' => [
            './bin/*'
        ],
    ],

    // (array[]|null) The translations to apply.
    'translations' => [
        'abc' => 'xyz',
    ],

    // (bool|null) Whether or not to turn on ANSI colors for the output.
    'ansi'         => true,

    // (bool|null) Whether or not to turn on the output.
    'quiet'        => false,

    // (array[]|null) The tasks to add.
    'tasks'        => [

        // (array|null) Task definition (key = Task name, value = Task parameters).
        'some:task' => [

            // (string|null) If not specified, the key of the containing array will be used instead.
            'name'        => 'Some task',

            // (string|null) If not specified a fallback will be used instead.
            'description' => 'Some task',

            // (string) Valid values are 'shell', 'callback', or any available program (PHP for example).
            'executor'    => 'shell',

            // (string|callable) Depending on the executor, either a string containing command name or a path to a file, or a valid php callable.
            'executable'  => 'ls',

            // (string|array|null) Depending on the executor, either a string containing command options/arguments, or an array of arguments to pass to the callback.
            'arguments'   => '-lash',

            // (bool) Whether or not to hide the task from being listed.
            'hidden'      => false,

            // (bool) Whether or not to prevent the task from being ran.
            'disabled'    => false,

        ],

    ],

];
