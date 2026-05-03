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
    help: IsValidCommand::HELP,
    usages: ['1.0.0']
)]
final class IsValidCommand extends JsonCommand
{
    public const string HELP = <<<HELP
        Wraps <href=%semver.url%/src/VersionParser.php>Composer\Semver\VersionParser::isValid()</> and returns its boolean result as JSON.

        See <href=%semver.url%/src/VersionParser.php>%semver.url%/src/VersionParser.php</>
        HELP;

    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to validate.')]
        string $version
    ): int {
        $parser = new VersionParser();

        return $this->writeSuccess($output, $parser->isValid($version));
    }
}
