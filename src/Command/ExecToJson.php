<?php

declare(strict_types=1);

namespace TypistTech\ComSem\Command;

use Closure;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function get_debug_type;
use function json_encode;

trait ExecToJson
{
    protected string $name;

    private function exec(OutputInterface $output, Closure $fn): int
    {
        try {
            $this->writeJson($output, [
                'command' => $this->name,
                'result' => $fn(),
            ]);

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $this->writeJson($output, [
                'command' => $this->name,
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
        $output->writeln($json, OutputInterface::OUTPUT_RAW);
    }
}
