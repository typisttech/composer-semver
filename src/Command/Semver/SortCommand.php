<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Semver;

use Composer\Semver\Semver;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\ExecToJson;
use UnexpectedValueException;

#[AsCommand(
    name: 'semver:sort',
    description: 'Sort versions in ascending Composer order',
    help: <<<HELP
        Wraps <href=%semver.url%/src/Semver.php>Composer\Semver\Semver::sort()</> and returns the sorted version list as JSON.

        See <href=%semver.url%/src/Semver.php>%semver.url%/src/Semver.php</>
        HELP,
    usages: ['1.0.0-beta 1.0.0 2.0.0'],
)]
class SortCommand
{
    use ExecToJson;

    /**
     * @param list<string> $versions
     * @throws UnexpectedValueException
     */
    public function __invoke(OutputInterface $output, #[Argument('The versions to sort.')] array $versions): int
    {
        return $this->exec($output, static fn() => Semver::sort($versions));
    }
}
