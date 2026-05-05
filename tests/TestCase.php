<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use TypistTech\ComSem\Application;

abstract class TestCase extends BaseTestCase {
    /**
     * @param array<int|string, string> $input
     * @return array{status: int, stdout: string, stderr: string}
     */
    protected function runComSem(array $input): array
    {
        $application = new Application();
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);
        $status = $tester->run($input, [
            'interactive' => false,
            'decorated' => false,
            'capture_stderr_separately' => true,
        ]);

        return [
            'status' => $status,
            'stdout' => $tester->getDisplay(),
            'stderr' => $tester->getErrorOutput(),
        ];
    }
}
