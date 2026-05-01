<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Semver;

use Composer\Semver\Semver;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'semver:sort',
    description: 'Sort versions in ascending Composer order.',
    help: 'Wraps Composer\\Semver\\Semver::sort() and returns the sorted version list as JSON.',
    usages: ['1.0.0-beta 1.0.0 2.0.0']
)]
final class SortCommand extends JsonCommand
{
    /**
     * @param list<string> $versions
     * @throws \UnexpectedValueException
     */
    public function __invoke(
        OutputInterface $output,
        #[Argument('The versions to sort.')]
        array $versions
    ): int {
        return $this->writeSuccess($output, Semver::sort($versions));
    }
}
