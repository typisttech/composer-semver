<?php

declare(strict_types=1);

namespace TypistTech\ComposerSemVer;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;

class Application extends SymfonyConsoleApplication
{
    private const BANNER = <<<BANNER
                        ____
                       / ___|___  _ __ ___  _ __   ___  ___  ___ _ __
                      | |   / _ \| '_ ` _ \| '_ \ / _ \/ __|/ _ \ '__|
                      | |__| (_) | | | | | | |_) | (_) \__ \  __/ |
                       \____\___/|_| |_| |_| .__/_\___/|___/\___|_|
                      / ___|  ___ _ __ __\ \_| / /__ _ __
                      \___ \ / _ \ '_ ` _ \ \ / / _ \ '__|
                       ___) |  __/ | | | | \ V /  __/ |
                      |____/ \___|_| |_| |_|\_/ \___|_|
                      BANNER;

    public const BUILD_TIMESTAMP = '@datetime@';

    public function getLongVersion(): string
    {
        $longVersion = self::BANNER;
        $longVersion .= PHP_EOL . PHP_EOL;

        $app = sprintf(
            '%-15s <info>%s</info> %s',
            $this->getName(),
            $this->getVersion(),
            self::BUILD_TIMESTAMP,
        );
        $longVersion .= $app;

        $githubUrl = sprintf(
            '<href=https://github.com/typisttech/composer-semver/releases/tag/%1$s>https://github.com/typisttech/composer-semver/releases/tag/%1$s</>',
            $this->getVersion(),
        );
        // https://github.com/box-project/box/blob/b0123f358f2a32488c92e09bf56f16d185e4e3cb/src/Configuration/Configuration.php#L2116
        if (preg_match('/^(?<tag>.+)-\d+-g(?<hash>[a-f0-9]{7})$/', $this->getVersion(), $matches)) {
            // Not on a tag.
            $githubUrl = sprintf(
                '<href=https://github.com/typisttech/composer-semver/compare/%1$s...%2$s>https://github.com/typisttech/composer-semver/compare/%1$s...%2$s</>',
                $matches['tag'],
                $matches['hash'],
            );
        }
        $longVersion .= PHP_EOL . $githubUrl;

        $longVersion .= PHP_EOL . PHP_EOL . '<comment>Built with:</>';

        $semVerVersion = InstalledVersions::getPrettyVersion('composer/semver');
        $semVer = sprintf(
            '%1$-15s %2$s',
            'composer/semver',
            $semVerVersion,
        );
        $longVersion .= PHP_EOL . $semVer;

        $semVerReleaseUrl = sprintf(
            '<href=https://github.com/composer/semver/releases/tag/%1$s>https://github.com/composer/semver/releases/tag/%1$s</>',
            $semVerVersion,
        );
        $longVersion .= PHP_EOL . $semVerReleaseUrl;

        $longVersion .= PHP_EOL . PHP_EOL . '<comment>PHP:</>';

        $phpVersion = sprintf(
            '%-15s %s',
            'Version',
            PHP_VERSION,
        );
        $longVersion .= PHP_EOL . $phpVersion;

        // TODO: Print PHP_BUILD_DATE for PHP >=8.5.

        $phpSapi = sprintf(
            '%-15s %s',
            'SAPI',
            PHP_SAPI,
        );
        $longVersion .= PHP_EOL . $phpSapi;

        // TODO: Add sponsor info.

        $longVersion .= PHP_EOL;

        return $longVersion;
    }
}
