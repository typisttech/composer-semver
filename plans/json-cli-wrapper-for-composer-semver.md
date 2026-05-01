# Build The Initial JSON `comsem` CLI Around Documented `composer/semver` APIs

This ExecPlan is a living document. The sections `Progress`, `Surprises & Discoveries`, `Decision Log`, and `Outcomes & Retrospective` must be kept up to date as work proceeds.

This document was authored against the ExecPlan requirements in `~/.agent/PLANS.md`. That file is outside this repository, so every assumption needed to finish the work is restated here instead of being left implicit.

## Purpose / Big Picture

After this change, the repository will ship a working Symfony Console CLI in `bin/comsem` that wraps the documented `composer/semver` APIs with explicit subcommands. A user will be able to compare versions, test whether versions satisfy constraints, filter version lists by a constraint, sort version lists exactly the way Composer does, and call selected `VersionParser` helpers for stability parsing, validity checks, and normalization.

The most important user-visible behavior is that every custom command added by this project emits exactly one JSON object to standard output, whether the command succeeds or fails. Successful custom commands must return `Command::SUCCESS`. Invalid usage of a valid custom command must return `Command::INVALID`. All other custom-command failures must return `Command::FAILURE`. The user explicitly clarified that Symfony Console defaults such as `--help`, `--version`, `help`, `list`, and shell completion are exempt from that JSON and exit-code contract and may remain standard Symfony text commands.

The final implementation must make these examples work from the repository root at project root:

    php bin/comsem comparator:greater-than 1.25.0 1.24.0
    php bin/comsem semver:satisfies 1.2.3 '^1.0'
    php bin/comsem semver:satisfied-by '^1.0' 1.0.0 1.2.0 2.0.0
    php bin/comsem parser:parse-stability 1.0.0-beta2
    php bin/comsem parser:is-valid 1.0.0
    php bin/comsem parser:normalize v1.2.3
    php bin/comsem parser:parse-stability dev-main
    php bin/comsem parser:parse-stability not-a-version
    php bin/comsem semver:sort 1.0.0 not-a-version
    php bin/comsem semver:sort

The first six commands must return `Command::SUCCESS` and print JSON success payloads. `parser:parse-stability dev-main` must return `dev`, and `parser:parse-stability not-a-version` must still return a successful JSON payload with `stable`, documenting Composer's permissive parser behavior. `semver:sort 1.0.0 not-a-version` must return `Command::FAILURE` and print a JSON runtime error because `Composer\Semver\Semver::sort()` rejects malformed versions. `semver:sort` with no arguments must return `Command::INVALID` and print a JSON usage error because the user invoked a valid custom command without the required array argument.

## Progress

- [x] (2026-05-01 00:57Z) Read `~/.agent/PLANS.md` and extracted the requirements that matter here: self-contained context, observable acceptance criteria, exact commands, required living-document sections, and a revision note.
- [x] (2026-05-01 00:57Z) Inspected the current repository and confirmed the starting point: `bin/comsem` exists, `src/` is empty, `plans/` is empty, `composer.json` already requires `composer/semver`, `symfony/console`, Pest, PHPStan, and Mago, and `mago.toml` currently scans only `src/`.
- [x] (2026-05-01 00:57Z) Inspected the reference project at `~/Code/composer-semver` and captured the reusable patterns: keep `bin/*` as a thin bootstrap, keep a dedicated `Runner` class, keep a dedicated `Application` class, and define commands with Symfony attributes on `__invoke()` methods.
- [x] (2026-05-01 00:57Z) Enumerated the original in-scope API surface from `vendor/composer/semver/README.md`: six documented `Composer\Semver\Comparator` methods and four documented `Composer\Semver\Semver` methods. Confirmed that `Composer\Semver\Intervals` methods are intentionally out of scope.
- [x] (2026-05-01 00:57Z) Resolved the output-contract boundary after the user clarification: only custom commands added by this project must follow the JSON and exit-code rules; Symfony defaults remain untouched.
- [x] (2026-05-01 01:45Z) Re-inspected the repository after the user's updates and confirmed that Pest is now set up, `phpunit.xml` exists, `tests/Pest.php` and example tests exist, and `vendor/bin/pest` currently passes.
- [x] (2026-05-01 01:45Z) Inspected `Composer\Semver\VersionParser` usage and confirmed the current method surface in this repository: static methods `parseStability()` and `normalizeStability()`, and instance methods `isValid()`, `normalize()`, `parseNumericAliasPrefix()`, `normalizeBranch()`, `normalizeDefaultBranch()`, and `parseConstraints()`.
- [x] (2026-05-01 01:45Z) Updated this ExecPlan to add the parser command family, align with the repo's real Pest setup, use correct instance-method wording for `VersionParser::isValid()` and `VersionParser::normalize()`, and incorporate the final design-review recommendations around `OutputInterface`, stdout-only JSON routing, permissive behavior documentation, and test placement.
- [x] (2026-05-01 04:39Z) Implemented `src/Application.php` and `src/Runner.php`, restored a working Symfony Console application in `bin/comsem`, and added `Runner::buildApplication()` so feature tests can construct the application with auto-exit disabled.
- [x] (2026-05-01 04:39Z) Implemented the shared `JsonCommand` base class and all thirteen custom command classes under `src/Command/Comparator/`, `src/Command/Semver/`, and `src/Command/Parser/`, including stdout-only JSON success and failure handling through one raw-output `writeJson()` path.
- [x] (2026-05-01 04:39Z) Replaced scaffold tests with Pest feature coverage for comparator, semver, parser, and default Symfony behavior; added `tests/Support/cli.php`; and added `phpstan.neon` so `bin`, `src`, and `tests` are all analyzed.
- [x] (2026-05-01 04:39Z) Updated `mago.toml` so Mago covers `bin/`, `src/`, and `tests/`, and moved the PHP bootstrap body into `bin/comsem.php` so Mago 1.25.x can scan the executable logic while `bin/comsem` remains the thin entrypoint.
- [x] (2026-05-01 04:39Z) Ran the validation sequence and recorded the results: `composer dump-autoload`, targeted CLI smoke tests, `vendor/bin/pest`, `vendor/bin/phpstan analyse`, `mago list-files`, `mago format --check`, `mago lint`, and `mago analyze` all completed successfully. `mago analyze` still prints informational invalid-UTF8 notices from vendor includes, but reports `No issues found` for the project code.

## Surprises & Discoveries

- Observation: the repository does not yet contain any PHP source files under `src/`, so the existing executable cannot run at all.
  Evidence: `php bin/comsem --help` currently fails with `Fatal error: Uncaught Error: Class "TypistTech\ComSem\Runner" not found in ~/Code/comsem/bin/comsem:21`.

- Observation: `Composer\Semver\Comparator` does not validate malformed version strings in the same way the `Semver` class does. At least for `greaterThan`, malformed input still produces a boolean result instead of an exception.
  Evidence: `php -r 'require "vendor/autoload.php"; var_dump(\Composer\Semver\Comparator::greaterThan("not-a-version", "1.0.0"));'` printed `bool(false)`.

- Observation: `Composer\Semver\Semver` methods do validate malformed versions and throw `UnexpectedValueException`.
  Evidence: `php -r 'require "vendor/autoload.php"; try { var_dump(\Composer\Semver\Semver::sort(["1.0.0", "not-a-version"])); } catch (Throwable $e) { fwrite(STDERR, get_class($e).": ".$e->getMessage().PHP_EOL); exit(1); }'` printed `UnexpectedValueException: Invalid version string "not-a-version"`.

- Observation: Pest is now installed, configured, and currently passing with the scaffolded example tests.
  Evidence: `vendor/bin/pest` currently reports `2 passed (2 assertions)`.

- Observation: the repository now contains a standard Pest bootstrap using `tests/Pest.php`, `tests/TestCase.php`, and `phpunit.xml`.
  Evidence: `tests/` now contains `Feature/`, `Unit/`, `Pest.php`, and `TestCase.php`, and `phpunit.xml` includes the `tests` directory plus `bin` and `src` as source paths.

- Observation: `mago.toml` currently scans only `src/`, which means the executable in `bin/comsem` and any future test files would be skipped unless the configuration is expanded.
  Evidence: `mago.toml` currently contains `[source] paths = ["src/"]`.

- Observation: Symfony Console's built-in `Application` class handles `--version`, `--help`, default exception rendering, and default commands as text-first behaviors.
  Evidence: `vendor/symfony/console/Application.php` prints `getLongVersion()` for `--version`, reroutes `--help` to the built-in `help` command, and renders uncaught exceptions with formatted text blocks in `renderThrowable()`.

- Observation: the current installed `VersionParser` surface mixes static and instance methods. `parseStability()` and `normalizeStability()` are static, while `isValid()`, `normalize()`, `parseNumericAliasPrefix()`, `normalizeBranch()`, `normalizeDefaultBranch()`, and `parseConstraints()` are instance methods.
  Evidence: reflecting `Composer\Semver\VersionParser` in this repository prints `static parseStability`, `static normalizeStability`, `instance isValid`, `instance normalize`, `instance parseNumericAliasPrefix`, `instance normalizeBranch`, `instance normalizeDefaultBranch`, and `instance parseConstraints`.

- Observation: `VersionParser::parseStability()` is permissive and does not behave like a strict validator. Inputs like `dev-main` and even `not-a-version` still produce successful stability strings rather than runtime errors.
  Evidence: current local behavior checks show `parseStability('1.0.0-beta2') === 'beta'`, `parseStability('dev-main') === 'dev'`, and `parseStability('not-a-version') === 'stable'`.

- Observation: Mago 1.25.x does not include extensionless PHP executables like `bin/comsem` in `mago list-files`, even when the path is explicit and `extensions = ["php", ""]` is configured.
  Evidence: local investigation showed `mago list-files` skipped `bin/comsem` while `mago ast bin/comsem` could parse it, and moving the implementation into `bin/comsem.php` made the executable logic visible to Mago while preserving the thin wrapper entrypoint.

## Decision Log

- Decision: implement thirteen custom commands: the six documented `Comparator` wrappers, the four documented `Semver` wrappers, and the three explicitly user-requested `VersionParser` wrappers `parseStability`, `isValid`, and `normalize`.
  Rationale: the user expanded scope after the initial plan. `Intervals` remains excluded, and the parser commands are now explicit requirements.
  Date/Author: 2026-05-01 / OpenCode

- Decision: use Symfony command namespaces `comparator:*` and `semver:*`, with method names converted from camelCase to kebab-case.
  Rationale: this keeps command names predictable, matches Symfony Console conventions, and creates a stable one-to-one mapping from wrapped method to CLI command.
  Date/Author: 2026-05-01 / OpenCode

- Decision: add a third custom namespace, `parser:*`, for the requested `VersionParser` commands.
  Rationale: keeping parser-related commands under their own namespace mirrors the underlying library class and avoids overloading the `semver:*` namespace with a different responsibility.
  Date/Author: 2026-05-01 / OpenCode

- Decision: preserve Symfony default commands and options exactly as Symfony provides them.
  Rationale: the user explicitly exempted default features from the custom JSON and exit-code contract, so replacing them with a project-specific JSON layer would add risk without solving a requested problem.
  Date/Author: 2026-05-01 / OpenCode

- Decision: invokable commands in this project should depend on `OutputInterface` rather than `SymfonyStyle`.
  Rationale: the user explicitly wants low-level output handling for agentic JSON usage. `OutputInterface` keeps command signatures minimal and avoids presentation helpers that would conflict with the JSON-only custom-command contract.
  Date/Author: 2026-05-01 / OpenCode

- Decision: every custom command success payload uses the same JSON envelope: `{"ok":true,"command":"<name>","result":<json-value>}`.
  Rationale: a stable top-level shape makes shell and programmatic consumption consistent across all commands while still allowing scalar booleans and arrays.
  Date/Author: 2026-05-01 / OpenCode

- Decision: every custom command failure payload uses the same JSON envelope: `{"ok":false,"command":"<name>","error":{"category":"usage|runtime","class":"<debug type>","message":"<message>"}}`.
  Rationale: this captures the most useful machine-readable metadata with minimal complexity and avoids ad hoc per-command error formats.
  Date/Author: 2026-05-01 / OpenCode

- Decision: both success JSON and failure JSON go to standard output.
  Rationale: the user explicitly wants stdout-only JSON because it is the preferred contract for agentic consumers in this project.
  Date/Author: 2026-05-01 / OpenCode

- Decision: both success and failure payloads must be written through one shared `writeJson()` helper.
  Rationale: a single JSON write path avoids drift between success and failure formatting, keeps raw-output behavior consistent, and guarantees one serialization strategy for all custom-command outputs.
  Date/Author: 2026-05-01 / OpenCode

- Decision: implement a shared abstract `JsonCommand` base class for all custom commands, and make the custom `Application` subclass intercept exceptions only for that base class.
  Rationale: usage errors for valid custom commands happen inside Symfony input binding, which means application-level interception is required. Scoping the interception to the shared base class preserves normal Symfony behavior for built-in commands.
  Date/Author: 2026-05-01 / OpenCode

- Decision: keep using Symfony's invokable-command convention for this project and design the command classes around `__invoke()` plus Symfony attributes.
  Rationale: this matches the established project convention and the user explicitly approved the coupling to Symfony's invokable-command behavior.
  Date/Author: 2026-05-01 / OpenCode

- Decision: classify any throwable implementing `Symfony\Component\Console\Exception\ExceptionInterface` as a custom-command usage error and map it to `Command::INVALID`; classify every other throwable as a runtime error and map it to `Command::FAILURE`.
  Rationale: this cleanly matches Symfony's own command-input exception taxonomy without inventing fragile string parsing rules.
  Date/Author: 2026-05-01 / OpenCode

- Decision: use Symfony `Command` exit-code constants in the implementation and in test expectations instead of raw integers wherever code is being described.
  Rationale: this follows Symfony conventions and makes the intent of each return path clearer than bare `0`, `1`, and `2` values.
  Date/Author: 2026-05-01 / OpenCode

- Decision: return `Command::SUCCESS` when the wrapped library returns a normal boolean result of `false`.
  Rationale: command status reflects whether command execution succeeded, not whether the wrapped method's boolean result was truthy. A valid comparison that evaluates to `false`, or `VersionParser::isValid()` returning `false`, is still a successful command execution and should remain in the success JSON envelope.
  Date/Author: 2026-05-01 / OpenCode

- Decision: do not pre-validate comparator command inputs before calling the wrapped library.
  Rationale: the wrapper should preserve the documented API behavior. Research showed that comparator methods can return booleans even for malformed strings, and the CLI should expose that behavior instead of silently redefining it.
  Date/Author: 2026-05-01 / OpenCode

- Decision: follow `composer/semver` permissive behaviors for parser commands as well, and document them explicitly in tests and examples.
  Rationale: `parseStability()` is not a strict validator. The CLI should surface the library's actual behavior rather than rewriting it into a stricter contract than Composer itself provides.
  Date/Author: 2026-05-01 / OpenCode

- Decision: represent list inputs as repeated positional arguments, not comma-delimited strings and not JSON strings.
  Rationale: repeated positional arguments are idiomatic for CLIs, require no parsing convention invented by this project, and map directly onto Symfony's array argument support.
  Date/Author: 2026-05-01 / OpenCode

- Decision: make `semver:satisfied-by` accept CLI arguments as `<constraints> <versions>...` even though the wrapped PHP method signature is `satisfiedBy(array $versions, $constraints)`.
  Rationale: Symfony Console requires array positional arguments to come last. Reordering the CLI arguments is the smallest correct way to keep repeated versions ergonomic.
  Date/Author: 2026-05-01 / OpenCode

- Decision: add `Runner::buildApplication()` in addition to `Runner::run()`.
  Rationale: Pest feature tests need a fully configured application instance with `setAutoExit(false)`. A builder method avoids reflection and duplicate bootstrap code in tests.
  Date/Author: 2026-05-01 / OpenCode

- Decision: add `phpstan.neon`, include Pest's PHPStan extension files, and analyze `bin`, `src`, and `tests` together.
  Rationale: this repository is small, tests are first-class code, and the needed Pest PHPStan integration files already exist in `vendor/pestphp/pest/`.
  Date/Author: 2026-05-01 / OpenCode

- Decision: wrap `VersionParser` methods according to their real invocation style in the current project: call `parseStability()` statically, and call `normalize()` and `isValid()` through a constructed `VersionParser` instance.
  Rationale: the user explicitly clarified that `isValid()` is an instance method and asked for the plan wording to reflect that instead of using static `::` phrasing where it would be misleading.
  Date/Author: 2026-05-01 / OpenCode

- Decision: update the testing plan to build on the existing Pest scaffold instead of replacing it, keep CLI behavior tests under `tests/Feature`, and only edit `tests/Pest.php` or `tests/TestCase.php` when the implementation actually needs it.
  Rationale: the repository already has a valid Pest bootstrap and the user explicitly asked to keep CLI tests under `tests/Feature`. The smallest correct approach is to add tests in the requested locations and leave the global bootstrap alone unless a concrete need appears.
  Date/Author: 2026-05-01 / OpenCode

- Decision: do not add `phpunit`, `phpunit.xml`, or PHPUnit-style test classes.
  Rationale: the user explicitly prohibited `phpunit`, and Pest already provides the needed testing model.
  Date/Author: 2026-05-01 / OpenCode

- Decision: keep the initial implementation focused on command help text instead of adding a top-level `README.md` during the first delivery.
  Rationale: the repository currently has no CLI implementation at all. A working binary, accurate built-in help, and strong test coverage are more important than extra top-level prose in the first milestone.
  Date/Author: 2026-05-01 / OpenCode

## Outcomes & Retrospective

The repository now ships a working Symfony Console application in `bin/comsem` with thirteen custom JSON commands covering the planned `Comparator`, `Semver`, and selected `VersionParser` APIs. Custom commands emit exactly one compact JSON object to stdout on both success and failure, preserve Composer's permissive comparator and parser behaviors where required, and leave Symfony defaults such as `--help`, `--version`, `help`, `list`, and completion as normal text-based commands.

The implementation also includes end-to-end Pest coverage, PHPStan configuration, and Mago coverage for the executable bootstrap, source files, and tests. The one tooling surprise discovered during implementation was that Mago 1.25.x does not enumerate extensionless PHP entrypoints in `mago list-files`; the repository now works around that by keeping `bin/comsem` as the executable shell wrapper while placing the real PHP bootstrap logic in `bin/comsem.php`, which Mago can scan and format.

Validation is complete: smoke tests, Pest, PHPStan, `mago format --check`, `mago lint`, and `mago analyze` all pass. The only remaining non-project output during validation is informational invalid-UTF8 noise from vendor files included for analysis context by Mago.

## Context and Orientation

This repository is a new Symfony Console CLI project. The executable entrypoint is `bin/comsem`. It already loads Composer's autoloader and calls `TypistTech\ComSem\Runner::run()`, but `src/` is currently empty, so the binary crashes immediately.

The package autoload root in `composer.json` is `TypistTech\ComSem\` mapped to `src/`. The binary name is `comsem`. The package currently depends on PHP `^8.5`, `composer/semver`, `symfony/console`, `symfony/polyfill-iconv`, Pest, PHPStan, and PHPStan's Symfony-related extensions. Mago is configured through `mago.toml` and is already installed on the machine.

The term `subcommand` in this plan means a Symfony Console command name such as `semver:sort` or `comparator:greater-than`. Symfony Console expresses command hierarchies through colon-separated names, not through shell-style nested executables.

The term `usage error` means the user invoked a valid custom command incorrectly. Typical examples are omitting a required argument, passing too many arguments, or using an option that the custom command does not support. These are different from library-level runtime failures such as `UnexpectedValueException` from `composer/semver`.

The term `JSON envelope` means the one top-level JSON object emitted by a custom command. This plan uses one envelope shape for success and one for failure so every custom command is script-friendly in the same way.

The key repository files and directories relevant to this work are these:

- `bin/comsem`, the existing executable bootstrap.
- `composer.json`, which already declares the runtime and dev dependencies.
- `phpunit.xml`, which now exists and is part of the active Pest setup.
- `mago.toml`, which needs to be expanded to cover the real repository layout.
- `plans/`, which is currently empty and now contains this file.
- `src/`, which is currently empty and must gain all application and command code.
- `tests/Pest.php` and `tests/TestCase.php`, which now form the base test bootstrap.
- `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php`, which prove Pest is currently wired correctly.
- `vendor/composer/semver/README.md`, which defines the exact command scope.
- `vendor/composer/semver/src/Comparator.php`, `vendor/composer/semver/src/Semver.php`, and `vendor/composer/semver/src/VersionParser.php`, which define the wrapped runtime behavior.
- `vendor/symfony/console/Application.php`, which defines how Symfony handles `--help`, `--version`, default commands, and input-binding exceptions.
- `vendor/symfony/console/Output/OutputInterface.php`, which this project should use as the primary invokable command dependency.

The reference project at `~/Code/composer-semver` matters only for conventions. The conventions that should be copied are already stated here: thin `bin/` bootstrap, dedicated `Application`, dedicated `Runner`, attribute-based commands, strict types everywhere, and small command classes that delegate to the underlying library.

### In-Scope Command Surface

The custom commands that must exist at the end of implementation are exactly these:

- `comparator:greater-than <version1> <version2>` wraps `Composer\Semver\Comparator::greaterThan()`.
- `comparator:greater-than-or-equal-to <version1> <version2>` wraps `Composer\Semver\Comparator::greaterThanOrEqualTo()`.
- `comparator:less-than <version1> <version2>` wraps `Composer\Semver\Comparator::lessThan()`.
- `comparator:less-than-or-equal-to <version1> <version2>` wraps `Composer\Semver\Comparator::lessThanOrEqualTo()`.
- `comparator:equal-to <version1> <version2>` wraps `Composer\Semver\Comparator::equalTo()`.
- `comparator:not-equal-to <version1> <version2>` wraps `Composer\Semver\Comparator::notEqualTo()`.
- `semver:satisfies <version> <constraints>` wraps `Composer\Semver\Semver::satisfies()`.
- `semver:satisfied-by <constraints> <versions>...` wraps `Composer\Semver\Semver::satisfiedBy()`.
- `semver:sort <versions>...` wraps `Composer\Semver\Semver::sort()`.
- `semver:rsort <versions>...` wraps `Composer\Semver\Semver::rsort()`.
- `parser:parse-stability <version>` wraps `Composer\Semver\VersionParser::parseStability()`.
- `parser:is-valid <version>` wraps the `isValid()` instance method on `Composer\Semver\VersionParser`.
- `parser:normalize <version>` wraps the `normalize()` instance method on `Composer\Semver\VersionParser`.

Do not add wrappers for `Composer\Semver\Intervals`. Do not add wrappers for `Comparator::compare()` or for any other `VersionParser` methods such as `normalizeBranch()`, `normalizeDefaultBranch()`, `parseConstraints()`, `parseNumericAliasPrefix()`, or `normalizeStability()`.

Method-kind clarification for the current installed `VersionParser` in this repository:

- Static methods: `parseStability()`, `normalizeStability()`.
- Instance methods: `isValid()`, `normalize()`, `parseNumericAliasPrefix()`, `normalizeBranch()`, `normalizeDefaultBranch()`, `parseConstraints()`.

### Required JSON Contract For Custom Commands

Every successful custom command must emit one compact JSON object followed by a trailing newline on standard output. The required top-level shape is:

    {"ok":true,"command":"semver:satisfied-by","result":["1.0.0","1.2.0"]}

The `command` field must contain the registered Symfony command name. The `result` field must preserve the wrapped method's natural return type after JSON encoding. Comparator commands, `semver:satisfies`, and `parser:is-valid` therefore return JSON booleans. `semver:satisfied-by`, `semver:sort`, and `semver:rsort` return JSON arrays of strings in the exact order returned by `composer/semver`. `parser:parse-stability` and `parser:normalize` return JSON strings.

Every failed custom command must emit one compact JSON object followed by a trailing newline on standard output. The required top-level shape is:

    {"ok":false,"command":"semver:sort","error":{"category":"runtime","class":"UnexpectedValueException","message":"Invalid version string \"not-a-version\""}}

For usage failures on valid custom commands, the `category` value must be `usage` and the return code must be `Command::INVALID`. For all other custom-command failures, the `category` value must be `runtime` and the return code must be `Command::FAILURE`.

The commands must not emit pretty-printed JSON, ANSI formatting, banners, blank leading lines, or human-only prose around the JSON payload. Use `json_encode()` with `JSON_THROW_ON_ERROR` and `JSON_UNESCAPED_SLASHES`. Write JSON using raw output mode so Symfony's formatter does not interpret tag-like substrings such as `<info>` inside JSON strings. `writeJson()` must append exactly one trailing newline and nothing else. If a payload cannot be encoded, that is a programmer bug and should not be silently hidden.

### Default Symfony Behavior That Must Remain Available

The application must still expose Symfony's standard features. That includes top-level help, command-specific help, version output, the built-in `list` command, and shell completion. Those outputs are intentionally allowed to remain text-based and to follow Symfony's own exit-code behavior. This means the implementation must not globally replace `Application::renderThrowable()` or `Application::doRun()` with unconditional JSON behavior.

## Milestones

### Milestone 1: Boot A Real Application And Scope The JSON Layer Correctly

The first milestone makes `bin/comsem` stop fatalling and creates the single place where the custom JSON contract is enforced. At the end of this milestone, the application must boot, built-in Symfony commands must remain available, and custom commands must have a path for JSON failures even when Symfony throws during input binding before business logic completes.

The work in this milestone is to add `src/Application.php`, `src/Runner.php`, and the shared custom-command base class. The proof for this milestone is that `php bin/comsem --version` and `php bin/comsem list` work as normal Symfony text commands, while a deliberately incomplete custom command invocation such as `php bin/comsem semver:sort` returns JSON and `Command::INVALID` after the custom commands are registered.

### Milestone 2: Add The Comparator Command Family

The second milestone adds the six wrappers for the documented `Comparator` methods. At the end of this milestone, each comparator command must exist, must have built-in Symfony help text, must return a boolean in the common success JSON envelope, and must preserve Composer's surprising malformed-input behavior where the underlying comparator returns a boolean instead of throwing.

The proof for this milestone is that all six comparator commands return `{"ok":true,...}` payloads for ordinary comparisons and at least one malformed comparator input is captured by tests as a successful `false` result rather than being reclassified into a runtime error.

### Milestone 3: Add The Semver Command Family

The third milestone adds the four wrappers for the documented `Semver` methods. At the end of this milestone, repeated positional arguments must work for array inputs, invalid versions or constraints must become JSON runtime failures with `Command::FAILURE`, and the command help for `semver:satisfied-by` must explain why the CLI argument order differs from the wrapped PHP method signature.

The proof for this milestone is that `semver:satisfies`, `semver:satisfied-by`, `semver:sort`, and `semver:rsort` all return JSON success payloads on valid input, and malformed input to `Semver` commands returns JSON runtime errors instead of raw stack traces.

### Milestone 4: Add The Parser Command Family

The fourth milestone adds the three requested `VersionParser` wrappers. At the end of this milestone, `parser:parse-stability` must call the static parser helper, while `parser:normalize` and `parser:is-valid` must call the corresponding instance methods on a constructed `VersionParser` object.

The proof for this milestone is that `parser:parse-stability 1.0.0-beta2` returns `beta`, `parser:parse-stability dev-main` returns `dev`, `parser:parse-stability not-a-version` still returns a successful `stable` result, `parser:normalize v1.2.3` returns `1.2.3.0`, malformed input to `parser:normalize` returns a JSON runtime error, and `parser:is-valid` uses `$parser->isValid($version)` rather than static-style wording or custom reimplementation.

### Milestone 5: Add Tooling Coverage And End-To-End Verification

The fifth milestone turns the working command set into a maintainable project. At the end of this milestone, the repository must have a real Pest suite, `phpstan.neon`, an updated `mago.toml`, and a reproducible validation sequence that covers `bin/`, `src/`, and `tests/`.

The proof for this milestone is that `vendor/bin/pest`, `vendor/bin/phpstan analyse`, `mago format --check`, `mago lint`, and `mago analyze` all succeed from the repository root, and `mago list-files` proves that `bin/comsem`, the new `src/` files, and the new `tests/` files are all included.

## Plan of Work

Create `src/Application.php` as `final class Application extends Symfony\Component\Console\Application`. Use `declare(strict_types=1);`, the repository namespace `TypistTech\ComSem`, and modern PHP override markers such as `#[\Override]` on methods that override Symfony behavior. Override `getLongVersion()` to provide a plain-text version summary for Symfony defaults. Override `doRunCommand()` and wrap `parent::doRunCommand()` in `try/catch`. If the command is not an instance of the project's shared `JsonCommand` base class, rethrow the exception so Symfony's text behavior stays intact. If the command is an instance of `JsonCommand`, render the failure JSON there through the shared `writeJson()` path and return the mapped Symfony `Command::*` constant instead of rethrowing.

Create `src/Runner.php` as the single bootstrap and registration point. It must expose `public static function buildApplication(): Application` and `public static function run(): int`. `buildApplication()` must instantiate the custom `Application`, set the application name to `comsem`, derive the version from `Composer\InstalledVersions::getRootPackage()`, register all thirteen custom commands with `addCommands()`, and return the application. `run()` must call `buildApplication()->run()`. The existing `bin/comsem` file should remain as a thin wrapper that loads the autoloader and calls `Runner::run()`.

Create `src/Command/JsonCommand.php` as the shared abstract base class for every custom command. This class must extend `Symfony\Component\Console\Command\Command` and must centralize the JSON envelope, newline handling, JSON encoding, raw-output writes, and exit-code mapping. It must provide one success helper for subclasses and one failure-rendering method callable by `Application`. Keep this class small and free of business logic. It is infrastructure, not another layer of abstraction for its own sake. Keep the project on Symfony's invokable-command path: do not replace this design with explicit `execute()` implementations unless a concrete blocker appears.

Implementation shape requirement: `writeJson()` must be the only place in the custom-command stack that calls `json_encode()` and the only place that writes JSON to the console. `writeSuccess()` must build the success payload and delegate to `writeJson()`. `renderFailure()` must build the failure payload and delegate to `writeJson()`.

`JsonCommand` should also contain the shared `writeJson()` helper used by both command success paths and application-level failure handling. Both success and failure payloads must be routed to the provided standard output stream through this helper. All JSON writes from this helper should use `OutputInterface::OUTPUT_RAW` so formatter tags inside JSON strings are preserved literally. `writeJson()` must append exactly one trailing newline and nothing else.

Create the six comparator command files under `src/Command/Comparator/`. Each file should define one `final` class, use `#[AsCommand]`, include a description and a short help string naming the wrapped method, and expose an `__invoke()` method with `#[Argument]` attributes. The methods should accept `OutputInterface $output` plus the required strings and then call the exact static `Composer\Semver\Comparator` method. Do not normalize or pre-validate the inputs.

Create the four semver command files under `src/Command/Semver/` with the same overall style. `semver:satisfies` takes two string arguments. `semver:satisfied-by` takes a string constraint first and an array of versions second so the CLI remains ergonomic. `semver:sort` and `semver:rsort` each take a single array argument. These command classes should accept `OutputInterface $output`, call the wrapped static methods directly, and let exceptions bubble to the application-level JSON renderer.

Create the three parser command files under `src/Command/Parser/` with the same overall style. `parser:parse-stability` should accept `OutputInterface $output`, call the static `Composer\Semver\VersionParser::parseStability()` method, and return its string result. `parser:normalize` should accept `OutputInterface $output`, instantiate `Composer\Semver\VersionParser`, and call `$parser->normalize($version)` because the library method is instance-based. `parser:is-valid` should also accept `OutputInterface $output`, instantiate `VersionParser`, and call `$parser->isValid($version)` because that API is also instance-based. Describe these two commands as wrappers around instance methods, not as wrappers around static `::` calls.

Add built-in help quality to every command through the `#[AsCommand]` attribute. Each command should include a description in plain English and at least one usage example via the `usages` attribute. The help text should mirror the reference project's style by explicitly stating which `composer/semver` method is being wrapped.

Use the existing `tests/Pest.php` and `tests/TestCase.php` as the starting point instead of replacing them. Keep CLI behavior tests under `tests/Feature`. Unit-level helpers or pure-unit coverage may live under `tests/Unit`. Only edit `tests/Pest.php` or `tests/TestCase.php` when the implementation actually needs it. If a different unit-test base class becomes necessary, add a separate unit `TestCase` and bind it explicitly instead of broadly rewriting the existing bootstrap. If Pest helpers are useful, create `tests/Support/cli.php` and have `tests/Pest.php` require it only if needed. `tests/Support/cli.php` should define one small helper function, for example `runComsem(array $input): array`, that builds the application via `Runner::buildApplication()`, disables auto-exit, runs it through `Symfony\Component\Console\Tester\ApplicationTester`, and returns a structured array containing `status`, `stdout`, and decoded JSON when present.

Replace or delete the scaffold example tests once real coverage exists. Create `tests/Feature/ComparatorCommandsTest.php` to cover all six comparator commands, including a dataset-driven happy path and at least one malformed-version case proving that comparator behavior is preserved. Create `tests/Feature/SemverCommandsTest.php` to cover all four semver commands, successful filtering and sorting, runtime failures from malformed versions or constraints, and usage failures from missing array arguments. Create `tests/Feature/ParserCommandsTest.php` to cover `parser:parse-stability`, `parser:is-valid`, and `parser:normalize`, including both success and failure behavior and explicitly documenting permissive parser behavior such as `parseStability('dev-main') === 'dev'` and `parseStability('not-a-version') === 'stable'`. Create `tests/Feature/DefaultSymfonyBehaviorTest.php` to prove that `--help`, `help comparator:greater-than`, `list`, and `--version` still behave like Symfony defaults instead of being forced into the custom JSON envelope.

Testing requirements for the shared writer:

- Assert both decoded JSON and raw stdout contents for custom commands.
- Assert that stdout contains exactly one JSON object plus exactly one trailing newline, with no leading whitespace, no extra blank lines, and no trailing prose.
- Add at least one test where a JSON string value contains tag-like content such as `<info>` so `OutputInterface::OUTPUT_RAW` is proven to preserve payload integrity.
- For custom-command failures, assert the JSON envelope and the returned `Command::*` constant from the application run result.

Create `phpstan.neon` in the repository root. Keep it minimal like the reference project, but include Pest's PHPStan extension files from `vendor/pestphp/pest/extension.neon` and `vendor/pestphp/pest/phpstan-pest-extension.neon`. The `paths` section must include `bin`, `src`, and `tests`.

Update `mago.toml` so the `[source] paths` array becomes `['bin/', 'src/', 'tests/']`. Keep the existing `symfony` and `pest` integrations. After the config change, `mago list-files` must show `bin/comsem`, the new source files, and the new tests.

Do not add `phpunit` or new PHPUnit configuration. Do not add a service container. Do not introduce dependency injection for the wrapped library because the wrapped APIs are either static helpers or short-lived `VersionParser` instance calls, and a container would add complexity without benefit. Do not replace the existing `bin/comsem` entrypoint structure because it already matches the reference project's bootstrap pattern.

## Concrete Steps

1. From project root, create the new PHP source files and test files named in `Plan of Work`, then run `composer dump-autoload`.

   Expected result: Composer regenerates autoload metadata without errors.

2. From project root, run `php bin/comsem --version`.

   Expected result: the command exits successfully and prints plain text containing `comsem`, the root package version such as `dev-main`, the installed `composer/semver` version, the installed `symfony/console` version, and the active PHP version.

3. From project root, run the six comparator smoke tests:

       php bin/comsem comparator:greater-than 1.25.0 1.24.0
       php bin/comsem comparator:greater-than-or-equal-to 1.25.0 1.25.0
       php bin/comsem comparator:less-than 1.24.0 1.25.0
       php bin/comsem comparator:less-than-or-equal-to 1.24.0 1.24.0
       php bin/comsem comparator:equal-to 1.24.0 1.24.0
       php bin/comsem comparator:not-equal-to 1.24.0 1.25.0

   Expected result: all six commands return `Command::SUCCESS` and print one JSON object whose `result` value is `true`.

4. From project root, run the semver success smoke tests:

       php bin/comsem semver:satisfies 1.2.3 '^1.0'
       php bin/comsem semver:satisfied-by '^1.0' 1.0.0 1.2.0 2.0.0
       php bin/comsem semver:sort 1.0.0-beta 1.0.0 2.0.0
       php bin/comsem semver:rsort 1.0.0-beta 1.0.0 2.0.0

   Expected result: all four commands return `Command::SUCCESS`. `semver:satisfies` returns `true`. `semver:satisfied-by` returns `['1.0.0', '1.2.0']`. `semver:sort` returns `['1.0.0-beta', '1.0.0', '2.0.0']`. `semver:rsort` returns `['2.0.0', '1.0.0', '1.0.0-beta']`.

5. From project root, run the parser smoke tests:

       php bin/comsem parser:parse-stability 1.0.0-beta2
       php bin/comsem parser:parse-stability dev-main
       php bin/comsem parser:parse-stability not-a-version
       php bin/comsem parser:normalize v1.2.3
       php bin/comsem parser:normalize not-a-version
       echo $?
       php bin/comsem parser:is-valid 1.0.0

   Expected result: `parser:parse-stability 1.0.0-beta2` returns `Command::SUCCESS` and returns `beta`. `parser:parse-stability dev-main` returns `Command::SUCCESS` and returns `dev`. `parser:parse-stability not-a-version` also returns `Command::SUCCESS` and returns `stable`, documenting Composer's permissive behavior. `parser:normalize` returns `Command::SUCCESS` for `v1.2.3` and returns `1.2.3.0`. `parser:normalize not-a-version` prints a JSON runtime error on standard output and `echo $?` prints the numeric value for `Command::FAILURE`. `parser:is-valid 1.0.0` returns `Command::SUCCESS` and returns a JSON boolean result produced by `$parser->isValid('1.0.0')`.

6. From project root, run the custom-command usage-error smoke test:

       php bin/comsem semver:sort
       echo $?

   Expected result: the first command prints one JSON error object to standard output. The second command prints the numeric value for `Command::INVALID`.

7. From project root, run the custom-command runtime-error smoke tests:

       php bin/comsem semver:sort 1.0.0 not-a-version
       echo $?
       php bin/comsem semver:satisfies not-a-version '^1.0'
       echo $?

   Expected result: each CLI invocation prints one JSON error object to standard output with `category` equal to `runtime`, and each `echo $?` prints the numeric value for `Command::FAILURE`.

8. From project root, run the comparator malformed-input preservation test:

       php bin/comsem comparator:greater-than not-a-version 1.0.0
       echo $?

   Expected result: the command still returns a JSON success payload with `result` equal to `false`, and the exit code remains `0`. This proves the wrapper preserved native `Comparator` behavior.

9. From project root, run the default-Symfony smoke tests:

       php bin/comsem --help
       php bin/comsem help comparator:greater-than
       php bin/comsem list
       php bin/comsem completion --help

   Expected result: these commands produce normal Symfony help or listing text instead of the custom JSON envelope.

10. From project root, run `mago list-files`.

   Expected result: the output includes at least `bin/comsem`, the new files under `src/`, and the new files under `tests/`. If `bin/comsem` is missing, fix `mago.toml` before trusting the later Mago checks.

11. From project root, run `vendor/bin/pest`.

    Expected result: all feature tests pass. The suite must cover success JSON, usage JSON, runtime JSON, and the non-JSON Symfony default behaviors.

12. From project root, run `vendor/bin/phpstan analyse`.

    Expected result: PHPStan finishes successfully at max level across `bin`, `src`, and `tests`.

13. From project root, run the Mago validation sequence:

        mago format --check
        mago lint
        mago analyze

    Expected result: all three commands succeed. `mago format --check` must report no formatting drift. `mago lint` and `mago analyze` must report no remaining issues at the configured severity threshold.

## Validation and Acceptance

Acceptance is behavioral. The change is complete only when a human can observe all of the following from the repository root.

- `php bin/comsem` is now a working Symfony Console application instead of crashing because `Runner` is missing.
- The thirteen custom commands listed in `In-Scope Command Surface` exist and are discoverable through `php bin/comsem list` and `php bin/comsem help <command>`.
- Every successful custom command emits exactly one JSON object on standard output and returns `Command::SUCCESS`.
- Every invalid invocation of a valid custom command emits exactly one JSON error object on standard output and returns `Command::INVALID`.
- Every non-usage custom-command failure emits exactly one JSON error object on standard output and returns `Command::FAILURE`.
- At least one comparator malformed-input example is verified to return a successful boolean payload, proving the wrapper preserves native comparator behavior.
- At least one semver malformed-input example is verified to return a JSON runtime failure, proving the wrapper preserves native semver exception behavior.
- At least one parser stability example and one parser normalization example are verified against the underlying `VersionParser` behavior.
- At least one permissive parser example is verified, proving the CLI preserves Composer behavior instead of turning `parseStability()` into a validator.
- `parser:is-valid` is implemented as a wrapper around a `VersionParser` instance method call, not described or implemented as a static helper.
- Default Symfony features such as `--help`, `--version`, `help`, `list`, and shell completion still work and remain outside the custom JSON contract.
- Pest, PHPStan, Mago format checking, Mago linting, and Mago analysis all pass after the implementation is complete.

The test suite should make these behaviors explicit. The most important tests are not line-coverage tests. They are end-to-end command tests that assert the returned `Command::*` status, standard-output content, exact newline behavior, and decoded JSON structure.

## Idempotence and Recovery

This work is safe to repeat because it is additive. The repository has no database, no migrations, and no external service state to mutate.

If a partial implementation leaves `bin/comsem` booting but commands missing, rerun `composer dump-autoload` and then confirm that `Runner::buildApplication()` registers all thirteen commands. A missing registration should produce `Command "..." is not defined.` from Symfony, which is a signal to fix command registration rather than to edit the bootstrap.

If a custom command emits text instead of JSON, check two things first. Verify that the command extends the shared `JsonCommand` base class. Then verify that `Application::doRunCommand()` only intercepts throwables for instances of `JsonCommand`. If either link is broken, Symfony's default text rendering will leak through.

If success and failure JSON are not both going through the same serialization path, inspect the shared `writeJson()` helper first. This project requires both success and failure payloads to be emitted on standard output through the same raw-output JSON writer.

If a custom command writes more than one line, adds leading whitespace, or omits the trailing newline, inspect `writeJson()` next. The contract for custom commands is one JSON object plus exactly one trailing newline and nothing else.

If JSON output loses substrings that look like Symfony formatter tags such as `<info>`, inspect the raw-output setting next. JSON payloads must be written with `OutputInterface::OUTPUT_RAW` so formatter parsing does not rewrite valid JSON string content.

If `mago format --check`, `mago lint`, or `mago analyze` appear to ignore the executable bootstrap or the new tests, run `mago list-files` immediately. In Mago 1.25.x, extensionless PHP entrypoints are not enumerated, so this repository keeps the executable wrapper in `bin/comsem` and the scanable bootstrap logic in `bin/comsem.php`.

If Pest cannot find the suite, confirm that `tests/Pest.php`, `tests/TestCase.php`, and `phpunit.xml` all still exist and that the `tests/` directory is present. If PHPStan reports unknown Pest helpers in tests, confirm that `phpstan.neon` includes both `vendor/pestphp/pest/extension.neon` and `vendor/pestphp/pest/phpstan-pest-extension.neon`.

No rollback strategy should involve destructive Git commands. The safe retry path is to edit the named files, rerun `composer dump-autoload`, rerun the targeted smoke tests, and then rerun the full validation sequence.

## Artifacts and Notes

Historical pre-implementation failure state:

    $ php bin/comsem --help
    PHP Fatal error:  Uncaught Error: Class "TypistTech\ComSem\Runner" not found in ~/Code/comsem/bin/comsem:21

Representative success transcript after implementation:

    $ php bin/comsem semver:satisfied-by '^1.0' 1.0.0 1.2.0 2.0.0
    {"ok":true,"command":"semver:satisfied-by","result":["1.0.0","1.2.0"]}
    $ echo $?
    <numeric value of Command::SUCCESS>

Representative parser success transcript after implementation:

    $ php bin/comsem parser:parse-stability 1.0.0-beta2
    {"ok":true,"command":"parser:parse-stability","result":"beta"}
    $ echo $?
    <numeric value of Command::SUCCESS>

Representative permissive parser transcript after implementation:

    $ php bin/comsem parser:parse-stability not-a-version
    {"ok":true,"command":"parser:parse-stability","result":"stable"}
    $ echo $?
    <numeric value of Command::SUCCESS>

Representative usage-failure transcript after implementation:

    $ php bin/comsem semver:sort
    {"ok":false,"command":"semver:sort","error":{"category":"usage","class":"Symfony\Component\Console\Exception\RuntimeException","message":"Not enough arguments (missing: \"versions\")."}}
    $ echo $?
    <numeric value of Command::INVALID>

Representative runtime-failure transcript after implementation:

    $ php bin/comsem semver:sort 1.0.0 not-a-version
    {"ok":false,"command":"semver:sort","error":{"category":"runtime","class":"UnexpectedValueException","message":"Invalid version string \"not-a-version\""}}
    $ echo $?
    <numeric value of Command::FAILURE>

Representative parser runtime-failure transcript after implementation:

    $ php bin/comsem parser:normalize not-a-version
    {"ok":false,"command":"parser:normalize","error":{"category":"runtime","class":"UnexpectedValueException","message":"Invalid version string \"not-a-version\""}}
    $ echo $?
    <numeric value of Command::FAILURE>

Representative preserved-comparator-behavior transcript after implementation:

    $ php bin/comsem comparator:greater-than not-a-version 1.0.0
    {"ok":true,"command":"comparator:greater-than","result":false}
    $ echo $?
    <numeric value of Command::SUCCESS>

## Interfaces and Dependencies

The implementation must end with these concrete interfaces and file paths.

`src/Application.php` must define:

    final class Application extends SymfonyConsoleApplication
    {
        #[\Override]
        public function getLongVersion(): string;

        #[\Override]
        protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int;
    }

`src/Runner.php` must define:

    final class Runner
    {
        private const string NAME = 'comsem';

        public static function buildApplication(): Application;

        public static function run(): int;
    }

`src/Command/JsonCommand.php` must define an abstract base command with at least these responsibilities:

    abstract class JsonCommand extends Command
    {
        final protected function writeSuccess(OutputInterface $output, mixed $result): int;

        final public function renderFailure(Throwable $throwable, OutputInterface $output): int;

        final protected static function isUsageThrowable(Throwable $throwable): bool;

        final protected function writeJson(OutputInterface $output, array $payload): void;
    }

`writeSuccess()` and `renderFailure()` should both return Symfony `Command::*` constants after delegating the actual output write to `writeJson()`.

The thirteen command files under `src/Command/Comparator/`, `src/Command/Semver/`, and `src/Command/Parser/` must each define one `final` class that extends `JsonCommand`, uses `#[AsCommand]`, and implements command logic through an `__invoke()` method with `#[Argument]` attributes and an `OutputInterface $output` parameter. No command in this initial implementation needs custom options, so `#[Option]` is not required.

`tests/Support/cli.php`, if added, must define one helper function that builds the application, disables auto-exit, runs it through `ApplicationTester`, and returns captured `status` and `stdout` so tests can assert the shared stdout-only JSON contract. It must integrate with the already existing `tests/Pest.php` bootstrap instead of replacing it.

`phpstan.neon` must include:

    includes:
        - vendor/pestphp/pest/extension.neon
        - vendor/pestphp/pest/phpstan-pest-extension.neon

    parameters:
        level: max
        paths:
            - bin
            - src
            - tests

`mago.toml` must end with `[source] paths` that include `bin/`, `src/`, and `tests/`.

The only external runtime libraries that should be used directly by the implementation are `composer/semver`, `symfony/console`, and `composer-runtime-api` metadata through `Composer\InstalledVersions`. There is no need for a dependency injection container, no need for service definitions, and no need for any framework layer beyond raw Symfony Console.

## Plan Revision Note

2026-05-01 / OpenCode: Initial ExecPlan created after inspecting the current repository, the reference project, the `composer/semver` README and source behavior, Symfony Console's exception and default-command flow, Pest availability, PHPStan Pest integration files, and the existing Mago configuration. The purpose of this revision was to turn an empty implementation space into a complete, self-contained execution plan without starting implementation.

2026-05-01 / OpenCode: Revised the ExecPlan after the repository gained a working Pest scaffold and the requested scope expanded to three `VersionParser` commands. This revision also updates the wording so `parseStability()` is described as a static helper while `normalize()` and `isValid()` are described as instance-method wrappers using a constructed `VersionParser` object.

2026-05-01 / OpenCode: Final pre-implementation revision after the dependency was updated and the latest design constraints were clarified. This revision records the current `VersionParser` static-versus-instance method split from the installed dependency, switches command signatures back to `OutputInterface` per user direction, routes both success and failure JSON to stdout through one shared `writeJson()` helper, requires `writeJson()` to be the only JSON serializer/output path and to append exactly one trailing newline, uses Symfony `Command::*` constants as the implementation-level exit-code convention, requires raw output mode for JSON writes so Symfony formatter tags cannot corrupt payloads, documents Composer's permissive parser behaviors as required acceptance criteria, and tightens the Pest testing guidance to keep CLI tests under `tests/Feature` while leaving the existing bootstrap mostly untouched.

2026-05-01 / OpenCode: Implementation-complete revision. This revision marks the execution items as done, records the passing validation sequence, documents the Mago 1.25.x limitation around extensionless PHP entrypoints, and notes the repository workaround of keeping `bin/comsem` as a thin executable wrapper while moving the scanable PHP bootstrap into `bin/comsem.php`.
