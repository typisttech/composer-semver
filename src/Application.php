<?php

declare(strict_types = 1);

namespace TypistTech\ComSem;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TypistTech\ComSem\Command\JsonCommand;

use function implode;
use function sprintf;

final class Application extends SymfonyConsoleApplication
{
    #[\Override]
    public function getLongVersion(): string
    {
        return implode(PHP_EOL, [
            sprintf('%s %s', $this->getName(), $this->getVersion()),
            sprintf('composer/semver %s', self::packageVersion('composer/semver')),
            sprintf('symfony/console %s', self::packageVersion('symfony/console')),
            sprintf('PHP %s (%s)', PHP_VERSION, PHP_SAPI)
        ]);
    }

    /**
     * @throws Throwable
     */
    #[\Override]
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if (!$command instanceof JsonCommand) {
            return parent::doRunCommand($command, $input, $output);
        }

        try {
            return parent::doRunCommand($command, $input, $output);
        } catch (Throwable $throwable) {
            return $command->renderFailure($throwable, $output);
        }
    }

    private static function packageVersion(string $packageName): string
    {
        return InstalledVersions::getPrettyVersion($packageName) ?? 'UNKNOWN';
    }
}
