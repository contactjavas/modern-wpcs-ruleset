# Modern WPCS Ruleset

These are a set of modern (PHP >= 8.2) linting guidelines for WordPress development. Because of the newer PHP version, it is not suitable for work on Core WordPress, but may be useful for those who are not bound by legacy PHP versions.

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

- `ModernWpcsStandard.Whitespace.RequireParenthesisSpacing` is now set to severity 0. WordPress-Core's own sniffs (`PEAR.Functions.FunctionCallSignature`, `Squiz.Functions.FunctionDeclarationArgumentSpacing`, `WordPress.WhiteSpace.ControlStructureSpacing`, `Generic.WhiteSpace.ArbitraryParenthesesSpacing`, …) already report missing parenthesis padding, so each missing space was reported twice. The one construct no longer flagged is `list(...)`. To restore the sniff, add `<rule ref="ModernWpcsStandard.Whitespace.RequireParenthesisSpacing"><severity>5</severity></rule>` to your `phpcs.xml`.
- The `ignoreUnimportedSymbols` whitelist on `ImportDetection.Imports.RequireImports` (Automattic-internal `BD_*`, `WPCOM_*`, `BILLINGDADDY_HOME`, `require_lib`, `tracks_record_event`) was removed. Set your own regex in your `phpcs.xml` if you need one.
