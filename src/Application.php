<?php

declare(strict_types=1);

namespace TypistTech\ComSem;

use Composer\InstalledVersions;
use Override;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

use function implode;
use function sprintf;

final class Application extends SymfonyConsoleApplication
{
    #[Override]
    public function getLongVersion(): string
    {
        return implode(PHP_EOL, [
            sprintf('%s %s', $this->getName(), $this->getVersion()),
            sprintf('composer/semver %s', InstalledVersions::getPrettyVersion('composer/semver') ?? 'UNKNOWN'),
            sprintf('symfony/console %s', InstalledVersions::getPrettyVersion('symfony/console') ?? 'UNKNOWN'),
            sprintf('PHP %s (%s)', PHP_VERSION, PHP_SAPI),
        ]);
    }
}
