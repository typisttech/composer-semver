<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Semver;

use Composer\Semver\Semver;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'semver:rsort',
    description: 'Sort versions in descending Composer order.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/Semver.php>Composer\\\\Semver\\\\Semver::rsort()</> and returns the reverse-sorted version list as JSON.\n\nSee <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/Semver.php#:~:text=public%20static%20function%20rsort>Composer\\\\Semver\\\\Semver::rsort()</>',
    usages: ['1.0.0-beta 1.0.0 2.0.0']
)]
final class RSortCommand extends JsonCommand
{
    /**
     * @param list<string> $versions
     * @throws \UnexpectedValueException
     */
    public function __invoke(
        OutputInterface $output,
        #[Argument('The versions to reverse sort.')]
        array $versions
    ): int {
        return $this->writeSuccess($output, Semver::rsort($versions));
    }
}
