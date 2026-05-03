<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command;

use Closure;
use Composer\InstalledVersions;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function get_debug_type;
use function json_encode;

abstract class JsonCommand extends Command
{
    final protected function exec(OutputInterface $output, Closure $fn): int
    {
        try {
            $this->writeJson($output, [
                'command' => $this->getName(),
                'result' => $fn(),
            ]);

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $this->writeJson($output, [
                'command' => $this->getName(),
                'error' => [
                    'class' => get_debug_type($throwable),
                    'message' => $throwable->getMessage(),
                ],
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function writeJson(OutputInterface $output, array $payload): void
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $output->write($json, true, OutputInterface::OUTPUT_RAW);
    }

    #[Override]
    public function getProcessedHelp(): string
    {
        $ref = (string) InstalledVersions::getPrettyVersion('composer/semver');
        if ($ref === '' || str_starts_with($ref, 'dev-')) {
            $ref = (string) InstalledVersions::getReference('composer/semver');
        }
        if ($ref === '') {
            $ref = 'main';
        }
        $url = sprintf('https://github.com/composer/semver/blob/%s', $ref);

        return str_replace('%semver.url%', $url, parent::getProcessedHelp());
    }
}
