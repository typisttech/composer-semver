<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\ExecToJson;

#[AsCommand(
    name: 'comparator:less-than-or-equal-to',
    description: 'Compare whether the first version is less than or equal to the second',
    help: <<<HELP
        Wraps <href=%semver.url%/src/Comparator.php>Composer\Semver\Comparator::lessThanOrEqualTo()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/Comparator.php>%semver.url%/src/Comparator.php</>
        HELP,
    usages: ['1.24.0 1.24.0'],
)]
class LessThanOrEqualToCommand
{
    use ExecToJson;

    public function __invoke(
        OutputInterface $output,
        #[Argument('The first version string.')]
        string $version1,
        #[Argument('The second version string.')]
        string $version2,
    ): int {
        return $this->exec($output, static fn() => Comparator::lessThanOrEqualTo($version1, $version2));
    }
}
