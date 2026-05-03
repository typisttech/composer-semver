<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command\Semver;

use Composer\Semver\Semver;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\JsonCommand;

#[AsCommand(
    name: 'semver:satisfies',
    description: 'Check whether a version satisfies the given constraints.',
    help: '',
    usages: ["1.2.3 '^1.0'"]
)]
final class SatisfiesCommand extends JsonCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->setHelp(self::githubMethodHelp(
            'composer/semver',
            'src/Semver.php',
            'satisfies',
            true,
            'Composer\\\\Semver\\\\Semver::satisfies()',
            'and returns its boolean result as JSON.'
        ));
    }
    /**
     * @throws \UnexpectedValueException
     */
    public function __invoke(
        OutputInterface $output,
        #[Argument('The version to test.')]
        string $version,
        #[Argument('The Composer constraint string.')]
        string $constraints
    ): int {
        return $this->writeSuccess($output, Semver::satisfies($version, $constraints));
    }
}
