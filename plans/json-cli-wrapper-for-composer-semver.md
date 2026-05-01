# Build The Initial JSON `comsem` CLI Around Documented `composer/semver` APIs

This ExecPlan is a living document. The sections `Progress`, `Surprises & Discoveries`, `Decision Log`, and `Outcomes & Retrospective` must be kept up to date as work proceeds.

This document was authored against the ExecPlan requirements in `/Users/work/.agent/PLANS.md`. That file is outside this repository, so every assumption needed to finish the work is restated here instead of being left implicit.

## Purpose / Big Picture

After this change, the repository will ship a working Symfony Console CLI in `bin/comsem` that wraps the documented `composer/semver` APIs with explicit subcommands. A user will be able to compare versions, test whether versions satisfy constraints, filter version lists by a constraint, and sort version lists exactly the way Composer does.

The most important user-visible behavior is that every custom command added by this project emits exactly one JSON object, uses standard output for successful results, uses standard error for failures, returns exit code `0` on success, returns exit code `2` for invalid usage of a valid custom command, and returns exit code `1` for all other custom-command failures. The user explicitly clarified that Symfony Console defaults such as `--help`, `--version`, `help`, `list`, and shell completion are exempt from that JSON and exit-code contract and may remain standard Symfony text commands.

The final implementation must make these examples work from the repository root at `/Users/work/Code/comsem`:

    php bin/comsem comparator:greater-than 1.25.0 1.24.0
    php bin/comsem semver:satisfies 1.2.3 '^1.0'
    php bin/comsem semver:satisfied-by '^1.0' 1.0.0 1.2.0 2.0.0
    php bin/comsem semver:sort 1.0.0 not-a-version
    php bin/comsem semver:sort

The first three commands must exit `0` and print JSON success payloads. The fourth command must exit `1` and print a JSON runtime error because `Composer\Semver\Semver::sort()` rejects malformed versions. The fifth command must exit `2` and print a JSON usage error because the user invoked a valid custom command without the required array argument.

## Progress

- [x] (2026-05-01 00:57Z) Read `/Users/work/.agent/PLANS.md` and extracted the requirements that matter here: self-contained context, observable acceptance criteria, exact commands, required living-document sections, and a revision note.
- [x] (2026-05-01 00:57Z) Inspected the current repository and confirmed the starting point: `bin/comsem` exists, `src/` is empty, `plans/` is empty, `composer.json` already requires `composer/semver`, `symfony/console`, Pest, PHPStan, and Mago, and `mago.toml` currently scans only `src/`.
- [x] (2026-05-01 00:57Z) Inspected the reference project at `/Users/work/Code/composer-semver` and captured the reusable patterns: keep `bin/*` as a thin bootstrap, keep a dedicated `Runner` class, keep a dedicated `Application` class, and define commands with Symfony attributes on `__invoke()` methods.
- [x] (2026-05-01 00:57Z) Enumerated the exact in-scope API surface from `vendor/composer/semver/README.md`: six documented `Composer\Semver\Comparator` methods and four documented `Composer\Semver\Semver` methods. Confirmed that `Composer\Semver\Intervals` methods are intentionally out of scope.
- [x] (2026-05-01 00:57Z) Resolved the output-contract boundary after the user clarification: only custom commands added by this project must follow the JSON and exit-code rules; Symfony defaults remain untouched.
- [x] (2026-05-01 00:57Z) Authored this ExecPlan under `plans/json-cli-wrapper-for-composer-semver.md` without starting implementation.
- [ ] Implement `src/Application.php` and `src/Runner.php` so `bin/comsem` stops fatalling and the application can be constructed in tests without auto-exit.
- [ ] Implement the shared JSON command base class and the ten custom command classes under `src/Command/Comparator/` and `src/Command/Semver/`.
- [ ] Add Pest coverage, test helpers, and static-analysis configuration so `bin/`, `src/`, and `tests/` are all exercised.
- [ ] Update `mago.toml` so Mago covers `bin/`, `src/`, and `tests/` instead of only `src/`.
- [ ] Run the full validation sequence described in this plan and record the results in this section when implementation begins.

## Surprises & Discoveries

- Observation: the repository does not yet contain any PHP source files under `src/`, so the existing executable cannot run at all.
  Evidence: `php bin/comsem --help` currently fails with `Fatal error: Uncaught Error: Class "TypistTech\ComSem\Runner" not found in /Users/work/Code/comsem/bin/comsem:21`.

- Observation: `Composer\Semver\Comparator` does not validate malformed version strings in the same way the `Semver` class does. At least for `greaterThan`, malformed input still produces a boolean result instead of an exception.
  Evidence: `php -r 'require "vendor/autoload.php"; var_dump(\Composer\Semver\Comparator::greaterThan("not-a-version", "1.0.0"));'` printed `bool(false)`.

- Observation: `Composer\Semver\Semver` methods do validate malformed versions and throw `UnexpectedValueException`.
  Evidence: `php -r 'require "vendor/autoload.php"; try { var_dump(\Composer\Semver\Semver::sort(["1.0.0", "not-a-version"])); } catch (Throwable $e) { fwrite(STDERR, get_class($e).": ".$e->getMessage().PHP_EOL); exit(1); }'` printed `UnexpectedValueException: Invalid version string "not-a-version"`.

- Observation: Pest is installed but unusable until a real `tests/` tree exists.
  Evidence: `vendor/bin/pest --version` currently aborts with `The test directory [/Users/work/Code/comsem/tests/] does not exist.`

- Observation: `mago.toml` currently scans only `src/`, which means the executable in `bin/comsem` and any future test files would be skipped unless the configuration is expanded.
  Evidence: `mago.toml` currently contains `[source] paths = ["src/"]`.

- Observation: Symfony Console's built-in `Application` class handles `--version`, `--help`, default exception rendering, and default commands as text-first behaviors.
  Evidence: `vendor/symfony/console/Application.php` prints `getLongVersion()` for `--version`, reroutes `--help` to the built-in `help` command, and renders uncaught exceptions with formatted text blocks in `renderThrowable()`.

## Decision Log

- Decision: implement exactly ten custom commands, one for each documented `Comparator` and `Semver` method named in `vendor/composer/semver/README.md`, and nothing else.
  Rationale: the user scope is explicit, and adding wrappers for other public APIs such as `Comparator::compare()` or `VersionParser` would create extra surface area the user did not request.
  Date/Author: 2026-05-01 / OpenCode

- Decision: use Symfony command namespaces `comparator:*` and `semver:*`, with method names converted from camelCase to kebab-case.
  Rationale: this keeps command names predictable, matches Symfony Console conventions, and creates a stable one-to-one mapping from wrapped method to CLI command.
  Date/Author: 2026-05-01 / OpenCode

- Decision: preserve Symfony default commands and options exactly as Symfony provides them.
  Rationale: the user explicitly exempted default features from the custom JSON and exit-code contract, so replacing them with a project-specific JSON layer would add risk without solving a requested problem.
  Date/Author: 2026-05-01 / OpenCode

- Decision: every custom command success payload uses the same JSON envelope: `{"ok":true,"command":"<name>","result":<json-value>}`.
  Rationale: a stable top-level shape makes shell and programmatic consumption consistent across all commands while still allowing scalar booleans and arrays.
  Date/Author: 2026-05-01 / OpenCode

- Decision: every custom command failure payload uses the same JSON envelope: `{"ok":false,"command":"<name>","error":{"category":"usage|runtime","class":"<debug type>","message":"<message>"}}`.
  Rationale: this captures the most useful machine-readable metadata with minimal complexity and avoids ad hoc per-command error formats.
  Date/Author: 2026-05-01 / OpenCode

- Decision: successful JSON goes to standard output and failure JSON goes to standard error.
  Rationale: this follows normal CLI conventions and aligns with `clig.dev` guidance for script-friendly tools.
  Date/Author: 2026-05-01 / OpenCode

- Decision: implement a shared abstract `JsonCommand` base class for all custom commands, and make the custom `Application` subclass intercept exceptions only for that base class.
  Rationale: usage errors for valid custom commands happen inside Symfony input binding, which means application-level interception is required. Scoping the interception to the shared base class preserves normal Symfony behavior for built-in commands.
  Date/Author: 2026-05-01 / OpenCode

- Decision: classify any throwable implementing `Symfony\Component\Console\Exception\ExceptionInterface` as a custom-command usage error and map it to exit code `2`; classify every other throwable as a runtime error and map it to exit code `1`.
  Rationale: this cleanly matches Symfony's own command-input exception taxonomy without inventing fragile string parsing rules.
  Date/Author: 2026-05-01 / OpenCode

- Decision: do not pre-validate comparator command inputs before calling the wrapped library.
  Rationale: the wrapper should preserve the documented API behavior. Research showed that comparator methods can return booleans even for malformed strings, and the CLI should expose that behavior instead of silently redefining it.
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

- Decision: do not add `phpunit`, `phpunit.xml`, or PHPUnit-style test classes.
  Rationale: the user explicitly prohibited `phpunit`, and Pest already provides the needed testing model.
  Date/Author: 2026-05-01 / OpenCode

- Decision: keep the initial implementation focused on command help text instead of adding a top-level `README.md` during the first delivery.
  Rationale: the repository currently has no CLI implementation at all. A working binary, accurate built-in help, and strong test coverage are more important than extra top-level prose in the first milestone.
  Date/Author: 2026-05-01 / OpenCode

## Outcomes & Retrospective

At plan-authoring time, the repository is still intentionally unimplemented. The important outcome so far is that the scope is no longer ambiguous. The command surface is fully enumerated, the JSON envelope is fixed, the exit-code contract is fixed, the boundary between custom commands and default Symfony features is fixed, and the behavior differences between `Comparator` and `Semver` have already been researched.

No implementation work has started yet. The next contributor should be able to follow this file from top to bottom, create the named files, run the named commands, and arrive at a working CLI without needing to inspect prior chats or reverse-engineer the intent.

## Context and Orientation

This repository is a new Symfony Console CLI project. The executable entrypoint is `bin/comsem`. It already loads Composer's autoloader and calls `TypistTech\ComSem\Runner::run()`, but `src/` is currently empty, so the binary crashes immediately.

The package autoload root in `composer.json` is `TypistTech\ComSem\` mapped to `src/`. The binary name is `comsem`. The package currently depends on PHP `^8.5`, `composer/semver`, `symfony/console`, `symfony/polyfill-iconv`, Pest, PHPStan, and PHPStan's Symfony-related extensions. Mago is configured through `mago.toml` and is already installed on the machine.

The term `subcommand` in this plan means a Symfony Console command name such as `semver:sort` or `comparator:greater-than`. Symfony Console expresses command hierarchies through colon-separated names, not through shell-style nested executables.

The term `usage error` means the user invoked a valid custom command incorrectly. Typical examples are omitting a required argument, passing too many arguments, or using an option that the custom command does not support. These are different from library-level runtime failures such as `UnexpectedValueException` from `composer/semver`.

The term `JSON envelope` means the one top-level JSON object emitted by a custom command. This plan uses one envelope shape for success and one for failure so every custom command is script-friendly in the same way.

The key repository files and directories relevant to this work are these:

- `bin/comsem`, the existing executable bootstrap.
- `composer.json`, which already declares the runtime and dev dependencies.
- `mago.toml`, which needs to be expanded to cover the real repository layout.
- `plans/`, which is currently empty and now contains this file.
- `src/`, which is currently empty and must gain all application and command code.
- `vendor/composer/semver/README.md`, which defines the exact command scope.
- `vendor/composer/semver/src/Comparator.php` and `vendor/composer/semver/src/Semver.php`, which define the wrapped runtime behavior.
- `vendor/symfony/console/Application.php`, which defines how Symfony handles `--help`, `--version`, default commands, and input-binding exceptions.

The reference project at `/Users/work/Code/composer-semver` matters only for conventions. The conventions that should be copied are already stated here: thin `bin/` bootstrap, dedicated `Application`, dedicated `Runner`, attribute-based commands, strict types everywhere, and small command classes that delegate to the underlying library.

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

Do not add wrappers for `Composer\Semver\Intervals`. Do not add wrappers for `Comparator::compare()` or any `VersionParser` methods, because the user asked for the public methods mentioned in the README and explicitly excluded `Intervals`.

### Required JSON Contract For Custom Commands

Every successful custom command must emit one compact JSON object followed by a trailing newline on standard output. The required top-level shape is:

    {"ok":true,"command":"semver:satisfied-by","result":["1.0.0","1.2.0"]}

The `command` field must contain the registered Symfony command name. The `result` field must preserve the wrapped method's natural return type after JSON encoding. Comparator commands and `semver:satisfies` therefore return JSON booleans. `semver:satisfied-by`, `semver:sort`, and `semver:rsort` return JSON arrays of strings in the exact order returned by `composer/semver`.

Every failed custom command must emit one compact JSON object followed by a trailing newline on standard error. The required top-level shape is:

    {"ok":false,"command":"semver:sort","error":{"category":"runtime","class":"UnexpectedValueException","message":"Invalid version string \"not-a-version\""}}

For usage failures on valid custom commands, the `category` value must be `usage` and the exit code must be `2`. For all other custom-command failures, the `category` value must be `runtime` and the exit code must be `1`.

The commands must not emit pretty-printed JSON, ANSI formatting, banners, blank leading lines, or human-only prose around the JSON payload. Use `json_encode()` with `JSON_THROW_ON_ERROR` and `JSON_UNESCAPED_SLASHES`. If a payload cannot be encoded, that is a programmer bug and should not be silently hidden.

### Default Symfony Behavior That Must Remain Available

The application must still expose Symfony's standard features. That includes top-level help, command-specific help, version output, the built-in `list` command, and shell completion. Those outputs are intentionally allowed to remain text-based and to follow Symfony's own exit-code behavior. This means the implementation must not globally replace `Application::renderThrowable()` or `Application::doRun()` with unconditional JSON behavior.

## Milestones

### Milestone 1: Boot A Real Application And Scope The JSON Layer Correctly

The first milestone makes `bin/comsem` stop fatalling and creates the single place where the custom JSON contract is enforced. At the end of this milestone, the application must boot, built-in Symfony commands must remain available, and custom commands must have a path for JSON failures even when Symfony throws during input binding before business logic completes.

The work in this milestone is to add `src/Application.php`, `src/Runner.php`, and the shared custom-command base class. The proof for this milestone is that `php bin/comsem --version` and `php bin/comsem list` work as normal Symfony text commands, while a deliberately incomplete custom command invocation such as `php bin/comsem semver:sort` returns JSON and exit code `2` after the custom commands are registered.

### Milestone 2: Add The Comparator Command Family

The second milestone adds the six wrappers for the documented `Comparator` methods. At the end of this milestone, each comparator command must exist, must have built-in Symfony help text, must return a boolean in the common success JSON envelope, and must preserve Composer's surprising malformed-input behavior where the underlying comparator returns a boolean instead of throwing.

The proof for this milestone is that all six comparator commands return `{"ok":true,...}` payloads for ordinary comparisons and at least one malformed comparator input is captured by tests as a successful `false` result rather than being reclassified into a runtime error.

### Milestone 3: Add The Semver Command Family

The third milestone adds the four wrappers for the documented `Semver` methods. At the end of this milestone, repeated positional arguments must work for array inputs, invalid versions or constraints must become JSON runtime failures with exit code `1`, and the command help for `semver:satisfied-by` must explain why the CLI argument order differs from the wrapped PHP method signature.

The proof for this milestone is that `semver:satisfies`, `semver:satisfied-by`, `semver:sort`, and `semver:rsort` all return JSON success payloads on valid input, and malformed input to `Semver` commands returns JSON runtime errors instead of raw stack traces.

### Milestone 4: Add Tooling Coverage And End-To-End Verification

The fourth milestone turns the working command set into a maintainable project. At the end of this milestone, the repository must have a real Pest suite, `phpstan.neon`, an updated `mago.toml`, and a reproducible validation sequence that covers `bin/`, `src/`, and `tests/`.

The proof for this milestone is that `vendor/bin/pest`, `vendor/bin/phpstan analyse`, `mago format --check`, `mago lint`, and `mago analyze` all succeed from the repository root, and `mago list-files` proves that `bin/comsem`, the new `src/` files, and the new `tests/` files are all included.

## Plan of Work

Create `src/Application.php` as `final class Application extends Symfony\Component\Console\Application`. Use `declare(strict_types=1);`, the repository namespace `TypistTech\ComSem`, and modern PHP override markers such as `#[\Override]` on methods that override Symfony behavior. Override `getLongVersion()` to provide a plain-text version summary for Symfony defaults. Override `doRunCommand()` and wrap `parent::doRunCommand()` in `try/catch`. If the command is not an instance of the project's shared `JsonCommand` base class, rethrow the exception so Symfony's text behavior stays intact. If the command is an instance of `JsonCommand`, render the failure JSON there and return the mapped exit code instead of rethrowing.

Create `src/Runner.php` as the single bootstrap and registration point. It must expose `public static function buildApplication(): Application` and `public static function run(): int`. `buildApplication()` must instantiate the custom `Application`, set the application name to `comsem`, derive the version from `Composer\InstalledVersions::getRootPackage()`, register all ten custom commands with `addCommands()`, and return the application. `run()` must call `buildApplication()->run()`. The existing `bin/comsem` file should remain as a thin wrapper that loads the autoloader and calls `Runner::run()`.

Create `src/Command/JsonCommand.php` as the shared abstract base class for every custom command. This class must extend `Symfony\Component\Console\Command\Command` and must centralize the JSON envelope, output-stream selection, newline handling, and exit-code mapping. It must provide one success helper for subclasses and one failure-rendering method callable by `Application`. Keep this class small and free of business logic. It is infrastructure, not another layer of abstraction for its own sake.

Create the six comparator command files under `src/Command/Comparator/`. Each file should define one `final` class, use `#[AsCommand]`, include a description and a short help string naming the wrapped method, and expose an `__invoke()` method with `#[Argument]` attributes. The methods should accept `OutputInterface $output` plus the required strings and then call the exact static `Composer\Semver\Comparator` method. Do not normalize or pre-validate the inputs.

Create the four semver command files under `src/Command/Semver/` with the same overall style. `semver:satisfies` takes two string arguments. `semver:satisfied-by` takes a string constraint first and an array of versions second so the CLI remains ergonomic. `semver:sort` and `semver:rsort` each take a single array argument. These command classes should call the wrapped static methods directly and let exceptions bubble to the application-level JSON renderer.

Add built-in help quality to every command through the `#[AsCommand]` attribute. Each command should include a description in plain English and at least one usage example via the `usages` attribute. The help text should mirror the reference project's style by explicitly stating which `composer/semver` method is being wrapped.

Create `tests/Pest.php` to initialize the Pest suite. Create `tests/Support/cli.php` and have `tests/Pest.php` require it. `tests/Support/cli.php` should define one small helper function, for example `runComsem(array $input): array`, that builds the application via `Runner::buildApplication()`, disables auto-exit, runs it through `Symfony\Component\Console\Tester\ApplicationTester` with `capture_stderr_separately` enabled, and returns a structured array containing `status`, `stdout`, `stderr`, and decoded JSON when present.

Create `tests/Feature/ComparatorCommandsTest.php` to cover all six comparator commands, including a dataset-driven happy path and at least one malformed-version case proving that comparator behavior is preserved. Create `tests/Feature/SemverCommandsTest.php` to cover all four semver commands, successful filtering and sorting, runtime failures from malformed versions or constraints, and usage failures from missing array arguments. Create `tests/Feature/DefaultSymfonyBehaviorTest.php` to prove that `--help`, `help comparator:greater-than`, `list`, and `--version` still behave like Symfony defaults instead of being forced into the custom JSON envelope.

Create `phpstan.neon` in the repository root. Keep it minimal like the reference project, but include Pest's PHPStan extension files from `vendor/pestphp/pest/extension.neon` and `vendor/pestphp/pest/phpstan-pest-extension.neon`. The `paths` section must include `bin`, `src`, and `tests`.

Update `mago.toml` so the `[source] paths` array becomes `['bin/', 'src/', 'tests/']`. Keep the existing `symfony` and `pest` integrations. After the config change, `mago list-files` must show `bin/comsem`, the new source files, and the new tests.

Do not add `phpunit` or PHPUnit configuration. Do not add a service container. Do not introduce dependency injection for the wrapped library because the wrapped methods are static and a container would add complexity without benefit. Do not replace the existing `bin/comsem` entrypoint structure because it already matches the reference project's bootstrap pattern.

## Concrete Steps

1. From `/Users/work/Code/comsem`, create the new PHP source files and test files named in `Plan of Work`, then run `composer dump-autoload`.

   Expected result: Composer regenerates autoload metadata without errors.

2. From `/Users/work/Code/comsem`, run `php bin/comsem --version`.

   Expected result: the command exits successfully and prints plain text containing `comsem`, the root package version such as `dev-main`, the installed `composer/semver` version, the installed `symfony/console` version, and the active PHP version.

3. From `/Users/work/Code/comsem`, run the six comparator smoke tests:

       php bin/comsem comparator:greater-than 1.25.0 1.24.0
       php bin/comsem comparator:greater-than-or-equal-to 1.25.0 1.25.0
       php bin/comsem comparator:less-than 1.24.0 1.25.0
       php bin/comsem comparator:less-than-or-equal-to 1.24.0 1.24.0
       php bin/comsem comparator:equal-to 1.24.0 1.24.0
       php bin/comsem comparator:not-equal-to 1.24.0 1.25.0

   Expected result: all six commands exit `0` and print one JSON object whose `result` value is `true`.

4. From `/Users/work/Code/comsem`, run the semver success smoke tests:

       php bin/comsem semver:satisfies 1.2.3 '^1.0'
       php bin/comsem semver:satisfied-by '^1.0' 1.0.0 1.2.0 2.0.0
       php bin/comsem semver:sort 1.0.0-beta 1.0.0 2.0.0
       php bin/comsem semver:rsort 1.0.0-beta 1.0.0 2.0.0

   Expected result: all four commands exit `0`. `semver:satisfies` returns `true`. `semver:satisfied-by` returns `['1.0.0', '1.2.0']`. `semver:sort` returns `['1.0.0-beta', '1.0.0', '2.0.0']`. `semver:rsort` returns `['2.0.0', '1.0.0', '1.0.0-beta']`.

5. From `/Users/work/Code/comsem`, run the custom-command usage-error smoke test:

       php bin/comsem semver:sort
       echo $?

   Expected result: the first command prints one JSON error object to standard error. The second command prints `2`.

6. From `/Users/work/Code/comsem`, run the custom-command runtime-error smoke tests:

       php bin/comsem semver:sort 1.0.0 not-a-version
       echo $?
       php bin/comsem semver:satisfies not-a-version '^1.0'
       echo $?

   Expected result: each CLI invocation prints one JSON error object to standard error with `category` equal to `runtime`, and each `echo $?` prints `1`.

7. From `/Users/work/Code/comsem`, run the comparator malformed-input preservation test:

       php bin/comsem comparator:greater-than not-a-version 1.0.0
       echo $?

   Expected result: the command still returns a JSON success payload with `result` equal to `false`, and the exit code remains `0`. This proves the wrapper preserved native `Comparator` behavior.

8. From `/Users/work/Code/comsem`, run the default-Symfony smoke tests:

       php bin/comsem --help
       php bin/comsem help comparator:greater-than
       php bin/comsem list
       php bin/comsem completion --help

   Expected result: these commands produce normal Symfony help or listing text instead of the custom JSON envelope.

9. From `/Users/work/Code/comsem`, run `mago list-files`.

   Expected result: the output includes at least `bin/comsem`, the new files under `src/`, and the new files under `tests/`. If `bin/comsem` is missing, fix `mago.toml` before trusting the later Mago checks.

10. From `/Users/work/Code/comsem`, run `vendor/bin/pest`.

    Expected result: all feature tests pass. The suite must cover success JSON, usage JSON, runtime JSON, and the non-JSON Symfony default behaviors.

11. From `/Users/work/Code/comsem`, run `vendor/bin/phpstan analyse`.

    Expected result: PHPStan finishes successfully at max level across `bin`, `src`, and `tests`.

12. From `/Users/work/Code/comsem`, run the Mago validation sequence:

        mago format --check
        mago lint
        mago analyze

    Expected result: all three commands succeed. `mago format --check` must report no formatting drift. `mago lint` and `mago analyze` must report no remaining issues at the configured severity threshold.

## Validation and Acceptance

Acceptance is behavioral. The change is complete only when a human can observe all of the following from the repository root.

- `php bin/comsem` is now a working Symfony Console application instead of crashing because `Runner` is missing.
- The ten custom commands listed in `In-Scope Command Surface` exist and are discoverable through `php bin/comsem list` and `php bin/comsem help <command>`.
- Every successful custom command emits exactly one JSON object on standard output and exits `0`.
- Every invalid invocation of a valid custom command emits exactly one JSON error object on standard error and exits `2`.
- Every non-usage custom-command failure emits exactly one JSON error object on standard error and exits `1`.
- At least one comparator malformed-input example is verified to return a successful boolean payload, proving the wrapper preserves native comparator behavior.
- At least one semver malformed-input example is verified to return a JSON runtime failure, proving the wrapper preserves native semver exception behavior.
- Default Symfony features such as `--help`, `--version`, `help`, `list`, and shell completion still work and remain outside the custom JSON contract.
- Pest, PHPStan, Mago format checking, Mago linting, and Mago analysis all pass after the implementation is complete.

The test suite should make these behaviors explicit. The most important tests are not line-coverage tests. They are end-to-end command tests that assert exit code, standard-output content, standard-error content, and decoded JSON structure.

## Idempotence and Recovery

This work is safe to repeat because it is additive. The repository has no database, no migrations, and no external service state to mutate.

If a partial implementation leaves `bin/comsem` booting but commands missing, rerun `composer dump-autoload` and then confirm that `Runner::buildApplication()` registers all ten commands. A missing registration should produce `Command "..." is not defined.` from Symfony, which is a signal to fix command registration rather than to edit the bootstrap.

If a custom command emits text instead of JSON, check two things first. Verify that the command extends the shared `JsonCommand` base class. Then verify that `Application::doRunCommand()` only intercepts throwables for instances of `JsonCommand`. If either link is broken, Symfony's default text rendering will leak through.

If `mago format --check`, `mago lint`, or `mago analyze` appear to ignore `bin/comsem` or the new tests, run `mago list-files` immediately. Do not trust passing Mago results until `mago list-files` proves the relevant files are included.

If Pest cannot find the suite, confirm that `tests/Pest.php` exists and that the `tests/` directory is present. If PHPStan reports unknown Pest helpers in tests, confirm that `phpstan.neon` includes both `vendor/pestphp/pest/extension.neon` and `vendor/pestphp/pest/phpstan-pest-extension.neon`.

No rollback strategy should involve destructive Git commands. The safe retry path is to edit the named files, rerun `composer dump-autoload`, rerun the targeted smoke tests, and then rerun the full validation sequence.

## Artifacts and Notes

Current pre-implementation failure state:

    $ php bin/comsem --help
    PHP Fatal error:  Uncaught Error: Class "TypistTech\ComSem\Runner" not found in /Users/work/Code/comsem/bin/comsem:21

Representative success transcript after implementation:

    $ php bin/comsem semver:satisfied-by '^1.0' 1.0.0 1.2.0 2.0.0
    {"ok":true,"command":"semver:satisfied-by","result":["1.0.0","1.2.0"]}
    $ echo $?
    0

Representative usage-failure transcript after implementation:

    $ php bin/comsem semver:sort
    {"ok":false,"command":"semver:sort","error":{"category":"usage","class":"Symfony\Component\Console\Exception\RuntimeException","message":"Not enough arguments (missing: \"versions\")."}}
    $ echo $?
    2

Representative runtime-failure transcript after implementation:

    $ php bin/comsem semver:sort 1.0.0 not-a-version
    {"ok":false,"command":"semver:sort","error":{"category":"runtime","class":"UnexpectedValueException","message":"Invalid version string \"not-a-version\""}}
    $ echo $?
    1

Representative preserved-comparator-behavior transcript after implementation:

    $ php bin/comsem comparator:greater-than not-a-version 1.0.0
    {"ok":true,"command":"comparator:greater-than","result":false}
    $ echo $?
    0

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
    }

The ten command files under `src/Command/Comparator/` and `src/Command/Semver/` must each define one `final` class that extends `JsonCommand`, uses `#[AsCommand]`, and implements command logic through an `__invoke()` method with `#[Argument]` attributes. No command in this initial implementation needs custom options, so `#[Option]` is not required.

`tests/Support/cli.php` must define one helper function that builds the application, disables auto-exit, runs it through `ApplicationTester`, and returns captured `status`, `stdout`, and `stderr` so tests can assert both streams independently.

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
