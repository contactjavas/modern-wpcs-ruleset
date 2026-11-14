# Modern WPCS Ruleset Project Rules for AI Agents

## Project Overview

**Modern WPCS Ruleset** (`contactjavas/modern-wpcs-ruleset`) is a [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) **meta-ruleset** for strict, modern WordPress PHP code. It contains no sniffs of its own: it combines other standards and tunes them in a single `ruleset.xml`.

### Purpose
- Provide one `ModernWpcsRuleset` standard that consumers enable with `<rule ref="ModernWpcsRuleset"/>`
- Combine and adjust these packages:
  - [WordPress Coding Standards](https://github.com/contactjavas/wpcs) (`contactjavas/wpcs`, fork). Only `WordPress-Core` is included.
  - [VariableAnalysis](https://github.com/sirbrillig/phpcs-variable-analysis)
  - [ModernWpcsStandard](https://github.com/contactjavas/modern-wpcs-standard), sibling checkout at `../modern-wpcs-standard`
  - [ImportDetection](https://github.com/contactjavas/phpcs-import-detection) (fork)

### Tech Stack
- **Language**: PHP. `composer.json` and the README say `^8.2`, and the CI matrix is 8.2–8.5. See "Upstream Dependencies" for the pending move to `^8.4`.
- **Framework**: PHP_CodeSniffer `^4.0.1` → [Annotated ruleset](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Annotated-Ruleset)
- **Package Manager**: Composer (package type `phpcodesniffer-standard`, registered via `dealerdirect/phpcodesniffer-composer-installer`). The forked packages come from the VCS `repositories` in `composer.json`.
- **Dev machine**: Windows + PowerShell. `php` and `composer` are on `PATH`.

### Origin
- Forked from [Automattic/phpcs-neutron-ruleset](https://github.com/Automattic/phpcs-neutron-ruleset) (`upstream` remote). Upstream is not tracked actively; do not pull from it unless asked.

### Constraints
- Must use the existing codebase and architecture
- Fix sniff *bugs* in the package that owns the sniff (usually `../modern-wpcs-standard`), not by working around them here. This repo only decides which rules apply and how strict they are.

---

## Package Management

- **Package Manager**: Always use `composer`
- **Commands**:
  - Install: `composer install`
  - Update one dependency: `composer update <vendor/package>`
  - Validate (CI runs this): `composer validate --strict`
- `composer.lock` is gitignored; do not commit it.
- When adding a forked or unpublished dependency, add its VCS repository to `composer.json` **and** to the README's installation snippet. Consumers need it too.

---

## Security

- Never hardcode sensitive data (tokens, credentials) in config, CI or fixtures
- Do not silence security-relevant WordPress sniffs (`WordPress.Security.*`, `WordPress.DB.PreparedSQL.*`) further without the user's explicit approval
- Keep dependency constraints current when security advisories appear (`composer audit`)

---

## Ruleset Standards

### 1. Editing `ModernWpcsRuleset/ruleset.xml`
- Use **tabs** for indentation, matching the existing file
- Every override that isn't self-explanatory gets a one-line `<!-- … -->` comment explaining *why*, e.g. which sniff it conflicts with
- Prefer `<severity>0</severity>` (or `<type>`) on a specific error code over excluding a whole sniff
- Reference the **current** sniff code. PHPCS 4 renamed codes (e.g. `…NotSameWarning` → `…NotSame`), and WPCS 3+ moved sniffs to `Universal.*` (e.g. `Universal.Arrays.DisallowShortArraySyntax`). PHPCS silently ignores refs to unknown *error codes*, so a stale ref looks fine but does nothing. Confirm with `phpcs -s` that the code is actually reported.

### 2. Public API
- Error codes and properties in `ruleset.xml` are what consumers override in their own `phpcs.xml`. Changing them changes behaviour in every project that uses this ruleset.
- Every user-visible behaviour change gets an entry under **Changes → Unreleased** in `README.md`. Say what changed, why, and how to restore the old behaviour.

### 3. Sniff Interactions
- Fixers from different standards must not fight each other. Typical conflicts: short vs long arrays, assignment alignment vs `DisallowAssignAlign`, duplicate parenthesis-spacing reports. `phpcbf` must converge, never "FAILED TO FIX".
- Avoid enabling two sniffs that report the same problem. Keep the more context-aware one and set the other to severity 0.

### 4. Performance
- Runtime is dominated by the included sniffs. `ImportDetection.Imports.RequireImports` is the most expensive. The first run on Windows is slow because of cold start, not sniff cost. Don't optimise without a measurement.

---

## Development Workflow

### 1. Implementation

- Carefully read and interpret all user instructions
- Reproduce the behaviour first with a small sample file. Keep scratch files **outside** the repo unless you are adding a fixture on purpose.
- Ask for clarification when requirements are ambiguous or missing

### 2. Fixtures

There are no unit tests. CI checks the ruleset against three fixtures in `tests/fixtures/`:

| Fixture | Expectation |
| --- | --- |
| `Clean.php` | Code in the enforced style. `phpcs` must report **nothing**. |
| `Fixable.php` | `phpcbf` must converge (exit code < 4, no "FAILED TO FIX") and leave it clean. |
| `Dirty.php` | Must report specific sniff codes (listed in `.github/workflows/ci.yml`). |

When a change affects what is allowed, update the fixtures and the expected-code list in CI to match. Save fixtures with **LF** line endings: the ruleset enforces LF, and `.gitattributes` forces `eol=lf`. Use the Write tool, not PowerShell `Set-Content`.

### 3. Verification

**Always verify** before reporting a task as done. Run the same checks as CI from the repo root:

```
composer validate --strict
php vendor/bin/phpcs --standard=ModernWpcsRuleset -e                       # loads; fails on bad sniff refs / unknown properties
php vendor/bin/phpcs --standard=ModernWpcsRuleset tests/fixtures/Clean.php # must report nothing
php vendor/bin/phpcs -s --report=emacs --standard=ModernWpcsRuleset tests/fixtures/Dirty.php
```

For the fixer check, copy `tests/fixtures/Fixable.php` to a temp dir and run `php vendor/bin/phpcbf --standard=ModernWpcsRuleset <copy>`, then `phpcs` on the copy. Never run `phpcbf` on the fixture in place.

### 4. Testing Loop

```
1. Reproduce with a sample file (phpcs -s shows sniff codes)
2. Edit ruleset.xml / fixtures
3. Run the verification commands
4. If incorrect → fix → test again → examine again
5. Repeat until correct
```

---

## Error Handling Protocol

### 1. Error Analysis
- Parse and analyze error messages. `phpcs -s` shows which sniff and code reported each one.
- Decide whether the root cause is in this ruleset (config) or in an upstream sniff (bug)

### 2. Solution Planning
- Consider multiple solutions
- Select the optimal fix based on risk and scope. Config tweak here vs fix upstream.

### 3. Fix & Validate
- Implement the fix with minimal disruption
- Run the full verification to check for regressions

### 4. Documentation
- Add a README "Changes → Unreleased" entry for behaviour changes
- If the real fix belongs upstream, say so and describe it (with a repro) instead of papering over it here

---

## Upstream Dependencies

- `contactjavas/modern-wpcs-standard` is required by tag (`^2.0.0`). Fixes made in `../modern-wpcs-standard` arrive only after it tags a release. Then run `composer update contactjavas/modern-wpcs-standard` here and re-run the verification.
- **Pending:** the standard now requires PHP `^8.4`, so its next release will be 3.0.0. When that happens, this repo must bump `contactjavas/modern-wpcs-standard` to `^3.0`, raise its own `php` to `^8.4`, drop 8.2/8.3 from the CI matrix, and update the README's "PHP >= 8.2".
- After any dependency update, re-check that every ref in `ruleset.xml` still exists. Renamed codes fail silently (see Ruleset Standards §1).

---

## Git Workflow

> **IMPORTANT**: Do NOT commit or push unless the user explicitly asks you to.

### When Asked to Commit & Push

1. **Stage changes**: `git add .`. Check `git status` first, so nothing unexpected is included.

2. **Commit with this repo's format**:
   - **Subject line**: imperative mood, naming what changed, e.g. `Fix fixer conflicts: allow short arrays`
   - **Description**: Structured explanation (can be a list). Do not mention the AI agent, and do not add an AI co-author.

   ```bash
   git commit -m "Brief imperative subject" -m "Detailed description here.

   - Change 1
   - Change 2"
   ```

3. **Push to GitHub**: `git push` (remote `origin`, branch `master`). Never push to `upstream`.

- Do not create tags or releases unless the user asks. When asked, follow the `release` skill.

---

## Skills

Task-specific workflows live in `.agents/skills/<name>/SKILL.md`. When a request matches a skill's description, read that file and follow it.

| Skill | Path | Use when |
| --- | --- | --- |
| **release** | [`.agents/skills/release/SKILL.md`](.agents/skills/release/SKILL.md) | The user asks to release, tag or publish a version: sync the upstream standard, verify, move README "Unreleased" changes under the new version, annotated `vX.Y.Z` tag, push. |

---

## Project Structure

```
modern-wpcs-ruleset/
├── .agents/
│   ├── skills/                 # Task-specific agent skills & workflows
│   └── tasks/                  # Local task/audit notes (gitignored, may not exist)
├── .github/workflows/ci.yml    # validate, ruleset loads, Clean/Fixable/Dirty smoke tests on PHP 8.2–8.5
├── ModernWpcsRuleset/
│   └── ruleset.xml             # The entire product: included standards + overrides
├── tests/
│   └── fixtures/               # Clean.php, Fixable.php, Dirty.php (CI smoke tests)
├── composer.json               # Dependencies + VCS repositories for the forks
└── README.md                   # Install/config docs + "Changes" log
```

---

## Important

- Maintain clarity and request confirmation when needed
- Ensure high-quality, scoped, and standards-compliant implementations
- Regularly report progress during multi-step tasks
- Pause and confirm at critical decision points (silencing a rule, dropping a standard, version bumps)
- Clearly report errors or blockers, and propose practical resolutions

---

## Reference Documentation

**Do NOT read these preemptively.** Consult one only when the task touches its domain:

| Document | Path | Description |
| --- | --- | --- |
| **Usage / Changes** | [`README.md`](README.md) | Consumer install/config docs and the behaviour-change log. |
| **CI checks** | [`.github/workflows/ci.yml`](.github/workflows/ci.yml) | Exact smoke tests and expected sniff codes for `Dirty.php`. |
| **Sniff standard** | `../modern-wpcs-standard/AGENTS.md`, `../modern-wpcs-standard/GUIDELINES.md` | Rules and rationale of the custom sniffs this ruleset includes. |
| **Audit tasks (local)** | `.agents/tasks/<audit>/README.md` | Gitignored audit/task notes with their own agent workflow. Only use them when the user points you there. |
