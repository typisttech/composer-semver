<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Parser;

use Composer\Semver\VersionParser;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'parser:is-valid',
    description: 'Check whether a version is valid for Composer parsing.',
    help: 'Wraps <href=https://github.com/composer/semver/blob/%semver.reference%/src/VersionParser.php#:~:text=public%20function%20isValid>the Composer\\\\Semver\\\\VersionParser instance method isValid()</> and returns its boolean result as JSON.\n\nSee <href=https://github.com/composer/semver/blob/%semver.reference%/src/VersionParser.php#:~:text=public%20function%20isValid>https://github.com/composer/semver/blob/%semver.reference%/src/VersionParser.php#:~:text=public%20function%20isValid</>',
    usages: ['1.0.0']
)]
final class IsValidCommand extends JsonCommand
{
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to validate.')]
        string $version
    ): int {
        $parser = new VersionParser();

        return $this->writeSuccess($output, $parser->isValid($version));
    }
}
