<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'comparator:greater-than',
    description: 'Compare whether the first version is greater than the second.',
    help: '{github:composer/semver|src/Comparator.php|greaterThan|static|Composer\\\\Semver\\\\Comparator::greaterThan()|and returns its boolean result as JSON.}',
    usages: ['1.25.0 1.24.0']
)]
final class GreaterThanCommand extends JsonCommand
{
    public function __invoke(
        OutputInterface $output,
        #[Argument('The first version string.')]
        string $version1,
        #[Argument('The second version string.')]
        string $version2
    ): int {
        return $this->writeSuccess($output, Comparator::greaterThan($version1, $version2));
    }
}
