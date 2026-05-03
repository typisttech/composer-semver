<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Parser;

use Composer\Semver\VersionParser;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\ExecToJson;

#[AsCommand(
    name: 'parser:parse-stability',
    description: 'Parse the stability string for a version',
    help: <<<HELP
        Wraps <href=%semver.url%/src/VersionParser.php>Composer\Semver\VersionParser::parseStability()</> and returns the stability string as JSON.

        See <href=%semver.url%/src/VersionParser.php>%semver.url%/src/VersionParser.php</>
        HELP,
    usages: ['1.0.0-beta2'],
)]
class ParseStabilityCommand
{
    use ExecToJson;

    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to inspect.')]
        string $version,
    ): int {
        return $this->exec($output, static fn() => VersionParser::parseStability($version));
    }
}
