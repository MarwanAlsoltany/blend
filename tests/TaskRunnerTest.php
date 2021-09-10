<?php

declare(strict_types=1);

namespace MAKS\Blend\Tests;

use MAKS\Blend\Tests\TestCase;
use MAKS\Blend\Tests\Mocks\TaskRunnerMock;
use MAKS\Blend\TaskRunner;

class TaskRunnerTest extends TestCase
{
    private TaskRunner $runner;


    public function setUp(): void
    {
        parent::setUp();

        $this->runner = new TaskRunnerMock(null, null, null, false, false);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->runner->__destruct();
        unset($this->runner);
    }


    public function testCastingTheRunnerToString()
    {
        $this->expectOutputRegex('/(' . preg_quote(TaskRunner::class) . ')/');

        echo $this->runner;
    }

    public function testExtendMethodCanBeUsedToExtendTheClassInstanceWithNewMethods()
    {
        $testCase = $this;

        $returnParam = $this->runner->extend('returnParam', fn ($param) => $param);
        $param       = $this->runner->returnParam('param');

        $this->assertIsCallable($returnParam);
        $this->assertEquals($param, 'param');

        $returnThis = $this->runner->extend('returnThis', function () use ($testCase) {
            $testCase->assertInstanceOf(TaskRunner::class, $this);
            return $this;
        });

        $this->assertEquals($returnThis(), $this->runner);

        $this->assertInstanceOf($this->runner->shutdown(), $this->runner);

        $this->expectException(\BadMethodCallException::class);

        $this->runner->unknown();
    }

    public function testRunnerExceptionAndErrorHandlers()
    {
        $this->expectOutputRegex('/(Test Exception)/');
        $this->expectOutputRegex('/(\[Exception\])/');

        $this->runner->handleException(new \Exception('Test Exception'));

        $this->expectException(\ErrorException::class);

        $this->runner->handleError(1, 'Test Error', __FILE__, __LINE__);
    }

    public function testTheRunnerUsesThePassedConfiguration()
    {
        $runner = new TaskRunnerMock(null, null, [
            'autoload'     => dirname(__DIR__) . '/vendor/autoload.php',
            'merge'        => true,
            'executables'  => [
                'php' => [
                    './bin',
                ],
            ],
            'translations' => [
                'abc' => 'xyz',
            ],
            'ansi'         => false,
            'quiet'        => false,
            'tasks'        => [
                [
                    'name' => 'test:abc',
                    'description' => null,
                    'executor' => '',
                    'executable' => '',
                    'arguments' => '',
                    'hidden' => false,
                    'disabled' => false,
                ],
            ],
        ], false, false);

        $task = $runner->getTask('test:xyz');

        $this->assertFalse($runner->isAnsi());
        $this->assertFalse($runner->isQuiet());

        $this->assertNotEmpty($task->getName());
        $this->assertEmpty($task->getDescription());
    }

    public function testTheRunnerUsesUserProvidedConfigurationAndThrowsAnExceptionIfThePassedConfigurationIsInvalid()
    {
        $file = sprintf('%s/phpunit.config.json', getcwd());

        try {
            file_exists($file) && unlink($file);
            file_put_contents($file, json_encode(['autoload' => dirname(__DIR__) . '/vendor/autoload.php']));

            $runner = new TaskRunnerMock(null, null, null, false, false);
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\RuntimeException::class, $exception);
            $this->assertMatchesRegularExpression(
                "/(missing \['merge', 'executables', 'translations', 'ansi', 'quiet', 'tasks'\])/",
                $exception->getMessage()
            );

            unlink($file);

            return;
        }

        $this->fail('Expected Exception is not thrown');
    }

    public function testLoadMethodThrowsAnExceptionIfAnExecutableDirectoryIsAFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(A directory path or a glob pattern was expected, got a path to a file)/');

        $runner = new TaskRunnerMock(['php' => [__FILE__]]);
    }

    public function testExecMethodExecutesACommandAndPrintsItsOutput()
    {
        $this->expectOutputRegex('/(123)/');

        $code = $this->runner->exec('echo 123');

        $this->assertIsInt($code);
        $this->assertEquals(0, $code);
    }

    public function testExecMethodExecutesACommandAsynchronouslyAndReturnsItsPid()
    {
        // PHP Server, a long running process
        $cmd = sprintf('php -S localhost:8000 -t %s', __DIR__);
        $pid = $this->runner->exec($cmd, true);

        $this->assertIsInt($pid);

        // Process killing command
        $cmd = sprintf(PHP_OS === 'WINNT' ? 'tskill %d' : 'kill -15 %d', $pid);
        $code = $this->runner->exec($cmd);

        $this->assertIsInt($code);
        $this->assertEquals(0, $code);
    }

    public function testExecMethodFailsIfTheCommandIsInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->runner->exec(['', '']);
    }

    public function testAddTaskMethodAddsATask()
    {
        $this->runner->addTask('test:task', 'Test task', TaskRunner::SHELL_TASK, 'php', '-v');

        $task = $this->runner->getTask('test:task');

        $this->assertInstanceOf(\stdClass::class, $task);
    }

    public function testAddTaskMethodReturnedStdClass()
    {
        $this->runner->addTask('test:task', 'Test task', 'php', __FILE__, '-v');
        $task = $this->runner->getTask('test:task');

        $this->assertEquals('test:task', $task->name);
        $this->assertEquals('Test task', $task->description);
        $this->assertEquals('php', $task->executor);
        $this->assertEquals(__FILE__, $task->executable);

        $this->assertIsString((string)$task);

        $task->setName('changed:test:task');

        $this->assertEquals('changed:test:task', $task->getName());

        try {
            $task->getUnknown();
        } catch (\Exception $exception) {
            $this->assertInstanceOf(\BadMethodCallException::class, $exception);
            return;
        }

        $this->fail('Expected Exception is not thrown');
    }

    public function testAddTaskMethodThrowsAnExceptionForInvalidTaskName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->runner->addTask('', null, '', '', null);
    }

    public function testAddShellTaskMethod()
    {
        $this->runner->addShellTask('test:task', 'Test task', 'ls', '-lash');

        $task = $this->runner->getTask('test:task');

        $this->assertInstanceOf(\stdClass::class, $task);
        $this->assertEquals(TaskRunner::SHELL_TASK, $task->executor);
        $this->assertIsString($task->executable);
    }

    public function testAddShellTaskMethodReturnedStdClass()
    {
        $this->runner->addShellTask('test:task', 'Test task', 'ls', '-lash');

        $task = $this->runner->getTask('test:task');

        $this->assertIsString((string)$task->executable);
        $this->assertIsString((string)$task);
    }

    public function testAddCallbackTaskMethod()
    {
        $this->runner->addCallbackTask('test:task', 'Test task', function ($testCase) {
            /** @var TaskRunner $this */
            /** @var TaskRunnerTest $testCase */
            $this->say('Test task');
            $testCase->assertInstanceOf(TaskRunner::class, $this);
        }, [$this]);

        $task = $this->runner->getTask('test:task');

        $this->assertInstanceOf(\stdClass::class, $task);
        $this->assertEquals(TaskRunner::CALLBACK_TASK, $task->executor);
        $this->assertIsObject($task->executable);
    }

    public function testAddCallbackTaskMethodReturnedStdClass()
    {
        $this->runner->addCallbackTask('test:task', 'Test task', function ($testCase) {
            /** @var TaskRunner $this */
            /** @var TaskRunnerTest $testCase */
            $this->say('Test task');
            $testCase->assertInstanceOf(TaskRunner::class, $this);
        }, [$this]);

        $task = $this->runner->getTask('test:task');

        $this->assertIsString((string)$task);
        $this->assertIsString((string)$task->executable);
    }

    public function testMakeTaskMethodCreatesATask()
    {
        $this->runner->makeTask([
            'name' => 'test:task:1',
            'description' => 'Test task',
            'executor' => 'shell',
            'executable' => 'php',
            'arguments' => '-v',
            'hidden' => true,
            'disabled' => true,
        ]);

        $this->runner->makeTask([
            'name' => 'test:task:2',
            'executor' => 'callback',
            'executable' => function () {
                /** @var TaskRunner $this */
                $this->say('Test!');
            },
        ]);

        $this->assertIsObject($this->runner->getTask('test:task:1'));
        $this->assertIsObject($this->runner->getTask('test:task:2'));
    }

    public function testRemoveTaskMethod()
    {
        $this->runner->addTask('test:task', 'Test task', 'php', '', '-v');

        $tasks = $this->runner->getTasks();

        $this->assertIsArray($tasks);
        $this->assertArrayHasKey('test:task', $tasks);
        $this->assertInstanceOf(\stdClass::class, $tasks['test:task']);

        $this->runner->removeTask('test:task');

        $tasks = $this->runner->getTasks();

        $this->assertArrayNotHasKey('test:task', $tasks);
    }

    public function testHideTaskMethod()
    {
        $this->runner->addTask('test:task', 'Test task', 'php', '', '-v');
        $this->runner->hideTask('test:task');
        $this->runner->hideTask('help');

        $tasks = $this->runner->getTasks();

        $this->assertTrue($tasks['test:task']->hidden);
        $this->assertFalse($tasks['help']->hidden);
    }

    public function testDisableTaskMethod()
    {
        $this->runner->addTask('test:task', 'Test task', 'php', '', '-v');
        $this->runner->disableTask('test:task');
        $this->runner->disableTask('help');

        $tasks = $this->runner->getTasks();

        $this->assertTrue($tasks['test:task']->disabled);
        $this->assertFalse($tasks['help']->disabled);
    }

    public function testRunTaskMethod()
    {
        $this->runner->addTask('test:task', 'Test task', 'php', '', '-v');

        $this->expectOutputRegex('/(PHP \d+\.\d+\.\d+ \(cli\))/');

        $this->runner->runTask('test:task');
    }

    public function testRunTaskMethodMethodWithTaskTypeShell()
    {
        $this->runner->addShellTask('echo', null, 'echo', 'Test');

        $this->expectOutputRegex('/(Test)/');

        $this->runner->runTask('echo');
    }

    public function testRunTaskMethodMethodWithTaskTypeCallback()
    {
        $this->runner->addCallbackTask('test:task', null, static function ($runner) {
            $runner->say('Test');
        }, null);

        $this->expectOutputRegex('/(Test)/');

        $this->runner->runTask('test:task');
    }

    public function testRunTaskMethodThrowsAnExceptionForInvalidTaskName()
    {
        $this->expectExceptionMessageMatches('/(The task with the name \'\' was not found)/');

        $this->runner->runTask('');
    }

    public function testRunTaskMethodThrowsAnExceptionForDisabledTasks()
    {

        $this->runner->addShellTask('echo', null, 'echo Test', null);
        $this->runner->disableTask('echo');
        $this->runner->hideTask('echo');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(The task with the name \'echo\' is disabled)/');

        $this->runner->runTask('echo');
    }

    public function testRunMethod()
    {
        $this->runner->setName('PHPUnit');
        $this->runner->setVersion('vX.X.X');
        $this->runner->addShellTask('test:task:1', null, 'Test task', null);
        $this->runner->addShellTask('test:task:2', null, 'Test task', null)->hideTask('test:task:2');
        $this->runner->addShellTask('test:task:3', null, 'Test task', null)->disableTask('test:task:3');

        $this->expectOutputRegex('/(PHPUnit Task Runner vX.X.X)/');

        $this->runner->run(null);

        $this->expectOutputRegex('/(PHPUnit Task Runner vX.X.X)/');

        $this->runner->run('help');

        $this->expectOutputRegex('/(PHPUnit Available Tasks)/');

        $this->runner->run('list');

        $this->expectOutputRegex('/(1234)/');

        $this->runner->args[] = 'echo';
        $this->runner->args[] = '1234';
        $this->runner->run('exec');

        $this->expectOutputRegex('/(The task with the name  test:task  was not found)/');

        $this->runner->run('test:task');

        $this->expectOutputString('');

        $this->runner->setQuiet(!$this->runner->isQuiet())->run('unknown');
    }

    public function testSortMethodSortsTheTasks()
    {
        $this->runner->addShellTask('z', null, 'echo', '"Test task"');
        $this->runner->addShellTask('m', null, 'echo', '"Test task"');
        $this->runner->addShellTask('a', null, 'echo', '"Test task"');

        $tasks       = $this->runner->getTasks();
        $tasksSorted = $this->runner->sort()->getTasks();

        $this->assertNotEquals(array_keys($tasks), array_keys($tasksSorted));
    }

    public function testSayMethodOutputsToTheConsole()
    {
        $this->expectOutputRegex('/(Test message)/');

        $this->runner->say('Test message');

        $this->expectOutputRegex('/(Test message)/');

        $this->runner->setAnsi(!$this->runner->isAnsi())->say('@(y)[{Test}] message');

        $this->expectOutputRegex("/(\e\[33;49mTest\e\[0m message)/");

        $this->runner->setAnsi(!$this->runner->isAnsi())->say('@(y)[{Test}] message');
    }

    public function testStartMethodWithEnvironmentVariable()
    {
        global $argv;

        for ($i = 1; $i < count($argv); $i++) {
            // remove PHPUnit options/arguments that get interpreted as tasks.
            if (isset($argv[$i])) {
                unset($argv[$i]);
            }
        }

        putenv('TR_PHPUNIT=php:./bin/php');

        $this->expectOutputRegex('~(' . preg_quote('./bin/php') . ')~');

        (new TaskRunnerMock())->start();

        putenv('TR_PHPUNIT=php:');

        $this->expectExceptionMessageMatches("/(The 'TR_PHPUNIT' environment variable pattern is invalid)/");

        (new TaskRunnerMock())->start();
    }
}
