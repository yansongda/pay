# Security Update Note

## Symfony VarDumper Vulnerability

### Status: Requires composer.lock regeneration

This PR updates `composer.json` to require `symfony/var-dumper: ^6.4.4` which addresses the unsafe deserialization vulnerability (CVE pending).

### What was done:
- ✅ Updated `composer.json` to require `^6.4.4` (patched version)

### What needs to be done by maintainer:
- ⚠️ Run `composer update symfony/var-dumper` to update `composer.lock`
- ⚠️ Or run `composer update` to update all dev dependencies

### Why composer.lock wasn't updated in this PR:
The CI/sandbox environment lacks GitHub authentication tokens needed to download packages from GitHub's API. The maintainer with proper credentials should run the update command locally.

### Verification after running composer update:
```bash
composer show symfony/var-dumper
# Should show version >= 6.4.4
```

### Vulnerability Details:
- **Package**: symfony/var-dumper
- **Current version in lock**: 5.4.48
- **Required version in json**: ^6.4.4 ✅
- **Patched version**: 6.4.4+
- **Severity**: Dev dependency only (not used in production)
- **Advisory Status**: Withdrawn (but good practice to update anyway)

### Impact:
- This is a **development-only** dependency
- Does not affect production code
- Only used for debugging and testing
- Low risk but should still be addressed
