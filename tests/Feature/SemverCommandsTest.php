<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;

it('returns success json for semver satisfies', function (): void {
    $result = run_comsem([
        'command' => 'semver:satisfies',
        'version' => '1.2.3',
        'constraints' => '^1.0',
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
});

it('returns filtered versions for semver satisfied-by', function (): void {
    $result = run_comsem([
        'command' => 'semver:satisfied-by',
        'constraints' => '^1.0',
        'versions' => ['1.0.0', '1.2.0', '2.0.0'],
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => ['1.0.0', '1.2.0'],
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

it('returns sorted versions for semver sort', function (): void {
    $result = run_comsem([
        'command' => 'semver:sort',
        'versions' => ['1.0.0-beta', '1.0.0', '2.0.0'],
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => ['1.0.0-beta', '1.0.0', '2.0.0'],
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

it('returns reverse sorted versions for semver rsort', function (): void {
    $result = run_comsem([
        'command' => 'semver:rsort',
        'versions' => ['1.0.0-beta', '1.0.0', '2.0.0'],
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => ['2.0.0', '1.0.0', '1.0.0-beta'],
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

it('returns json when semver sort rejects malformed versions', function (): void {
    $result = run_comsem([
        'command' => 'semver:sort',
        'versions' => ['1.0.0', 'not-a-version'],
    ]);
    $json = require_json_object($result);

    $expected = [
        'error' => [
            'class' => 'UnexpectedValueException',
            'message' => 'Invalid version string "not-a-version"',
        ],
    ];

    expect($result['status'])
        ->toBe(Command::FAILURE)
        ->and($result['stderr'])
        ->toBeEmpty()
        ->and($json)
        ->toBe($expected)
        ->and($result['stdout'])
        ->toBe(json_encode($expected, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL);
});

it('returns json when semver satisfies receives malformed constraints', function (): void {
    $result = run_comsem([
        'command' => 'semver:satisfies',
        'version' => '1.0.0',
        'constraints' => 'not-a-constraint',
    ]);
    $json = require_json_object($result);

    $expected = [
        'error' => [
            'class' => 'UnexpectedValueException',
            'message' => 'Could not parse version constraint not-a-constraint: Invalid version string "not-a-constraint"',
        ],
    ];

    expect($result['status'])
        ->toBe(Command::FAILURE)
        ->and($result['stderr'])
        ->toBeEmpty()
        ->and($json)
        ->toBe($expected)
        ->and($result['stdout'])
        ->toBe(json_encode($expected, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . PHP_EOL);
});
