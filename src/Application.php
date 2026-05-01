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
            sprintf('composer/semver %s', InstalledVersions::getPrettyVersion('composer/semver') ?? 'UNKNOWN'),
            sprintf('symfony/console %s', InstalledVersions::getPrettyVersion('symfony/console') ?? 'UNKNOWN'),
            sprintf('PHP %s (%s)', PHP_VERSION, PHP_SAPI)
        ]);
    }

    #[\Override]
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        try {
            return parent::doRunCommand($command, $input, $output);
        } catch (Throwable $throwable) {
            if ($command instanceof JsonCommand) {
                return $command->writeFailure($output, $throwable);
            }
            throw $throwable;
        }
    }
}
