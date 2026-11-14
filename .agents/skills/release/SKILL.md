---
name: release
description: Cut a new version of contactjavas/modern-wpcs-ruleset by verifying the ruleset, optionally syncing a new modern-wpcs-standard release, moving README "Unreleased" changes under the new version, creating an annotated vX.Y.Z tag and pushing it to origin (Composer reads versions from tags). Use when the user says "release", "tag a release", "cut a version", "publish", or "tag and push".
---

# Release (verify → changelog → tag → push)

There is no `version` field in `composer.json` and no deploy workflow. Consumers install this
package from the GitHub VCS repository, so **pushing a `vX.Y.Z` tag to `origin` is the release**.
A pushed tag is public and can't be taken back cleanly.

## Steps

1. **Inspect state**
   - You must be on `master`, with a clean working tree (`git status`). If there are uncommitted changes,
     stop and ask whether they belong in the release. If they do, commit them first using the format
     from `AGENTS.md` → Git Workflow.
   - `git fetch origin --tags`. Local `master` must not be behind `origin/master`.
   - Latest released version: take it **from origin**, not from local tags. The clone also holds old
     `v3.x` tags from the Automattic `upstream` remote, and they are not this package's versions:
     `git ls-remote --tags --refs origin` and take the highest `vX.Y.Z` (e.g. `v1.0.0`).
   - What's unreleased: `git log --oneline <latest-tag>..HEAD`. If nothing is unreleased, tell the user and stop.

2. **Sync the upstream standard (if it released)**
   - `git -C ../modern-wpcs-standard ls-remote --tags --refs origin` (or ask the user) for the newest
     `contactjavas/modern-wpcs-standard` tag.
   - If its **major** version is newer than the `composer.json` constraint (currently `^2.0.0`), the bump is
     part of this release. Ask the user before doing it. Then: update the constraint, raise `php` to the
     standard's minimum (it needs `^8.4` from 3.0.0 on), drop unsupported PHP versions from the
     `.github/workflows/ci.yml` matrix, update "PHP >= …" in `README.md`, run `composer update`, and
     add a README "Unreleased" entry. Commit this before step 3.
   - For a newer minor or patch, just run `composer update contactjavas/modern-wpcs-standard` so step 3 tests against it.

3. **Verify**: the same checks as CI. Every one must pass:
   ```
   composer validate --strict
   php vendor/bin/phpcs --standard=ModernWpcsRuleset -e
   php vendor/bin/phpcs --standard=ModernWpcsRuleset tests/fixtures/Clean.php
   php vendor/bin/phpcs -s --report=emacs --standard=ModernWpcsRuleset tests/fixtures/Dirty.php
   ```
   For `Dirty.php`, check that every code listed in the CI "dirty fixture" step is reported. Run
   `phpcbf` on a **temp copy** of `tests/fixtures/Fixable.php`: no "FAILED TO FIX", exit code < 4,
   then `phpcs` on the copy reports nothing.
   Also check CI: `gh run list --branch master --limit 1` must be `completed success` for `HEAD`.
   If CI is running, say so and wait or ask. If it failed, stop.

4. **Pick the version** (semver). Base it on the README "Changes → Unreleased" entries and `git diff <latest-tag>..HEAD`:
   - **Major**: the minimum `php` requirement was raised, an included standard was dropped, or the
     ModernWpcsStandard major was bumped (step 2).
   - **Minor**: the ruleset got stricter. New sniffs or standards enabled, a severity raised, a warning turned
     into an error, or an ignore list removed. Users' CI may start failing.
   - **Patch**: the ruleset got looser or was fixed (duplicate reports silenced, fixer conflicts fixed, stale refs
     corrected), or the change is docs, CI or fixtures only.
   - Mixed changes take the highest level. Decide patch/minor yourself. For a **major**, tell the user why and get confirmation.

5. **Changelog commit**: in `README.md`, rename the `Unreleased:` heading under `## Changes` to
   `vX.Y.Z (YYYY-MM-DD):` and put a new empty `Unreleased:` above it. If there's no Unreleased entry but the
   release changes behaviour, write the entries first. Commit:
   ```powershell
   git add README.md
   git commit -m "Release vX.Y.Z"
   ```

6. **Tag** that commit with an **annotated** tag, leading `v`, three parts:
   ```powershell
   git tag -a vX.Y.Z -m @'
   Release vX.Y.Z: <one-line summary>

   - <notable change>
   - <notable change>
   '@
   ```
   One line per user-visible change, consistent with the README entries. Don't mention the AI agent.

7. **Push** the branch first, then **only this tag**:
   ```
   git push origin master
   git push origin vX.Y.Z
   ```
   **Never** `git push --tags`: it would publish the upstream `v3.x` tags as versions of this package.
   Never push to `upstream`. Don't use `--force`. If the branch push is rejected, `git pull --rebase origin master`,
   re-run step 3, re-create the still-unpushed tag on the new commit, and push again.

8. **Report**: the tag, its commit hash, the previous → new version, the README entries it contains, and
   whether the ModernWpcsStandard constraint changed. A GitHub Release page is optional. Create one with
   `gh release create vX.Y.Z --notes-from-tag` only if the user asks.

## Don'ts

- Don't add a `version` field to `composer.json`. Composer takes the version from the tag.
- Don't release on top of a ModernWpcsStandard fix that hasn't been tagged upstream. CI resolves dependencies from tags.
- Don't skip hooks (`--no-verify`) or amend commits that were already pushed.
- Don't move, delete or re-push a tag that already exists on `origin`. Release the next version instead.
