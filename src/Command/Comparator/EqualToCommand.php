<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'comparator:equal-to',
    description: 'Compare whether the two versions are equal',
    help: <<<HELP
        Wraps <href=%semver.url%/src/Comparator.php>Composer\Semver\Comparator::equalTo()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/Comparator.php>%semver.url%/src/Comparator.php</>
        HELP,
    usages: ['1.24.0 1.24.0'],
)]
class EqualToCommand extends AbstractCommand
{
    #[\Override]
    protected function compare(string $version1, string $version2): bool
    {
        return Comparator::equalTo($version1, $version2);
    }
}
