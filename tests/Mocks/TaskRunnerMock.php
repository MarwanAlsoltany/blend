<?php

declare(strict_types=1);

namespace MAKS\Blend\Tests\Mocks;

use MAKS\Blend\TaskRunner;

class TaskRunnerMock extends TaskRunner
{
    protected function terminate($code = 0): void
    {
        // prevent the script from exiting
    }
}
