<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\ExecToJson;

#[AsCommand(
    name: 'comparator:greater-than-or-equal-to',
    description: 'Compare whether the first version is greater than or equal to the second',
    help: <<<HELP
        Wraps <href=%semver.url%/src/Comparator.php>Composer\Semver\Comparator::greaterThanOrEqualTo()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/Comparator.php>%semver.url%/src/Comparator.php</>
        HELP,
    usages: ['1.25.0 1.25.0'],
)]
class GreaterThanOrEqualToCommand extends AbstractCommand
{

    protected function compare(string $version1, string $version2): bool
    {
        return Comparator::greaterThanOrEqualTo($version1, $version2);
    }
}
