<?php

declare(strict_types = 1);

namespace TypistTech\ComSem\Command;

use Composer\InstalledVersions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function get_debug_type;
use function json_encode;

abstract class JsonCommand extends Command
{
    final protected function writeSuccess(OutputInterface $output, mixed $result): int
    {
        $this->writeJson($output, [
            'ok' => true,
            'command' => $this->commandName(),
            'result' => $result
        ]);

        return Command::SUCCESS;
    }

    final public function writeFailure(OutputInterface $output, Throwable $throwable): int
    {
        $isUsageThrowable = $throwable instanceof ExceptionInterface;

        $this->writeJson($output, [
            'ok' => false,
            'command' => $this->commandName(),
            'error' => [
                'category' => $isUsageThrowable ? 'usage' : 'runtime',
                'class' => get_debug_type($throwable),
                'message' => $throwable->getMessage()
            ]
        ]);

        return $isUsageThrowable ? Command::INVALID : Command::FAILURE;
    }

    /**
     * @param array<string, mixed> $payload
     */
    final protected function writeJson(OutputInterface $output, array $payload): void
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        $output->write($json, true, OutputInterface::OUTPUT_RAW);
    }

    private function commandName(): string
    {
        return $this->getName() ?? throw new \LogicException('Command name is not available.');
    }

    /**
     * Build a help string that links to the upstream GitHub source for a method.
     * Uses InstalledVersions::getReference() at runtime so links track the installed
     * package reference.
     */
    protected static function githubMethodHelp(string $package, string $filePath, string $methodName, bool $isStatic, string $display, string $suffix = ''): string
    {
        $ref = InstalledVersions::getReference($package) ?: 'HEAD';

        $base = sprintf('https://github.com/%s/blob/%s/%s', $package, $ref, $filePath);
        $fragment = ($isStatic ? 'public%20static%20function%20' : 'public%20function%20') . $methodName;

        $help = sprintf("Wraps <href=%s>%s</> %s\n\nSee <href=%s#:~:text=%s>%s", $base, $display, $suffix, $base, $fragment, $display);

        return $help;
    }
}
