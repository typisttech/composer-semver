<?php

declare(strict_types=1);

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
    /** @var class-string<Command>[]  */
    private const array COMMANDS = [
        // Comparator
        EqualToCommand::class,
        GreaterThanCommand::class,
        GreaterThanOrEqualToCommand::class,
        LessThanCommand::class,
        LessThanOrEqualToCommand::class,
        NotEqualToCommand::class,

        //Parser
        IsValidCommand::class,
        NormalizeCommand::class,
        ParseStabilityCommand::class,

        // Semver
        RSortCommand::class,
        SatisfiedByCommand::class,
        SatisfiesCommand::class,
        SortCommand::class,
    ];

    public static function buildApplication(): Application
    {
        $application = new Application('comsem', self::version());

        foreach (self::COMMANDS as $command) {
            // @mago-expect analysis:unsafe-instantiation
            $application->addCommand(new $command());
        }

        return $application;
    }

    public static function run(): int
    {
        return self::buildApplication()->run();
    }

    private static function version(): string
    {
        $rootPackage = InstalledVersions::getRootPackage();

        $version = $rootPackage['pretty_version'];
        if ($version === '') {
            $version = $rootPackage['version'];
        }

        $reference = (string) $rootPackage['reference'];
        $isDev = $rootPackage['dev'];
        if ($isDev && $reference !== '' && $version !== '') {
            $version = sprintf('%s#%s', $version, $reference);
        }

        return $version !== '' ? $version : 'UNKNOWN';
    }
}
