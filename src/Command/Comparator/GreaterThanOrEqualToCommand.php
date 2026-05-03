<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'comparator:greater-than-or-equal-to',
    description: 'Compare whether the first version is greater than or equal to the second.',
    help: GreaterThanOrEqualToCommand::HELP,
    usages: ['1.25.0 1.25.0']
)]
final class GreaterThanOrEqualToCommand extends JsonCommand
{
    public const string HELP = <<<HELP
        Wraps <href=%semver.url%/src/Comparator.php>Composer\Semver\Comparator::greaterThanOrEqualTo()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/Comparator.php>%semver.url%/src/Comparator.php</>
        HELP;

    public function __invoke(
        OutputInterface $output,
        #[Argument('The first version string.')]
        string $version1,
        #[Argument('The second version string.')]
        string $version2
    ): int {
        return $this->writeSuccess($output, Comparator::greaterThanOrEqualTo($version1, $version2));
    }
}
