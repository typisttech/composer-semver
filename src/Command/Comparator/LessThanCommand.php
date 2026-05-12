<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'comparator:less-than',
    description: 'Compare whether the first version is less than the second',
    help: <<<HELP
        Wraps <href=%semver.url%/src/Comparator.php>Composer\Semver\Comparator::lessThan()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/Comparator.php>%semver.url%/src/Comparator.php</>
        HELP,
    usages: ['1.24.0 1.25.0'],
)]
class LessThanCommand extends AbstractCommand
{
    #[\Override]
    protected function compare(string $version1, string $version2): bool
    {
        return Comparator::lessThan($version1, $version2);
    }
}
