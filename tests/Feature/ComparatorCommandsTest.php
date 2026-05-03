<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;

dataset('successful comparator commands', [
    ['comparator:greater-than',             '1.25.0', '1.24.0'],
    ['comparator:greater-than-or-equal-to', '1.25.0', '1.25.0'],
    ['comparator:less-than',                '1.24.0', '1.25.0'],
    ['comparator:less-than-or-equal-to',    '1.24.0', '1.24.0'],
    ['comparator:equal-to',                 '1.24.0', '1.24.0'],
    ['comparator:not-equal-to',             '1.24.0', '1.25.0'],
]);

it('returns success json for comparator commands', function (
    string $commandName,
    string $version1,
    string $version2,
): void {
    $result = run_comsem([
        'command' => $commandName,
        'version1' => $version1,
        'version2' => $version2,
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => true,
    ];

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['stderr'])
        ->toBeEmpty()
        ->and($json)
        ->toBe($expected)
        ->and($result['stdout'])
        ->toBe(json_encode($expected, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL);
})->with('successful comparator commands');

it('preserves comparator false results as successful command execution', function (): void {
    $result = run_comsem([
        'command' => 'comparator:greater-than',
        'version1' => 'not-a-version',
        'version2' => '1.0.0',
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => false,
    ];

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['stderr'])
        ->toBeEmpty()
        ->and($json)
        ->toBe($expected)
        ->and($result['stdout'])
        ->toBe(json_encode($expected, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL);
});
