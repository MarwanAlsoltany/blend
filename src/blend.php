<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

require __DIR__ . '/TaskRunner.php';

use MAKS\Blend\TaskRunner as Blend;


$blend = new Blend();

$blend->setName('Blend');

$blend->addCallbackTask(
    'config:generate',
    'Generates a Blend config file in JSON format in CWD.',
    function () {
        $file = sprintf('%s/%s.config.json', getcwd(), basename($this->argv[0]));

        if (file_exists($file) && !in_array('--overwrite', $this->args)) {
            $this->say([
                '@(r)[{A config file already exists in this directory!}]',
                'Use the @(y)[{--overwrite}] flag to overwrite it.'
            ]);

            return 1;
        }

        $example = [
            'autoload'     => null,
            'merge'        => true,
            'executables'  => [
                'php' => [
                    './bin/*'
                ],
            ],
            'translations' => [
                'abc' => 'xyz',
            ],
            'ansi'         => true,
            'quiet'        => false,
            'tasks'        => [
                'ls' => [
                    'name'        => 'ls',
                    'description' => 'Lists content of the current directory or the passed one. (example task)',
                    'executor'    => 'shell',
                    'executable'  => 'ls',
                    'arguments'   => '-lash',
                    'hidden'      => false,
                    'disabled'    => false,
                ],
            ],
        ];

        file_put_contents($file, json_encode($example, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT), LOCK_EX);

        $filename  = basename($file);
        $directory = dirname($file);

        $this->say("Generated '@(y)[{{$filename}}]' in '@(y)[{{$directory}}]'");
    }
);

$blend->start();
