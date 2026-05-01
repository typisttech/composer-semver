<?php

declare(strict_types = 1);

use Symfony\Component\Console\Command\Command;

it('keeps top level help text-based', function (): void {
    $result = run_comsem([
        '--help' => true
    ]);

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['json'])
        ->toBeNull()
        ->and($result['stdout'])
        ->toContain('Usage:')
        ->and($result['stdout'])
        ->toContain('list [options] [--] [<namespace>]');
});

it('keeps command help text-based and includes wrapped method help', function (): void {
    $result = run_comsem([
        'command' => 'help',
        'command_name' => 'comparator:greater-than'
    ]);

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['json'])
        ->toBeNull()
        ->and($result['stdout'])
        ->toContain('comparator:greater-than')
        ->and($result['stdout'])
        ->toContain('Wraps Composer\\Semver\\Comparator::greaterThan()')
        ->and($result['stdout'])
        ->toContain('comparator:greater-than 1.25.0 1.24.0');
});

it('keeps list output text-based and exposes custom commands', function (): void {
    $result = run_comsem([
        'command' => 'list'
    ]);

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['json'])
        ->toBeNull()
        ->and($result['stdout'])
        ->toContain('comparator:greater-than')
        ->and($result['stdout'])
        ->toContain('semver:satisfied-by')
        ->and($result['stdout'])
        ->toContain('parser:normalize');
});

it('keeps version output text-based and detailed', function (): void {
    $result = run_comsem([
        '--version' => true
    ]);

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['json'])
        ->toBeNull()
        ->and($result['stdout'])
        ->toContain('comsem')
        ->and($result['stdout'])
        ->toContain('composer/semver')
        ->and($result['stdout'])
        ->toContain('symfony/console')
        ->and($result['stdout'])
        ->toContain('PHP ' . PHP_VERSION);
});

it('keeps completion help text-based', function (): void {
    $result = run_comsem([
        'command' => 'completion',
        '--help' => true
    ]);

    expect($result['status'])
        ->toBe(Command::SUCCESS)
        ->and($result['json'])
        ->toBeNull()
        ->and($result['stdout'])
        ->toContain('completion')
        ->and($result['stdout'])
        ->toContain('Dump the shell completion script');
});
