# Modern WPCS Ruleset

These are a set of modern (PHP >= 8.4) linting guidelines for WordPress development. Because of the newer PHP version, it is not suitable for work on Core WordPress, but may be useful for those who are not bound by legacy PHP versions.

These guidelines are being developed for projects that want strict, modern WordPress PHP code quality enforcement.

This project is a [phpcs](https://github.com/squizlabs/PHP_CodeSniffer) "standard" (a collection of rules or "sniffs") that can be included in any project.

This is a meta-project in that it's just a collection of rules defined in these packages with certain modifications:

- [WordPress Coding Standards](https://github.com/contactjavas/wpcs)
- [VariableAnalysis](https://github.com/sirbrillig/phpcs-variable-analysis)
- [ModernWpcsStandard](https://github.com/contactjavas/modern-wpcs-standard)
- [ImportDetection](https://github.com/contactjavas/phpcs-import-detection)

## Installation

To use these rules in a project which is set up using [composer](https://getcomposer.org/), we recommend using the [phpcodesniffer-composer-installer library](https://github.com/PHPCSStandards/composer-installer) which will automatically use all installed standards in the current project with the composer type `phpcodesniffer-standard` when you run phpcs.

First, add the required VCS repositories to your project's `composer.json`:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/contactjavas/modern-wpcs-ruleset" },
    { "type": "vcs", "url": "https://github.com/contactjavas/modern-wpcs-standard" },
    { "type": "vcs", "url": "https://github.com/contactjavas/phpcs-import-detection" },
    { "type": "vcs", "url": "https://github.com/contactjavas/wpcs" }
]
```

Then install:

```
composer require --dev squizlabs/php_codesniffer dealerdirect/phpcodesniffer-composer-installer
composer require --dev contactjavas/modern-wpcs-ruleset
```

## Configuration

When installing sniff standards in a project, you edit a `phpcs.xml` file with the `rule` tag inside the `ruleset` tag. The `ref` attribute of that tag should specify a standard, category, sniff, or error code to enable. It's also possible to use these tags to disable or modify certain rules. The [official annotated file](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Annotated-ruleset.xml) explains how to do this.

The following configuration will enable all the sniffs in this ruleset.

```xml
<?xml version="1.0"?>
<ruleset name="MyStandard">
 <description>My library.</description>
 <rule ref="ModernWpcsRuleset"/>
</ruleset>
```

## Usage

Most editors have a phpcs plugin available, but you can also run phpcs manually. To run phpcs on a file in your project, just use the command-line as follows (the `-s` causes the sniff code to be shown, which is very important for learning about an error).

```
vendor/bin/phpcs -s src/MyProject/MyClass.php
```

## Changes

Unreleased:

v2.1.0 (2026-11-19):

- The ruleset now requires `contactjavas/wpcs ^5.0` (was `^4.0`). Sniff names, error codes and properties are unchanged, so no `phpcs.xml` changes are needed. Changes that reach this ruleset (which includes `WordPress-Core`):
  - `WordPress.DateTime.CurrentTimeTimestamp`: `phpcbf` no longer turns `\current_time( 'timestamp', true )` into `time()` without the leading backslash.
  - `WordPress.PHP.NoSilencedErrors`: no internal error when `customAllowedFunctionsList` in your `phpcs.xml` has an empty item.
  - It also brings the upstream WordPressCS 3.4.1 security fix for `WordPress.WP.EnqueuedResourceParameters`. That sniff is not in `WordPress-Core`, but it matters if your `phpcs.xml` adds `WordPress-Extra` or `WordPress`. See the [v5.0.0 release notes](https://github.com/contactjavas/wpcs/releases/tag/v5.0.0).

v2.0.0 (2026-11-14):

- **Breaking:** PHP 8.4 or newer is required (was 8.2). The ruleset now requires `contactjavas/modern-wpcs-standard ^3.0`, which raised its PHP requirement and brings sniff fixes (TypeHint, LongFunction, DisallowMultipleNewlines fixer, DisallowGlobalFunctions, and more). It also replaces the `__serialize` ban (`ModernWpcsStandard.MagicMethods.DisallowMagicSerialize`, removed) with a ban on the deprecated `Serializable` interface (`ModernWpcsStandard.MagicMethods.DisallowSerializableInterface.SerializableInterface`). See the [v3.0.0 release notes](https://github.com/contactjavas/modern-wpcs-standard/releases/tag/v3.0.0).
- `ModernWpcsStandard.Whitespace.RequireParenthesisSpacing` is now set to severity 0. WordPress-Core's own sniffs (`PEAR.Functions.FunctionCallSignature`, `Squiz.Functions.FunctionDeclarationArgumentSpacing`, `WordPress.WhiteSpace.ControlStructureSpacing`, `Generic.WhiteSpace.ArbitraryParenthesesSpacing`, …) already report missing parenthesis padding, so each missing space was reported twice. The one construct no longer flagged is `list(...)`. To restore the sniff, add `<rule ref="ModernWpcsStandard.Whitespace.RequireParenthesisSpacing"><severity>5</severity></rule>` to your `phpcs.xml`.
- The `ignoreUnimportedSymbols` whitelist on `ImportDetection.Imports.RequireImports` (Automattic-internal `BD_*`, `WPCOM_*`, `BILLINGDADDY_HOME`, `require_lib`, `tracks_record_event`) was removed. Set your own regex in your `phpcs.xml` if you need one.
- Short array syntax (`[]`) is accepted again. Every array literal used to be an error, and `phpcbf` failed with "FAILED TO FIX": WordPress-Core (WPCS 3+) forbids `[]` through `Universal.Arrays.DisallowShortArraySyntax`, and the ruleset still silenced the old WPCS 2 name `Generic.Arrays.DisallowShortArraySyntax`. The rule now targets `Universal.Arrays.DisallowShortArraySyntax`. `array()` is still reported by `ModernWpcsStandard.Arrays.DisallowLongformArray`.
- Unaligned assignments are no longer reported. PHPCS 4 renamed `Generic.Formatting.MultipleStatementAlignment.NotSameWarning` to `.NotSame`, so the old severity-0 override stopped applying. Because `ModernWpcsStandard.AssignAlign.DisallowAssignAlign` forbids alignment, adjacent assignments got a warning either way and `phpcbf` failed. The override now targets `.NotSame`.
