<?php

declare(strict_types=1);

namespace TypistTech\ComSem;

use Composer\InstalledVersions;
use Override;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
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

use function implode;
use function sprintf;

final class Application extends SymfonyConsoleApplication
{
    /** @var class-string[] */
    private const array COMMANDS = [
        // Comparator
        EqualToCommand::class,
        GreaterThanCommand::class,
        GreaterThanOrEqualToCommand::class,
        LessThanCommand::class,
        LessThanOrEqualToCommand::class,
        NotEqualToCommand::class,

        // Parser
        IsValidCommand::class,
        NormalizeCommand::class,
        ParseStabilityCommand::class,

        // Semver
        RSortCommand::class,
        SatisfiedByCommand::class,
        SatisfiesCommand::class,
        SortCommand::class,
    ];

    public function __construct(string $name = 'ComSem', string $version = '')
    {
        $version = $version !== '' ? $version : $this->getRootPackageVersion();
        parent::__construct($name, $version);
    }

    private function getRootPackageVersion(): string
    {
        $rootPackage = InstalledVersions::getRootPackage();

        $version = $rootPackage['pretty_version'];
        $version = $version === '' ? $rootPackage['version'] : $version;

        $reference = (string) $rootPackage['reference'];
        $isDev = $rootPackage['dev'];

        if ($isDev && $reference !== '' && $version !== '') {
            $version = sprintf('%s#%s', $version, $reference);
        }

        return $version !== '' ? $version : 'UNKNOWN';
    }

    #[Override]
    public function getHelp(): string
    {
        return implode(PHP_EOL, [
            parent::getHelp(),
            sprintf('composer/semver %s', InstalledVersions::getPrettyVersion('composer/semver') ?? 'UNKNOWN'),
            sprintf('symfony/console %s', InstalledVersions::getPrettyVersion('symfony/console') ?? 'UNKNOWN'),
            sprintf('PHP %s (%s)', PHP_VERSION, PHP_SAPI),
        ]);
    }

    #[Override]
    protected function getDefaultCommands(): array
    {
        $commands = array_map(
            function (string $klass): Command {
                // @mago-expect analysis:unknown-class-instantiation
                $code = new $klass();
                // @mago-expect analysis:invalid-argument
                $command = new Command(null, $code);

                $originalHelp = $command->getHelp();
                $processedHelp = $this->processCommandHelp($originalHelp);
                $command->setHelp($processedHelp);

                return $command;
            },
            self::COMMANDS,
        );

        return [
            ...$commands,
            ...parent::getDefaultCommands(),
        ];
    }

    private function processCommandHelp(string $text): string
    {
        $ref = (string) InstalledVersions::getPrettyVersion('composer/semver');
        if (str_starts_with($ref, 'dev-')) {
            $ref = substr($ref, 4);
        }
        if (str_ends_with($ref, '-dev')) {
            $ref = substr($ref, 0, -4);
        }
        $ref = $ref === '' ? (string) InstalledVersions::getReference('composer/semver') : $ref;
        $ref = $ref === '' ? 'main' : $ref;

        $url = sprintf('https://github.com/composer/semver/blob/%s', $ref);

        return str_replace('%semver.url%', $url, $text);
    }
}
