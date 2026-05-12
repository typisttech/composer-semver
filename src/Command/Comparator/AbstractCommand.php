<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command\Comparator;

use Composer\Semver\VersionParser;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Output\OutputInterface;
use TypistTech\ComSem\Command\ExecToJson;

abstract class AbstractCommand
{
    use ExecToJson;

    public function __invoke(
        OutputInterface $output,
        #[Argument('The first version string.')]
        string $version1,
        #[Argument('The second version string.')]
        string $version2,
    ): int {
        return $this->exec($output, function () use ($version1, $version2): bool {
            $parser = new VersionParser();

            $v1 = $parser->normalize($version1);
            $v2 = $parser->normalize($version2);

            return $this->compare($v1, $v2);
        });
    }

    abstract protected function compare(string $version1, string $version2): bool;
}
