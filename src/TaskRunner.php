<?php

/**
 * Blend - PHP Task Runner
 *
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Blend;

/**
 * @package MAKS\Blend
 * @link https://github.com/MarwanAlsoltany/blend Blend Repository
 *
 * @method void handleException(\Throwable $exception) Exception handler function.
 * @method void handleError(int $code, string $message, string $file, int $line) Error handler function.
 * @method mixed shutdown() Shutdown function. This method is abstract, implement it using `self::extend()`.
 */
class TaskRunner
{
    /**
     * Package name.
     *
     * @var string
     *
     * @since 1.0.10
     */
    public const NAME = 'Blend';

    /**
     * Package version.
     *
     * @var string
     */
    public const VERSION = 'v1.0.11';

    /**
     * Default executables.
     *
     * @var array
     */
    public const EXECUTABLES = [
        'php' => [
            './bin/*',
        ],
    ];

    /**
     * Default task name translations.
     *
     * @var array
     */
    public const TRANSLATIONS = [
        // file extensions with dots, come first to minimize unexpected name translations
        '/\.(php|phar|sh)/' => '',
        '/[-_\\\\\/\s]/' => ':',
        '@' => '', '#' => '', '$' => '', '&' => '', '?' => '', '!' => '',
        '(' => '', ')' => '', '{' => '', '}' => '', '[' => '', ']' => '',
        '+' => '', '*' => '', '=' => '', '^' => '', '~' => '', '%' => '',
        '.' => '', ',' => '', ';' => '', '`' => '', '"' => '', "'" => '',
    ];

    /**
     * Default config.
     *
     * @var array
     */
    public const CONFIG = [
        'autoload'     => null, // (string|null)
        'merge'        => null, // (bool|null)
        'executables'  => null, // (array[]|null)
        'translations' => null, // (string[]|null)
        'ansi'         => null, // (bool|null)
        'quiet'        => null, // (bool|null)
        'tasks'        => null, // (array[]|null)
    ];

    /**
     * Task success code.
     *
     * @var int
     *
     * @since 1.0.2
     */
    public const SUCCESS = 0;

    /**
     * Task failure code.
     *
     * @var int
     *
     * @since 1.0.2
     */
    public const FAILURE = 1;

    /**
     * Task type callback.
     *
     * @var string
     */
    public const CALLBACK_TASK = 'callback';

    /**
     * Task type shell.
     *
     * @var string
     */
    public const SHELL_TASK = 'shell';

    /**
     * Task type internal.
     *
     * @var string
     */
    protected const INTERNAL_TASK = 'internal';


    /**
     * A reference to the `$argc` global variable.
     *
     * @var int
     */
    public int $argc;

    /**
     * 	A reference to the `$argv` global variable.
     *
     * @var array
     */
    public array $argv;

    /**
     * An array of the arguments that could be passed to the executed task.
     *
     * @var array
     */
    public array $args;


    /**
     * Environment variable.
     *
     * @var string
     */
    private string $envVar;


    /**
     * Task runner path.
     *
     * @var string
     * @since 1.0.4
     */
    protected string $path;

    /**
     * Task runner ID.
     *
     * @var string
     */
    protected string $id;

    /**
     * Task runner name.
     *
     * @var string
     */
    protected string $name;

    /**
     * Task runner version.
     *
     * @var string
     */
    protected string $version;

    /**
     * The current task name passed to the task runner.
     *
     * @var string
     */
    protected string $task;

    /**
     * Task runner tasks.
     *
     * @var array
     */
    protected array $tasks;

    /**
     * Magic methods added via `self::extend()`.
     *
     * @var array
     */
    protected array $methods;

    /**
     * The results of commands executed via `self::exec()`.
     *
     * @var array
     *
     * @since 1.0.7
     */
    protected array $results;

    /**
     * The executables that will be loaded.
     *
     * @var array
     */
    protected array $executables;

    /**
     * The translations that will be applied to tasks names.
     *
     * @var array
     */
    protected array $translations;

    /**
     * The currently loaded configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * Whether or not to turn on ANSI colors for the output.
     *
     * @var bool
     */
    protected bool $ansi;

    /**
     * Whether or not to turn on the output.
     *
     * @var bool
     */
    protected bool $quiet;


    /**
     * Class constructor.
     * Use `self::extend()` to add a method with the name shutdown to be used as a shutdown function that is bound to the object.
     *
     * @param array[]|null $executables [optional] Executables to load. Example: `['php' => ['./bin', './scripts'], ...]`, paths can be glob patterns.
     * @param string[]|null $translations [optional] Task name translations `['search' => 'replacement', ...]`.
     * @param mixed[]|null $config [optional] The configuration to use. If a configuration file is found in CWD or a parent, this will be overridden.
     * @param bool $ansi [optional] Whether or not to turn on ANSI colors for the output.
     * @param bool $quiet [optional] Whether or not to turn on the output.
     */
    public function __construct(?array $executables = null, ?array $translations = null, ?array $config = null, bool $ansi = true, bool $quiet = false)
    {
        global $argv;
        global $argc;

        $this->argc         = &$argc;
        $this->argv         = &$argv;
        $this->args         = array_slice($argv, 2);

        $this->path         = realpath($this->argv[0]);
        $this->id           = basename($this->argv[0]);
        $this->name         = ucfirst($this->id);
        $this->envVar       = 'TR_' . strtr(strtoupper($this->id), ['.' => '_', '-' => '_']);
        $this->version      = static::VERSION;
        $this->task         = $this->argv[1] ?? '';
        $this->tasks        = [];
        $this->methods      = [];
        $this->results      = [];

        $this->executables  = $executables  ?? static::EXECUTABLES;
        $this->translations = $translations ?? static::TRANSLATIONS;
        $this->config       = $config       ?? static::CONFIG;
        $this->ansi         = $ansi;
        $this->quiet        = $quiet;

        $this->registerHandlers();
        $this->checkEnvironment();
        $this->checkConfiguration();
        $this->bootstrap();
    }

    public function __destruct()
    {
        $this->restoreHandlers();
    }

    public function __call(string $method, array $arguments)
    {
        if (isset($this->methods[$method])) {
            return $this->methods[$method](...$arguments);
        }

        $class = static::class;

        if ($method === 'shutdown') {
            return $class;
        }

        throw new \BadMethodCallException("Call to undefined method {$class}::{$method}()");
    }

    public function __toString()
    {
        return sprintf('%s [%s]', self::class, $this->path);
    }


    /**
     * Extends the class with a magic method using the passed callback.
     * The passed function will get converted to a closure and get bound to the object
     * with `object` visibility (can access private, protected, and public members).
     *
     * @param string $name Method name.
     * @param callable $callback The callback to use as method body.
     *
     * @return callable The created bound closure.
     */
    public function extend(string $name, callable $callback): callable
    {
        $method = \Closure::fromCallable($callback);
        $method = \Closure::bind($method, $this, $this);

        return $this->methods[$name] = $method;
    }

    /**
     * Registers error handler, exception handler, and shutdown function.
     *
     * @return void
     */
    private function registerHandlers(): void
    {
        $this->extend('handleError', function (int $code, string $message, string $file, int $line): void {
            throw new \ErrorException($message, 1, $code, $file, $line);
        });

        $this->extend('handleException', function (\Throwable $exception): void {
            $this->write(
                ['', '@(b,r)[{ ERROR }] %s @(y)[{[%s]}]', ''],
                [$exception->getMessage(), (new \ReflectionClass($exception))->getShortName()]
            );

            $this->terminate(static::FAILURE);
        });

        set_time_limit(0);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'shutdown']);
    }

    /**
     * Restores the error handler and the exception handler.
     *
     * @return void
     */
    private function restoreHandlers(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * Checks the environment for TR_* variable, validates its pattern and updates class internal state.
     *
     * @return void
     *
     * @throws \RuntimeException If TR_* variable pattern is invalid.
     */
    private function checkEnvironment(): void
    {
        $env = getenv($this->envVar);

        if (!$env) {
            return;
        }

        if (!preg_match('/(.+):((.+)(,*)\+?)/', $env)) {
            throw new \RuntimeException("The '{$this->envVar}' environment variable pattern is invalid, it does not match 'exe:dir,...+...'");
        }

        $executables = [];
        foreach (explode('+', $env) ?: [] as $exec) {
            $exec = explode(':', strtr($exec, [':\\' => '?\\']));
            $dirs = $exec[1] ?? '';
            $exec = $exec[0] ?? '';
            $dirs = explode(',', strtr($dirs, ['?\\' => ':\\']));

            $executables[$exec] = $dirs;
        }

        $this->executables = array_merge_recursive($this->executables, $executables);
    }

    /**
     * Checks the CWD or its parent(s) for a configuration file, validates its entries and updates class internal state.
     *
     * @return void
     *
     * @throws \RuntimeException If the configuration is invalid.
     */
    private function checkConfiguration(): void
    {
        $config    = $this->config;
        $rootRegex = '/(^\/$)|(^[A-Z]:(\\\|\/)$)/i';
        $cwd       = getcwd();

        while (!preg_match($rootRegex, $cwd)) {
            $php  = sprintf('%s/%s.config.%s', $cwd, $this->id, 'php');
            $json = sprintf('%s/%s.config.%s', $cwd, $this->id, 'json');

            if (file_exists($file = $php) || file_exists($file = $json)) {
                $type = strtoupper(pathinfo($file, PATHINFO_EXTENSION));

                $config = $type === 'JSON'
                    ? json_decode(file_get_contents($file), true)
                    : include($file);

                $config['__PATH__'] = realpath($file);

                break;
            }

            $cwd = dirname($cwd);
        }

        $invalid = array_diff_key(array_flip(array_keys(static::CONFIG)), $config);

        if ($invalid) {
            $missing = "['" . implode("', '", array_flip($invalid)) . "']";

            throw new \RuntimeException("Blend config file '{$config['__PATH__']}' is invalid, missing {$missing}");
        }

        $this->config = $config;
    }

    /**
     * Terminates the task runner by exiting the script.
     *
     * @param mixed $code The exit code.
     *
     * @return void This method exists the script.
     */
    protected function terminate($code = 0): void
    {
        exit($code); // @codeCoverageIgnore
    }

    /**
     * Bootstraps the task runner by adding predefined tasks.
     *
     * @return void
     */
    protected function bootstrap(): void
    {
        $config = (object)[
            'autoload'     => (string)($this->config['autoload'] ?? null),
            'merge'        => (bool)($this->config['merge'] ?? true),
            'executables'  => (array)($this->config['executables'] ?? null),
            'translations' => (array)($this->config['translations'] ?? null),
            'ansi'         => (bool)($this->config['ansi'] ?? $this->ansi),
            'quiet'        => (bool)($this->config['quiet'] ?? $this->quiet),
            'tasks'        => (array)($this->config['tasks'] ?? null),
        ];

        $this->executables  = array_merge_recursive($config->merge ? $this->executables : [], $config->executables);
        $this->executables  = array_map(fn ($executable) => array_filter(array_unique($executable)), $this->executables);
        // remove duplicated keys of the translations array if the arrays gonna merge, so that overrides get added to the end of the stack
        $this->translations = array_diff_key($this->translations, $config->merge ? $config->translations : []);
        $this->translations = array_merge($config->merge ? $this->translations : [], $config->translations);

        $this->ansi         = $config->ansi;
        $this->quiet        = $config->quiet;

        // all tasks get added after the config is fully loaded and merged
        $this->addTask('help', 'Displays help message', static::INTERNAL_TASK, sprintf('php %s help', $this->id));
        $this->addTask('list', 'Lists available tasks', static::INTERNAL_TASK, sprintf('php %s list', $this->id));
        $this->addTask('exec', 'Executes CLI commands', static::INTERNAL_TASK, sprintf('php %s exec', $this->id));

        if (!empty($config->autoload)) {
            require $config->autoload;
        }

        foreach ($config->tasks as $name => $task) {
            $task['name'] = $task['name'] ?? (string)$name;

            $this->makeTask($task);
        }

        $tasks = $this->load($this->executables) ?? [];

        foreach ($tasks as $task) {
            $this->addTask(
                $task['name'],
                $task['description'],
                $task['executor'],
                $task['executable'],
                $task['arguments']
            );
        }
    }

    /**
     * Loads tasks from the specified executables array.
     *
     * @param array[] $executables An array where the key is the executer program and the value is an array of glob patterns.
     *
     * @return array|null
     *
     * @throws \Exception If an executables directory is a file.
     */
    protected function load(array $executables): ?array
    {
        static $prefixes = [];

        $tasks = null;

        foreach ($executables as $executor => $directories) {
            foreach ($directories as $directory) {
                if (is_file($directory)) {
                    throw new \Exception("A directory path or a glob pattern was expected, got a path to a file '{$directory}'");
                }

                if (is_dir($directory)) {
                    $directory = rtrim($directory, '/') . '/*';
                }

                $prefix = isset($prefixes['']) ? dirname($directory) : '';
                $files  = glob($directory) ?: [];

                $prefixes[$prefix] = sprintf('[%s@%s]:%s', $executor, $directory, $prefix);

                foreach ($files as $executable) {
                    if (!is_file($executable)) {
                        continue;
                    }

                    $name = trim(sprintf('%s-%s', $prefix, basename($executable)), '-');

                    $tasks[$name] = [
                        'name'        => $name,
                        'description' => null,
                        'executor'    => $executor,
                        'executable'  => $executable,
                        'arguments'   => null,
                    ];
                }
            }
        }

        return $tasks;
    }

    /**
     * Translates the passed string using the specified translations.
     *
     * @param string $string The string to translate.
     * @param array|null $translations The translations to use. If null is passed, class translations will be used instead.
     *
     * @return string The translated string. Note that the returned string will always be trimmed from non-alphanumeric characters.
     */
    protected function translate(string $string, ?array $translations = null): string
    {
        $translations = $translations ?? $this->translations;
        $set          = md5(serialize($translations));

        static $patterns = [];

        if (!isset($patterns[$set])) {
            $patterns[$set] = [];

            set_error_handler(fn ($code) => $code, E_WARNING);

            foreach ($translations as $from => $to) {
                // if the key is a regex, keep it as is, otherwise make a regex out of it
                [$pattern, $replacement] = preg_match($from, '') !== false // if valid
                    ? [$from, $to]
                    : ['/' . preg_quote($from, '/') . '/', addcslashes($to, '$')];

                $patterns[$set][$pattern] = $replacement;
            }

            restore_error_handler();

            $patterns[$set]['/^[^[:alpha:]]+/'] = ''; // task name must start with a letter
            $patterns[$set]['/[^[:alnum:]]+$/'] = ''; // task name must end with a letter or number
        }

        return preg_replace(
            array_keys($patterns[$set]),
            array_values($patterns[$set]),
            strtolower(trim($string))
        );
    }

    /**
     * Formats a string like `*printf()` functions with the ability to add ANSI colors using the placeholder `@(foreground,background)[{text}]`.
     *
     * @param string $format A formatted string.
     * @param mixed ...$values Format values.
     *
     * @return string
     */
    protected function format(string $format, ...$values): string
    {
        static $colors = [
            'black'   => ['FG' => 30, 'BG' => 40],
            'red'     => ['FG' => 31, 'BG' => 41],
            'green'   => ['FG' => 32, 'BG' => 42],
            'yellow'  => ['FG' => 33, 'BG' => 43],
            'blue'    => ['FG' => 34, 'BG' => 44],
            'magenta' => ['FG' => 35, 'BG' => 45],
            'cyan'    => ['FG' => 36, 'BG' => 46],
            'white'   => ['FG' => 37, 'BG' => 47],
            'default' => ['FG' => 39, 'BG' => 49],
        ];

        $text     = vsprintf($format, $values);
        $regex    = '/@\((\w+),?(\w+)?\)\[\{(.*?)\}\]/s'; // @(white,black)[{text}] or @(w,b)[{txt}]
        $callback = function (array $matches) use (&$colors): string {
            $foreground = $matches[1];
            $background = $matches[2];
            $text       = $matches[3];

            if (!$this->ansi) {
                return $text;
            }

            foreach ([&$foreground, &$background] as &$color) {
                $color = strtolower($color);
                if (strlen($color) > 0 && strlen($color) <= 3) {
                    $names = preg_grep("/^{$color}/i", array_keys($colors));
                    $color = current($names);
                }
            }

            $fg = $colors[$foreground]['FG'] ?? $colors['default']['FG'];
            $bg = $colors[$background]['BG'] ?? $colors['default']['BG'];

            return "\e[{$fg};{$bg}m{$text}\e[0m";
        };

        $result = preg_replace_callback($regex, $callback, $text);

        if (!$this->ansi) {
            $result = preg_replace('/\\x1b[[][^A-Za-z]*[A-Za-z]/', '', $result);
        }

        return $result;
    }

    /**
     * Writes out a formatted text block from the specified lines and format value.
     * Available placeholders: `@name`, `@executor`, `@executable`, `@task`, `@runner`, and `@(foreground,background)[{text}]`
     *
     * @param string[] $lines An array of strings with format placeholders.
     * @param mixed[] $formatValues [optional] Format values.
     *
     * @return void
     */
    protected function write(array $lines, array $formatValues = []): void
    {
        if ($this->quiet) {
            return;
        }

        static $placeholders = [];

        if ($task = $this->getTask($this->task)) {
            $placeholders = [
                '@runner'     => $this,
                '@task'       => $task,
                '@name'       => $task->name,
                '@executor'   => $task->executor,
                '@executable' => $task->executable,
            ];
        }

        $text = str_ireplace(
            array_keys($placeholders),
            array_values($placeholders),
            implode(PHP_EOL, $lines)
        );

        echo $this->format($text, ...$formatValues), PHP_EOL;
    }

    /**
     * Prints out a help message listing all tasks of the task runner.
     *
     * @return void
     */
    protected function displayHelp(): void
    {
        $this->write(
            ['', "\e[4mBased on %s %s by \e[1mMarwan Al-Soltany\e[0m", ''],
            [static::NAME, static::VERSION]
        );

        $this->write(
            ['', 'Running as @(b,w)[{ %s }]', ''],
            [$this->getUser()]
        );

        $this->write(
            [
                '',
                '@(y)[{Usage:}]',
                '%3s php @(r)[{%s}] @(g)[{<task>}] [options] [--] [arguments]',
                '',
                '@(y)[{Examples:}]',
                '%3s php @(r)[{%s}] @(g)[{help}]',
                '%3s php @(r)[{%s}] @(g)[{some:task}] -o --opt -- arg',
                '',
                '@(y)[{Tasks:}]',
            ],
            ['', $this->id, '', $this->id, '', $this->id]
        );

        $this->listTasks($this->tasks);

        $this->write(
            [
                '',
                '@(y)[{Options/Arguments:}]',
                '%3s All will be passed to the executed task.',
                '%3s To preserve "double quotes", use \'single quotes\' around them.',
                '',
            ],
            ['', '']
        );

        $listExecutables = function ($array) {
            return implode(' + ', array_map(
                fn ($executables, $executor) => vsprintf('(%s):["%s"]', [
                    $executor,
                    implode('","', $executables)
                ]),
                array_values($array),
                array_keys($array)
            ));
        };

        $this->write(
            [
                '',
                'Use the environment variable @(c)[{%s}] to set one or more directories',
                'and/or glob patterns to load executables from (default: @(c)[{%s}]).',
                '@(c)[{%s}] pattern: @(m)[{exe}]@(y)[{:}]@(c)[{dir}]@(y)[{,}]@(c)[{...}]@(y)[{+}]...',
                '@(c)[{%s}] example: @(m)[{php}]@(y)[{:}]@(c)[{bin}]@(y)[{,}]@(c)[{cmd}]@(y)[{+}]@(m)[{node}]@(y)[{:}]@(c)[{bin}]@(y)[{+}]...',
                '',
                'If more than one pattern/directory is specified, the tasks will be',
                'prefixed with the containing directory name to minimize collisions.',
                'To prevent the prefixing behaviour, provide a translation therefor.',
                '',
                'Currently loading: @(c)[{%s}]',
                '',
                '',
                '@(b,c)[{ CONFIG }] @(r)[{%s}]',
                '',
            ],
            [
                $this->envVar,
                $listExecutables(static::EXECUTABLES),
                $this->envVar,
                $this->envVar,
                $listExecutables($this->executables) ?: 'NONE',
                $this->config['__PATH__'] ?? 'N/A'
            ]
        );

        $this->terminate(static::SUCCESS);
    }

    /**
     * Prints out a hint message listing tasks matching the current task of the task runner.
     *
     * @return void
     */
    protected function displayHint(): void
    {
        $this->write(
            ['', 'The task with the name @(b,r)[{ %s }] was not found!'],
            [$this->task]
        );

        $pattern = strlen($this->task) > 1
            ? sprintf('/(%s)/i', implode(')|(', array_filter(explode('\:', preg_quote($this->task, '/')))))
            : sprintf('/^%s+/i', preg_quote($this->task, '/'));

        $matches = preg_grep($pattern, array_keys($this->tasks)) ?: [];

        $matchedTasks  = array_map(fn ($match) => $this->tasks[$match], $matches);
        $disabledTasks = array_filter($this->tasks, fn ($task) => in_array($task, $matchedTasks) && $task->disabled);
        $enabledTasks  = array_filter($this->tasks, fn ($task) => in_array($task, $matchedTasks) && !$task->disabled);

        if (!count($matchedTasks)) {
            $this->write(['']);

            $this->terminate(static::FAILURE);

            return;
        }

        if ($count = count($disabledTasks)) {
            $this->write(['', '@(r)[{Found %d disabled matching task(s).}]'], [$count]);
        }

        if ($count = count($enabledTasks)) {
            $this->write(['', '@(y)[{Found %d possible matching task(s):}]'], [$count]);
        }

        $this->listTasks($enabledTasks);

        $altTasks = [];
        foreach ($matchedTasks as $task) {
            if ($task->disabled || $task->hidden) {
                continue;
            }

            $altTasks[] = $this->format('    @(blue)[{->}] php @(r)[{%s}] @(g)[{%s}]', $this->id, $task->name);
        }

        if (!empty($altTasks)) {
            $this->write(
                ['', '@(blue)[{Run the following instead:}]', '%s'],
                [implode(PHP_EOL, $altTasks)]
            );
        }

        $this->write(['']);

        $this->terminate(static::FAILURE);
    }

    /**
     * Prints out a list of all available tasks of the task runner.
     *
     * @return void
     */
    protected function displayList(): void
    {
        $this->write(['', '@(y)[{Available Tasks:}]']);

        $this->listTasks($this->tasks);

        $this->write(['']);

        $this->terminate(static::SUCCESS);
    }

    /**
     * Prints out the result of executing the current argument of the task runner.
     *
     * @return void
     */
    protected function displayExec(): void
    {
        $command = implode(' ', $this->args);

        $this->write(
            ['', '[@(c)[{%s}]] @(b,y)[{ EXECUTING }] %s'],
            [date('H:i:s'), $command]
        );

        $time = microtime(true);
        $code = $this->exec($command);
        $time = (microtime(true) - $time) * 1000;

        $this->write(
            ['[@(c)[{%s}]] @(b,%s)[{ %s }] @(m)[{%.2fms}]', ''],
            [date('H:i:s'), $code > static::SUCCESS ? 'r' : 'g', 'DONE', $time]
        );

        $this->terminate($code);
    }

    /**
     * Prints out a list of the passed tasks.
     *
     * @param object[] $tasks The tasks to list
     *
     * @return void
     */
    protected function listTasks(array $tasks): void
    {
        $cw = max(array_map('strlen', array_keys($tasks)) ?: [13]) + 3; // column width

        foreach ($tasks as $task) {
            if ($task->hidden) {
                continue;
            }

            if ($task->disabled) {
                $this->write(
                    ["%3s @(r)[{%-{$cw}.{$cw}s}] @(blue)[{->}] @(r)[{(disabled)}] %s @(m)[{[%s]}]"],
                    ['', '*****', '*******', '***']
                );
                continue;
            }

            $this->write(
                ["%3s @(g)[{%-{$cw}.{$cw}s}] @(blue)[{->}] %s @(m)[{[%s]}]"],
                ['', $task->name, $task->description ?? $task->executable, $task->executor]
            );
        }
    }

    /**
     * Executes a shell command using `passthru()`.
     * This is useful for interactive commands and/or long running processes with output.
     *
     * @param string|string[] $cmd A string or an array of commands to execute.
     * @param bool $escape [optional] Whether to escape shell meta-characters (like: &#;`|*?~<>^()[]{}$) in the command(s) or not.
     *
     * @return int The status code of the executed command.
     * Note that if multiple commands are passed only the code of the last one will be returned.
     * Use `self::getExecResult()` to get all info about the executed command.
     *
     * @throws \InvalidArgumentException If the command is an empty string.
     *
     * @since 1.0.11
     */
    public function passthru($cmd, bool $escape = true): int
    {
        $commands = (array)$cmd;
        $windows  = PHP_OS === 'WINNT';

        $code = null;

        foreach ($commands as $index => $command) {
            $command = $escape ? escapeshellcmd(trim($command)) : trim($command);
            $command = $windows ? preg_replace('`(?<!^) `', '^ ', $command) : $command; // escape spaces on windows

            if (!strlen($command)) {
                throw new \InvalidArgumentException('No valid command is specified');
            }

            fwrite(STDOUT, PHP_EOL);
            passthru($command, $code);
            fwrite($code ? STDERR : STDOUT, PHP_EOL);

            $cmd = $commands[$index];
            $cid = md5(trim($cmd));

            $this->results[$cid] = [
                'command' => $cmd,
                'pid'     => null,
                'output'  => null,
                'code'    => (int)$code,
            ];
        }

        return (int)$code;
    }

    /**
     * Executes a shell command synchronously or asynchronous and prints out its result if possible.
     *
     * @param string|string[] $cmd A string or an array of commands to execute.
     * @param bool $async [optional] Whether the command(s) should be a background process (asynchronous) or not (synchronous).
     * @param bool $escape [optional] Whether to escape shell meta-characters (like: &#;`|*?~<>^()[]{}$) in the command(s) or not. This parameter is ignored if `$async` is set to `true`.
     *
     * @return int The status code of the executed command (or PID if asynchronous).
     * Note that if multiple commands are passed only the code/PID of the last one will be returned.
     * Use `self::getExecResult()` to get all info about the executed command.
     *
     * @throws \InvalidArgumentException If the command is an empty string.
     */
    public function exec($cmd, bool $async = false, bool $escape = true): int
    {
        $commands = (array)$cmd;
        $windows  = PHP_OS === 'WINNT';

        $code = null;
        $pid  = null;

        foreach ($commands as $index => $command) {
            $command = $async || $escape ? escapeshellcmd(trim($command)) : trim($command);
            $command = $windows ? preg_replace('`(?<!^) `', '^ ', $command) : $command; // escape spaces on windows
            $wrapper = $async
                ? ($windows ? 'start /B %s > NUL' : '/usr/bin/nohup %s > /dev/null 2>&1 & echo $!;')
                : ($escape ? '%s 2>&1' : '%s');

            if (!strlen($command)) {
                throw new \InvalidArgumentException('No valid command is specified');
            }

            $command = sprintf($wrapper, $command);

            if ($async) {
                if ($windows) {
                    $descSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                    $process  = proc_open($command, $descSpec, $pipes);
                    $status   = proc_get_status($process);
                    $parentId = $status['pid'] ?? getmypid();
                    $code     = proc_close($process);

                    $ids = `wmic process get ParentProcessId,ProcessId | findStr {$parentId}`;
                    $ids = explode(' ', trim($ids ?? ' '));
                    $pid = end($ids);
                } else {
                    $pid = exec($command, $output, $code);
                }
            } else {
                exec($command, $output, $code);
            }

            $cmd = $commands[$index];
            $cid = md5(trim($cmd));

            $this->results[$cid] = [
                'command' => $cmd,
                'pid'     => $async ? (int)$pid : null,
                'output'  => $async ? null : implode(PHP_EOL, $output),
                'code'    => (int)$code,
            ];

            if (!$async) foreach ($output as $line) {
                $this->write(['%3s @(%s)[{>}] %s'], ['', $code > static::SUCCESS ? 'r' : 'g', $line]);
            }
        }

        return $async ? (int)$pid : (int)$code;
    }

    /**
     * Returns the result of a command executed via `self::exec()`.
     *
     * @param string|null $cmd [optional] The command to get the result of (as was passed to `self::exec()`), or the result of the last command if no parameter is specified.
     *
     * @return array|null The result of the command or null if the given command was not executed.
     * The return value is cached and will be returned from cache if the command was executed before.
     * The return value is an array with the following keys:
     * - `command`: (string) The command that was executed.
     * - `pid`: (int|null) The PID of the executed command or null if the command was executed synchronously. On Windows this will always be `0` if the process is not a long running process.
     * - `output`: (string|null) The raw output of the executed command or null if the command was executed asynchronously.
     * - `code`: (int) The status code of the executed command.
     *
     * @since 1.0.7
     */
    public function getExecResult(?string $cmd = null): ?array
    {
        if ($cmd === null) {
            return end($this->results) ?: null;
        }

        return $this->results[md5(trim($cmd))] ?? null;
    }

    /**
     * Adds a task that can be executed by the used shell (Bash for example).
     *
     * @param string $name Task name.
     * @param string|null $description [optional] Task description.
     * @param string $command Shell command.
     * @param string|null $arguments Shell arguments and/or options.
     *
     * @return $this
     */
    public function addShellTask(string $name, ?string $description, string $command, ?string $arguments = null)
    {
        return $this->addTask($name, $description, static::SHELL_TASK, $command, $arguments);
    }

    /**
     * Adds a task that executes the passed callback.
     *
     * @param string $name Task name.
     * @param string|null $description [optional] Task description.
     * @param callable $callback The callback that should be executed (it will be bound to the task runner object, if it can't be bound, the first parameter passed to it will be the task runner object).
     * @param array|null $arguments The arguments that should be passed to the callback.
     *
     * @return $this
     */
    public function addCallbackTask(string $name, ?string $description, callable $callback, ?array $arguments = null)
    {
        $callback = new class($callback) {
            public string $id;
            public \Closure $callback;
            public function __construct(callable $callback)
            {
                $this->callback = \Closure::fromCallable($callback);
                $this->id       = 'callback-' . md5(spl_object_hash($this->callback));
            }
            public function __invoke(...$arguments)
            {
                return ($this->callback)(...$arguments);
            }
            public function __call(string $name, array $arguments)
            {
                return $this->callback->{$name}(...$arguments);
            }
            public function __toString()
            {
                return $this->id;
            }
        };

        return $this->addTask($name, $description, static::CALLBACK_TASK, $callback, $arguments);
    }

    /**
     * Adds a new task.
     *
     * @param string $name Task name.
     * @param string|null $description [optional] Task description.
     * @param string $executor Task executor.
     * @param mixed $executable Task executable.
     * @param mixed $arguments [optional] Task arguments.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If task name is an empty string.
     */
    public function addTask(string $name, ?string $description, string $executor, $executable, $arguments = null)
    {
        $name = $this->translate($name);

        if (!strlen($name)) {
            throw new \InvalidArgumentException('Task name cannot be an empty string');
        }

        $properties = [
            'name'        => $name,
            'description' => $description,
            'executor'    => $executor,
            'executable'  => $executable,
            'arguments'   => $arguments,
            'disabled'    => false,
            'hidden'      => false,
        ];

        $task = new class($properties) extends \stdClass {
            public function __construct(array $properties)
            {
                foreach ($properties as $name => $value) {
                    $this->{$name} = $value;
                }
            }
            public function __call(string $method, array $arguments)
            {
                $class = static::class;

                try {
                    if (preg_match('/^([gs]et)([a-z0-9_]+)$/i', $method, $matches)) {
                        $function = strtoupper($matches[1]);
                        $property = strtolower($matches[2]);
                        $value    = $arguments[0] ?? null;

                        if (!property_exists($this, $property)) {
                            throw new \Exception("Call to undefined property {$class}::${$property}");
                        }

                        if ($function === 'SET') {
                            $this->{$property} = $value ?? null;
                            return $this;
                        }

                        return $this->{$property};
                    }
                } catch (\Exception $exception) {
                    throw new \BadMethodCallException("Call to undefined method {$class}::{$method}()", 0, $exception);
                }
            }
            public function __toString()
            {
                return sprintf('%s -> %s [%s]', $this->name, $this->executable, $this->executor);
            }
        };

        $this->tasks[$name] = $task;

        return $this;
    }

    /**
     * Makes a task from array representation of a task object and adds it to the available tasks.
     *
     * @param array $task An associative array that represents a task object with the following keys:
     * `name`, `description`, `executor`, `executable`, `arguments`, `hidden`, `disabled`.
     * Note: the same rules of tasks supplied by config file applies to this array.
     *
     * @return $this
     *
     * @since 1.0.6
     */
    public function makeTask(array $task)
    {
        $task = (object)[
            'name'        => $task['name']        ?? 'task-' . md5(uniqid('', true)),
            'description' => $task['description'] ?? null,
            'executor'    => $task['executor']    ?? '',
            'executable'  => $task['executable']  ?? '',
            'arguments'   => $task['arguments']   ?? null,
            'hidden'      => $task['hidden']      ?? false,
            'disabled'    => $task['disabled']    ?? false,
        ];

        $func = $task->executor == static::SHELL_TASK || $task->executor == static::CALLBACK_TASK
            ? 'add' . ucfirst(strtolower($task->executor)) . 'Task'
            : 'addTask';

        $args = $func === 'addTask'
            ? [$task->name, $task->description, $task->executor, $task->executable, $task->arguments]
            : [$task->name, $task->description, $task->executable, $task->arguments];

        $this
            ->{$func}(...$args)
            ->getTask($task->name)
            ->setHidden($task->hidden)
            ->setDisabled($task->disabled);
    }

    /**
     * Removes a task from the available tasks.
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeTask(string $name)
    {
        $name = $this->translate($name);

        if (isset($this->tasks[$name])) {
            unset($this->tasks[$name]);
        }

        return $this;
    }

    /**
     * Disables a task by preventing it from being ran. The task will still get listed, but will be obfuscated.
     * Note that internal tasks cannot be disabled.
     *
     * @param string $name
     *
     * @return $this
     */
    public function disableTask(string $name)
    {
        if ($task = $this->getTask($name)) {
            $task->disabled = $task->executor === static::INTERNAL_TASK
                ? $task->disabled
                : true;
        }

        return $this;
    }

    /**
     * Hides a task by preventing it from being listed. The task can still get ran though.
     * Note that internal tasks cannot be hidden.
     *
     * @param string $name
     *
     * @return $this
     */
    public function hideTask(string $name)
    {
        if ($task = $this->getTask($name)) {
            $task->hidden = $task->executor === static::INTERNAL_TASK
                ? $task->hidden
                : true;
        }

        return $this;
    }

    /**
     * Returns a task.
     *
     * @param string $name Task name.
     *
     * @return object|null If found, the returned object has getters and setters for the following properties:
     * `name`, `description`, `executor`, `executable`, `arguments`, `hidden`, `disabled`
     */
    public function getTask(string $name): ?object
    {
        $name = $this->translate($name);

        return $this->tasks[$name] ?? null;
    }

    /**
     * Returns all tasks.
     *
     * @return object[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Runs a task.
     *
     * @param string $name Task name.
     *
     * @return int|mixed Exit code or the return value if the task is a callback task.
     *
     * @throws \Exception If the task is not found or disabled.
     */
    public function runTask(string $name)
    {
        $task = $this->getTask($name);

        if (!$task) {
            throw new \Exception("The task with the name '{$name}' was not found");
        }

        if ($task->disabled) {
            throw new \Exception("The task with the name '{$name}' is disabled");
        }

        $time   = microtime(true);
        $result = null;

        $executor   = $task->executor   ?? '';
        $executable = $task->executable ?? '';
        $arguments  = $task->arguments  ?? '';

        $this->write(
            ['', '[@(c)[{%s}]] @(b,y)[{ RUNNING }] @(g)[{%s}]'],
            [date('H:i:s'), $task->name ?? 'N/A']
        );

        if ($executor === static::INTERNAL_TASK || $executor === static::SHELL_TASK) {
            $executor = '';
        }

        if ($executor === static::CALLBACK_TASK) {
            $executor  = '';
            $arguments = array_values((array)$arguments);
            try {
                $result = $executable->bindTo($this)(...$arguments) ?? static::SUCCESS;
            } catch (\Exception $e) {
                $result = $executable($this, ...$arguments) ?? static::SUCCESS;
            }
        }

        if ($result === null) {
            $arguments = trim(sprintf('%s %s', $arguments, implode(' ', $this->args)));
            $command   = trim(sprintf('%s %s %s', $executor, $executable, $arguments));
            $result    = $this->exec($command);
        }

        $time = (microtime(true) - $time) * 1000;

        $this->write(
            ['[@(c)[{%s}]] @(b,%s)[{ %s }] @(m)[{%.2fms}]', ''],
            [date('H:i:s'), $result > static::SUCCESS ? 'r' : 'g', 'DONE', $time]
        );

        return $result;
    }

    /**
     * Runs a task or starts the runner if no parameter is specified or the task is not found.
     *
     * @param string|null $task Task name.
     *
     * @return $this
     */
    public function run(?string $task = null)
    {
        $this->write(
            ['', '@(r)[{%s}] Task Runner %s'],
            [$this->getName(), $this->getVersion()]
        );

        $chainable = $task !== null;
        $task      = $this->task = (string)($task ?? $this->task);
        $available = $this->getTask($task);
        $code      = static::SUCCESS;

        switch (true) {
            case ($task === 'help' || $task === ''):
                $this->displayHelp();
                break;
            case ($task === 'list'):
                $this->displayList();
                break;
            case ($task === 'exec'):
                $this->displayExec();
                break;
            case ($available === null || ($available && $available->disabled)):
                $this->displayHint();
                break;
        }

        if ($available) {
            $code = $this->runTask($task);
        }

        return $chainable ? $this : $this->terminate($code);
    }

    /**
     * Writes a message out to the console.
     * Use the `@name`, `@executor`, `@executable`, `@task` and/or `@runner` placeholders to get
     * their corresponding values, these placeholder are only available if a task is being ran.
     * Use the `@(foreground,background)[{text}]` placeholder to make colorful message segments.
     * Available colors are: `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan`, `white`, and `default`.
     * Color name can be the initial letter only. Incase of collisions, add letters until it's unique.
     *
     * @param string|string[] $message The message to say. When an array is passed, each element will be a new line.
     *
     * @return $this
     */
    public function say($message)
    {
        $text = array_map(fn ($line) => sprintf('%3s @(m)[{>}] %s', '', $line), (array)$message);

        $this->write($text);

        return $this;
    }

    /**
     * Sorts the tasks alphabetically.
     *
     * @return $this
     */
    public function sort()
    {
        ksort($this->tasks, SORT_STRING);

        return $this;
    }

    /**
     * Starts the task runner.
     *
     * @return void
     */
    public function start(): void
    {
        $this->run(null);
    }

    /**
     * Returns the task runner user.
     *
     * @return string
     */
    protected function getUser(): string
    {
        return sprintf('%s@%s', get_current_user(), gethostname());
    }

    /**
     * Returns the task runner name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the task runner name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = trim($name);

        return $this;
    }

    /**
     * Returns the task runner version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the task runner version.
     *
     * @param string $version
     *
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = trim($version);

        return $this;
    }

    /**
     * Returns whether the task runner output is currently using ANSI colors or not.
     *
     * @return bool
     */
    public function isAnsi(): bool
    {
        return $this->ansi;
    }

    /**
     * Sets the task runner ANSI output value.
     *
     * @param bool $ansi
     *
     * @return $this
     */
    public function setAnsi(bool $ansi)
    {
        $this->ansi = $ansi;

        return $this;
    }

    /**
     * Returns whether the task runner output is currently quiet or not.
     *
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->quiet;
    }

    /**
     * Sets the task runner quiet output value.
     *
     * @param bool $quiet
     *
     * @return $this
     */
    public function setQuiet(bool $quiet)
    {
        $this->quiet = $quiet;

        return $this;
    }
}
