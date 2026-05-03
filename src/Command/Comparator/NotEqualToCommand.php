<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'comparator:not-equal-to',
    description: 'Compare whether the two versions are different.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/Comparator.php>Composer\\\\Semver\\\\Comparator::notEqualTo()</> and returns its boolean result as JSON.\n\nSee <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/Comparator.php#:~:text=public%20static%20function%20notEqualTo>Composer\\\\Semver\\\\Comparator::notEqualTo()</>',
    usages: ['1.24.0 1.25.0']
)]
final class NotEqualToCommand extends JsonCommand
{
    public function __invoke(
        OutputInterface $output,
        #[Argument('The first version string.')]
        string $version1,
        #[Argument('The second version string.')]
        string $version2
    ): int {
        return $this->writeSuccess($output, Comparator::notEqualTo($version1, $version2));
    }
}
