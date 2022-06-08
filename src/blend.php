<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MAKS\Blend;

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

            return Blend::FAILURE;
        }

        $example = [
            '$schema'      => 'https://raw.githubusercontent.com/MarwanAlsoltany/blend/master/config/schema.json',
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
                    'description' => 'Lists content of CWD or the passed one (example task).',
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

        $this->say("Generated '@(g)[{{$filename}}]' in '@(y)[{{$directory}}]'");

        return Blend::SUCCESS;
    }
);

if (extension_loaded('phar') && strlen($phar = \Phar::running(false))) {
    $blend->addCallbackTask(
        'phar:update',
        'Updates Blend PHAR to the latest version from remote.',
        function () use ($phar) {
            $repo = 'https://github.com/MarwanAlsoltany/blend/releases/latest/download/blend.phar';
            $temp = tempnam(sys_get_temp_dir(), 'BUF');
            $code = Blend::SUCCESS;

            if (copy($repo, $temp)) {
                $this->say('Checking if an update is available.');

                if (sha1_file($temp) === sha1_file($phar)) {
                    $this->say('@(y)[{You are already running the latest version of Blend.}]');
                } else {
                    $this->say('@(y)[{A new Blend version has been found!}]');

                    if (copy($temp, $phar)) {
                        $this->say('@(g)[{Updated Blend PHAR to the latest version!}]');
                    } else {
                        $this->say('@(r)[{Failed to update Blend. Could not replace old source!}]');
                        $code = Blend::FAILURE;
                    }
                }

                $this->say('Cleaning up temporary files.');
            } else {
                $this->say('@(r)[{Failed to update Blend. Could not fetch data from remote!}]');
                $code = Blend::FAILURE;
            }

            unlink($temp);

            return $code;
        }
    );
}

$blend->start();
