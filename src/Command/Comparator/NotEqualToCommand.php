<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\ExecToJson;

#[AsCommand(
    name: 'comparator:not-equal-to',
    description: 'Compare whether the two versions are different',
    help: <<<HELP
        Wraps <href=%semver.url%/src/Comparator.php>Composer\Semver\Comparator::notEqualTo()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/Comparator.php>%semver.url%/src/Comparator.php</>
        HELP,
    usages: ['1.24.0 1.25.0'],
)]
class NotEqualToCommand extends AbstractCommand
{
    protected function compare(string $version1, string $version2): bool
    {
        return Comparator::notEqualTo($version1, $version2);
    }
}
