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
    help: '',
    usages: ['1.0.0']
)]
final class IsValidCommand extends JsonCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->setHelp(self::githubMethodHelp(
            'composer/semver',
            'src/VersionParser.php',
            'isValid',
            false,
            'Composer\\\\Semver\\\\VersionParser::isValid()',
            'and returns its boolean result as JSON.'
        ));
    }
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version string to validate.')]
        string $version
    ): int {
        $parser = new VersionParser();

        return $this->writeSuccess($output, $parser->isValid($version));
    }
}
