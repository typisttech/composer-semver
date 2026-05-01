<?php

declare(strict_types = 1);

use Symfony\Component\Console\Tester\ApplicationTester;
use TypistTech\ComSem\Runner;

if (!function_exists('run_comsem')) {
    /**
     * @param array<string, mixed> $input
     * @return array{status:int, stdout:string, stderr:string, json:array<array-key, mixed>|null}
     * @throws RuntimeException
     */
    function run_comsem(array $input): array
    {
        $application = Runner::buildApplication();
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);
        $status = $tester->run($input, [
            'interactive' => false,
            'decorated' => false,
            'capture_stderr_separately' => true
        ]);

        $stdout = $tester->getDisplay();
        $stderr = $tester->getErrorOutput();

        return [
            'status' => $status,
            'stdout' => $stdout,
            'stderr' => $stderr,
            'json' => decode_json_payload($stdout)
        ];
    }
}

if (!function_exists('decode_json_payload')) {
    /**
     * @return array<array-key, mixed>|null
     */
    function decode_json_payload(string $json): ?array
    {
        if (!json_validate($json)) {
            return null;
        }

        /** @var array<array-key, mixed>|scalar|null $decoded */
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('require_json_object')) {
    /**
     * @param array{status:int, stdout:string, stderr:string, json:array<array-key, mixed>|null} $result
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    function require_json_object(array $result): array
    {
        $json = $result['json'];

        if (!is_array($json) || array_is_list($json)) {
            throw new RuntimeException('Expected a JSON object payload.');
        }

        /** @var array<string, mixed> $json */
        return $json;
    }
}
