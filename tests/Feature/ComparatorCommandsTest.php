<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;

dataset('comparator commands', [
    ['comparator:equal-to',                 '1.2.3',   '1.2.3', true],
    ['comparator:equal-to',                 '1.2.3.0', '1.2.3', true],

    ['comparator:greater-than',             '9.8.7',   '1.2.3', true],
    ['comparator:greater-than',             '1.2.3',   '9.8.7', false],
    ['comparator:greater-than',             '1.2.3',   '1.2.3', false],

    ['comparator:greater-than-or-equal-to', '9.8.7',   '9.8.7', true],
    ['comparator:less-than',                '1.2.3',   '9.8.7', true],
    ['comparator:less-than-or-equal-to',    '1.2.3',   '1.2.3', true],
    ['comparator:equal-to',                 '1.2.3',   '1.2.3', true],
    ['comparator:not-equal-to',             '1.2.3',   '9.8.7', true],
]);

test('comparator commands', function (string $commandName, string $version1, string $version2, bool $expected): void {
    $inputs = [
        'command' => $commandName,
        'version1' => $version1,
        'version2' => $version2,
    ];

    /** @var \Tests\TestCase $this */
    [
        'status' => $status,
        'stdout' => $stdout,
        'stderr' => $stderr,
    ] = $this->runComSem($inputs);

    expect($status)->toBe(Command::SUCCESS);
    expect($stderr)->toBeEmpty();

    // @mago-expect analysis:mixed-method-access,non-documented-property
    // @phpstan-ignore method.nonObject
    expect($stdout)->json()->result->toBe($expected);
})->with('comparator commands');
