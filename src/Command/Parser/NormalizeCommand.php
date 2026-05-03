<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Parser;

use Composer\Semver\VersionParser;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'parser:normalize',
    description: 'Normalize a version string using Composer rules',
    help: <<<HELP
        Wraps <href=%semver.url%/src/VersionParser.php>Composer\Semver\VersionParser::normalize()</> and returns the normalized version string as JSON.

        See <href=%semver.url%/src/VersionParser.php>%semver.url%/src/VersionParser.php</>
        HELP,
    usages: ['v1.2.3'],
)]
final class NormalizeCommand extends JsonCommand
{
    /**
     * @throws \UnexpectedValueException
     */
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to normalize.')]
        string $version,
    ): int {
        $parser = new VersionParser();

        return $this->exec($output, static fn() => $parser->normalize($version));
    }
}
