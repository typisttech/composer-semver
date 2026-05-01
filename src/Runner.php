<?php

declare(strict_types = 1);

namespace TypistTech\ComSem;

use Composer\InstalledVersions;
use Symfony\Component\Console\Command\Command;
use TypistTech\ComSem\Command\Comparator\EqualToCommand;
use TypistTech\ComSem\Command\Comparator\GreaterThanCommand;
use TypistTech\ComSem\Command\Comparator\GreaterThanOrEqualToCommand;
use TypistTech\ComSem\Command\Comparator\LessThanCommand;
use TypistTech\ComSem\Command\Comparator\LessThanOrEqualToCommand;
use TypistTech\ComSem\Command\Comparator\NotEqualToCommand;
use TypistTech\ComSem\Command\Parser\IsValidCommand;
use TypistTech\ComSem\Command\Parser\NormalizeCommand;
use TypistTech\ComSem\Command\Parser\ParseStabilityCommand;
use TypistTech\ComSem\Command\Semver\RSortCommand;
use TypistTech\ComSem\Command\Semver\SatisfiedByCommand;
use TypistTech\ComSem\Command\Semver\SatisfiesCommand;
use TypistTech\ComSem\Command\Semver\SortCommand;

final class Runner
{
    private const string NAME = 'comsem';
    private const string UNKNOWN_VERSION = 'UNKNOWN';

    public static function buildApplication(): Application
    {
        $application = new Application(self::NAME, self::version());
        $application->addCommands(self::commands());

        return $application;
    }

    /**
     * @throws \Exception
     */
    public static function run(): int
    {
        return self::buildApplication()->run();
    }

    /**
     * @return list<Command>
     */
    private static function commands(): array
    {
        return [
            new GreaterThanCommand(),
            new GreaterThanOrEqualToCommand(),
            new LessThanCommand(),
            new LessThanOrEqualToCommand(),
            new EqualToCommand(),
            new NotEqualToCommand(),
            new SatisfiesCommand(),
            new SatisfiedByCommand(),
            new SortCommand(),
            new RSortCommand(),
            new ParseStabilityCommand(),
            new IsValidCommand(),
            new NormalizeCommand()
        ];
    }

    private static function version(): string
    {
        $rootPackage = InstalledVersions::getRootPackage();

        if ($rootPackage['pretty_version'] !== '') {
            return $rootPackage['pretty_version'];
        }

        if ($rootPackage['version'] !== '') {
            return $rootPackage['version'];
        }

        return self::UNKNOWN_VERSION;
    }
}
