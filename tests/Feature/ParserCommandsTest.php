<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;

it('returns parsed stability for beta versions', function (): void {
    $result = run_comsem([
        'command' => 'parser:parse-stability',
        'version' => '1.0.0-beta2',
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => 'beta',
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

it('preserves permissive parse stability behavior for dev branches', function (): void {
    $result = run_comsem([
        'command' => 'parser:parse-stability',
        'version' => 'dev-main',
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => 'dev',
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

it('preserves permissive parse stability behavior for malformed versions', function (): void {
    $result = run_comsem([
        'command' => 'parser:parse-stability',
        'version' => 'not-a-version',
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => 'stable',
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

it('normalizes versions through a VersionParser instance method', function (): void {
    $result = run_comsem([
        'command' => 'parser:normalize',
        'version' => 'v1.2.3',
    ]);
    $json = require_json_object($result);

    $expected = [
        'result' => '1.2.3.0',
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

it('returns json when normalize rejects malformed versions', function (): void {
    $result = run_comsem([
        'command' => 'parser:normalize',
        'version' => 'not-a-version',
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

it('preserves tag-like strings in json output by writing raw output', function (): void {
    $result = run_comsem([
        'command' => 'parser:normalize',
        'version' => '<info>boom</info>',
    ]);
    $json = require_json_object($result);

    $expected = [
        'error' => [
            'class' => 'UnexpectedValueException',
            'message' => 'Invalid version string "<info>boom</info>"',
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

it('returns true for valid parser is-valid checks', function (): void {
    $result = run_comsem([
        'command' => 'parser:is-valid',
        'version' => '1.0.0',
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

it('returns false for invalid parser is-valid checks without failing the command', function (): void {
    $result = run_comsem([
        'command' => 'parser:is-valid',
        'version' => 'not-a-version',
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
