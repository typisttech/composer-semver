<?php

declare(strict_types=1);

namespace TypistTech\ComposerSemVer;

use Composer\Semver\VersionParser;

class Runner
{
    private const string NAME = 'Composer SemVer';
    private const string GIT_TAG = '@git-tag@';

    public static function run(): int
    {
        $app = new Application(self::NAME, self::GIT_TAG);

        $parser = new VersionParser();

        $app->addCommands([
            new NormalizeCommand($parser),
            new ParseCommand($parser),
        ]);

        return $app->run();
    }
}
