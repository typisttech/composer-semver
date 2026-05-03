<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'comparator:less-than',
    description: 'Compare whether the first version is less than the second.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/%semver.reference%/src/Comparator.php#:~:text=public%20static%20function%20lessThan>Composer\\\\Semver\\\\Comparator::lessThan()</> and returns its boolean result as JSON.\n\nSee <href=https://github.com/composer/semver/blob/%semver.reference%/src/Comparator.php#:~:text=public%20static%20function%20lessThan>https://github.com/composer/semver/blob/%semver.reference%/src/Comparator.php#:~:text=public%20static%20function%20lessThan</>',
    usages: ['1.24.0 1.25.0']
)]
final class LessThanCommand extends JsonCommand
{
    public function __invoke(
        OutputInterface $output,
        #[Argument('The first version string.')]
        string $version1,
        #[Argument('The second version string.')]
        string $version2
    ): int {
        return $this->writeSuccess($output, Comparator::lessThan($version1, $version2));
    }
}
