<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Semver;

use Composer\Semver\Semver;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'semver:satisfied-by',
    description: 'Filter versions by the given constraints.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/%semver.reference%/src/Semver.php#:~:text=public%20static%20function%20satisfiedBy>Composer\\\\Semver\\\\Semver::satisfiedBy()</>. The CLI uses <constraints> <versions>... because Symfony requires array arguments to come last.\n\nSee <href=https://github.com/composer/semver/blob/%semver.reference%/src/Semver.php#:~:text=public%20static%20function%20satisfiedBy>https://github.com/composer/semver/blob/%semver.reference%/src/Semver.php#:~:text=public%20static%20function%20satisfiedBy</>',
    usages: ["'^1.0' 1.0.0 1.2.0 2.0.0"]
)]
final class SatisfiedByCommand extends JsonCommand
{
    /**
     * @param list<string> $versions
     * @throws \UnexpectedValueException
     */
    public function __invoke(
        OutputInterface $output,
        #[Argument('The Composer constraint string.')]
        string $constraints,
        #[Argument('The versions to filter.')]
        array $versions
    ): int {
        return $this->writeSuccess($output, Semver::satisfiedBy($versions, $constraints));
    }
}
