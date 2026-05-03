<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Parser;

use Composer\Semver\VersionParser;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'parser:normalize',
    description: 'Normalize a version string using Composer rules.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/VersionParser.php>the Composer\\\\Semver\\\\VersionParser instance method normalize()</> and returns the normalized version string as JSON.\n\nSee <href=https://github.com/composer/semver/blob/09af5e85b5f1380e4e098dde28950e2549cba4ed/src/VersionParser.php#:~:text=public%20function%20normalize>Composer\\\\Semver\\\\VersionParser::normalize()</>',
    usages: ['v1.2.3']
)]
final class NormalizeCommand extends JsonCommand
{
    /**
     * @throws \UnexpectedValueException
     */
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to normalize.')]
        string $version
    ): int {
        $parser = new VersionParser();

        return $this->writeSuccess($output, $parser->normalize($version));
    }
}
