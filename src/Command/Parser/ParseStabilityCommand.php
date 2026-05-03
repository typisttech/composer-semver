<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Parser;

use Composer\Semver\VersionParser;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'parser:parse-stability',
    description: 'Parse the stability string for a version.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/VersionParser.php>Composer\\\\Semver\\\\VersionParser::parseStability()</> and returns the stability string as JSON.\n\nSee <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/VersionParser.php#:~:text=public%20static%20function%20parseStability>Composer\\\\Semver\\\\VersionParser::parseStability()</>',
    usages: ['1.0.0-beta2']
)]
final class ParseStabilityCommand extends JsonCommand
{
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to inspect.')]
        string $version
    ): int {
        return $this->writeSuccess($output, VersionParser::parseStability($version));
    }
}
