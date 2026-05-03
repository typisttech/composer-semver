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
    help: '',
    usages: ['1.0.0-beta2']
)]
final class ParseStabilityCommand extends JsonCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->setHelp(self::githubMethodHelp(
            'composer/semver',
            'src/VersionParser.php',
            'parseStability',
            true,
            'Composer\\\\Semver\\\\VersionParser::parseStability()',
            'and returns the stability string as JSON.'
        ));
    }
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to inspect.')]
        string $version
    ): int {
        return $this->writeSuccess($output, VersionParser::parseStability($version));
    }
}
