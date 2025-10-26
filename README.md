<div align="center">

# Composer SemVer

[![GitHub Release](https://img.shields.io/github/v/release/typisttech/composer-semver)](https://github.com/typisttech/composer-semver/releases)
[![Test](https://github.com/typisttech/composer-semver/actions/workflows/test.yml/badge.svg)](https://github.com/typisttech/composer-semver/actions/workflows/test.yml)
[![License](https://img.shields.io/github/license/typisttech/composer-semver.svg)](https://github.com/typisttech/composer-semver/blob/master/LICENSE)
[![Follow @TangRufus on X](https://img.shields.io/badge/Follow-TangRufus-15202B?logo=x&logoColor=white)](https://x.com/tangrufus)
[![Follow @TangRufus.com on Bluesky](https://img.shields.io/badge/Bluesky-TangRufus.com-blue?logo=bluesky)](https://bsky.app/profile/tangrufus.com)
[![Sponsor @TangRufus via GitHub](https://img.shields.io/badge/Sponsor-TangRufus-EA4AAA?logo=githubsponsors)](https://github.com/sponsors/tangrufus)
[![Hire Typist Tech](https://img.shields.io/badge/Hire-Typist%20Tech-778899)](https://typist.tech/contact/)

<p>
  <strong>Static linked CLI wrapper for <a href="https://packagist.org/packages/composer/semver"><code>composer/semver</code></a>.</strong>
  <br>
  Parsing and validating versions exactly like Composer does <strong>without installing PHP</strong>.
  <br>
  <br>
  Built with ♥ by <a href="https://typist.tech/">Typist Tech</a>
</p>

</div>

---

## Usage

### Normalize Versions

Normalizes a version string to be able to perform comparisons on it.
This is a wrapper of the [`Composer\Semver\VersionParser::normalize()`](https://github.com/composer/semver/blob/b52829022cb18210bb84e44e457bd4e890f8d2a7/src/VersionParser.php#L98-L108) method.

```console
$ composer-semver normalize '1.2-p.5+foo'
1.2.0.0-patch5

# Status code 0 means valid versions
$ echo $?
0

$ composer-semver normalize 'not-a-version'

 [ERROR] Invalid version string "not-a-version"

# Non-zero status codes mean invalid versions
$ echo $?
1

$ composer-semver normalize --help
Description:
  Normalizes a version string to be able to perform comparisons on it

Usage:
  normalize [options] [--] <version>

Arguments:
  version

Options:
      --full-version=FULL-VERSION  Complete version string to give more context.
  -h, --help                       Display help for the given command. When no command is given display help for the list command
      --silent                     Do not output any message
  -q, --quiet                      Only errors are displayed. All other output is suppressed
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This is a wrapper of the Composer\Semver\VersionParser::normalize() method.
```

### Parse Constraints

Parses a constraint string and strip its ignorable parts.
This is a wrapper of the [`Composer\Semver\VersionParser::parseConstraints()`](github.com/composer/semver/blob/b52829022cb18210bb84e44e457bd4e890f8d2a7/src/VersionParser.php#L251-L258) method.

```console
$ composer-semver parse '>=1.2 <2.0 || ~3.4.5 || ^6.7'
[[>= 1.2.0.0-dev < 2.0.0.0-dev] || [>= 3.4.5.0-dev < 3.5.0.0-dev] || [>= 6.7.0.0-dev < 7.0.0.0-dev]]

# Status code 0 means valid constraints
$ echo $?
0

$ composer-semver parse '~>1.2'

 [ERROR] Could not parse version constraint ~>1.2: Invalid operator "~>", you probably meant to use the "~"
         operator

# Non-zero status codes mean invalid constraints
$ echo $?
1

$ composer-semver parse --help
Description:
  Parses a constraint string and strip its ignorable parts

Usage:
  parse <constraints>

Arguments:
  constraints

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
      --silent          Do not output any message
  -q, --quiet           Only errors are displayed. All other output is suppressed
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This is a wrapper of the Composer\Semver\VersionParser::parseConstraints() method.
```

### Print Bundled PHP and `composer/semver` Versions

```console
$ composer-semver --version
```

### Dump Shell Completion Scripts

For shell completions, follow the instructions from:

```console
$ composer-semver completion --help
```

If you installed `Composer SemVer` [via Homebrew](#homebrew-macos--linux-recommended), completion scripts are managed by Homebrew.
Read more at https://docs.brew.sh/Shell-Completion

---

> [!TIP]
> **Hire Tang Rufus!**
>
> I am looking for my next role, freelance or full-time.
> If you find this tool useful, I can build you more weird stuff like this.
> Let's talk if you are hiring PHP / Ruby / Go developers.
>
> Contact me at https://typist.tech/contact/

---

## Why

> Which version numbering system does Composer itself use?
>
> Composer uses Semantic Versioning (aka SemVer) 2.0.0.
>
> -- [Composer FAQ](https://getcomposer.org/doc/faqs/which-version-numbering-system-does-composer-itself-use.md)

Under the hood, Composer uses [`composer/semver`](https://packagist.org/packages/composer/semver) to parse and validate versions.

Despite the lie on [Composer FAQ](https://getcomposer.org/doc/faqs/which-version-numbering-system-does-composer-itself-use.md), the [fragmentary documentation](https://getcomposer.org/doc/articles/versions.md), and the deceitful package name `composer/semver`,
Composer implements only a subset of [Semantic Versioning](https://semver.org/) specification while supports some uncommon versioning schemes.
Working with Composer packages versions without `composer/semver` is a bit tricky.

*"A bit tricky"* is an understatement.

| Version                 | Validity                                                                                                                                                                                                           |
|-------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `1.2.3`                 | :white_check_mark: Valid.                                                                                                                                                                                          |
| `1.2.3.4`               | :white_check_mark: Valid. But not SemVer compliant.                                                                                                                                                                |
| `1.0.0-a`               | :white_check_mark: Valid.                                                                                                                                                                                          |
| `1.0.0-b`               | :white_check_mark: Valid.                                                                                                                                                                                          |
| `1.0.0-c`               | :x: Invalid. But SemVer compliant. Composer only accepts [a limited sets of pre-release versions](https://github.com/composer/semver/blob/b52829022cb18210bb84e44e457bd4e890f8d2a7/src/VersionParser.php#L26-L39). |
| `99999`                 | :white_check_mark: Valid. Composer normalize it to `99999.0.0.0`.                                                                                                                                                  |
| `100000.0.0`            | :white_check_mark: Valid                                                                                                                                                                                           |
| `100000.0.0.0`          | :x: Invalid. Starting from `100000`, Composer treats it as CalVer which cannot have 4 bits.                                                                                                                        |
| `2010-01-02`            | :white_check_mark: Valid.                                                                                                                                                                                          |
| `2010-01-02-10-20-30.5` | :white_check_mark: Valid.                                                                                                                                                                                          |
| `20100102-203040`       | :white_check_mark: Valid.                                                                                                                                                                                          |
| `20100102.0.3.4`        | :x: Invalid. CalVer cannot have 4 bits.                                                                                                                                                                            |
| `2023013.0.0`           | :x: Invalid. `YYYYMMD` is a bad CalVer major version.                                                                                                                                                              |
| `202301311.0.0`         | :x: Invalid. `YYYYMMDDh` is a bad CalVer major version.                                                                                                                                                            |
| `20230131000.0.0`       | :x: Invalid.`YYYYMMDDhhm` is a bad CalVer major version.                                                                                                                                                           |
| `2023013100000.0.0`     | :x: Invalid. `YYYYMMDDhhmmX` is a bad CalVer major version.                                                                                                                                                        |
| `000.001.003.004`       | :white_check_mark: Valid. Composer normalizes it to `000.001.003.004`. The leading zeros are significant and cannot be ignored.                                                                                    |
| `0700`                  | :white_check_mark: Valid. Composer normalizes it to `0700.0.0.0`. The leading zero is significant and cannot be ignored.                                                                                           |
| `1.00.000`              | :white_check_mark: Valid. Composer normalizes it to `1.00.000.0`. All the zeroes are significant and cannot be ignored.                                                                                            |

`Composer SemVer` wraps `composer/semver` and the PHP runtime as a static linked CLI tool,
so you can work with the package versions exactly like Composer does without installing PHP.

> [!TIP]
> **Hire Tang Rufus!**
>
> There is no need to understand any of these quirks.
> Let me handle them for you.
> I am seeking my next job, freelance or full-time.
>
> If you are hiring PHP / Ruby / Go developers,
> contact me at https://typist.tech/contact/

## Installation

### Homebrew (macOS / Linux) (Recommended)

```sh
brew tap typisttech/tap
brew install typisttech/tap/composer-semver
```

### `apt-get` (Debian based distributions, for example: Ubuntu)

```sh
curl -1sLf 'https://dl.cloudsmith.io/public/typisttech/oss/setup.deb.sh' | sudo -E bash
sudo apt-get install composer-version
```

Instead of the automatic setup script, you can manually configure the repository with the instructions on [Cloudsmith](https://cloudsmith.io/~typisttech/repos/oss/setup/#formats-deb).

### Manual `.deb` (Debian based distributions, for example: Ubuntu)

> [!WARNING]
> If you install the `.deb` file manually, you have to take care of updating it by yourself.

Download the latest `.deb` file from [GitHub Releases](https://github.com/typisttech/composer-semver/releases/latest), or via [`gh`](https://cli.github.com/):

```sh
# Both arm64 (aarch64) and amd64 (x86_64) architectures are available.
gh release download --repo 'typisttech/composer-semver' --pattern 'composer-semver_linux_arm64.deb'
```

**Optionally**, verify the `.deb` file:

```sh
gh attestation verify --repo 'typisttech/composer-semver' 'composer-semver_linux_arm64.deb'
```

Finally, install the package:

```sh
sudo dpkg -i composer-semver_linux_arm64.deb
```

## Manual Binary

> [!WARNING]
> If you install the binary manually, you have to take care of updating it by yourself.

Download the latest `.tar.gz` file from [GitHub Releases](https://github.com/typisttech/composer-semver/releases/latest), or via [`gh`](https://cli.github.com/):

```sh
# Both darwin (macOS) and linux operating systems are available.
# Both arm64 (aarch64) and amd64 (x86_64) architectures are available.
gh release download --repo 'typisttech/composer-semver' --pattern 'composer-semver_darwin_arm64.tar.gz'
```

**Optionally**, verify the `.tar.gz` file:

```sh
gh attestation verify --repo 'typisttech/composer-semver' 'composer-semver_darwin_arm64.tar.gz'
```

Finally, unarchive and move the binary into `$PATH`:

```sh
tar -xvf 'composer-semver_darwin_arm64.tar.gz'

# Or, move it to any directory under `$PATH`
mv composer-semver /usr/local/bin
```

## Alternatives

- [ComVer](https://github.com/typisttech/comver)  
  A failed attempt to re-implement `composer/semver` in Go.
  It only supports a subset of Composer versioning.

## Credits

[`Composer SemVer`](https://github.com/typisttech/composer-semver) is a [Typist Tech](https://typist.tech) project and maintained by [Tang Rufus](https://x.com/TangRufus), freelance developer [for hire](https://typist.tech/contact/).

Full list of contributors can be found [on GitHub](https://github.com/typisttech/composer-semver/graphs/contributors).

## Copyright and License

This project is a [free software](https://www.gnu.org/philosophy/free-sw.en.html) distributed under the terms of the MIT license.
For the full license, see [LICENSE](./LICENSE).

## Contribute

Feedbacks / bug reports / pull requests are welcome.
