# Coding Assertions

The checks that must pass for code to count as done. Minimal, run after every change.
Every command runs inside Docker: the `php` and `front` containers must be up (`make up`, check with `docker compose ps`).
Source of truth: the `Makefile` targets. `make check-fast` and `make check` chain the rows below and stop at the first failure.

## Before commit

The fast gate: `make check-fast`.

| Order | Command          | Checks                                                    |
| ----- | ---------------- | --------------------------------------------------------- |
| 1     | `make lint-php`  | PHP syntax of `src/` and `tests/`                         |
| 2     | `make analyse`   | PHPStan (`phpstan.dist.neon` + baseline), no new error    |
| 3     | `make lint-front`| ESLint on `front/`, 0 error (warnings tolerated, none added) |

## Before push

The heavier gate: `make check`. Clears `.agents/.needs-check` only when everything passes.

| Order | Command            | Checks                         |
| ----- | ------------------ | ------------------------------ |
| 1     | `make check-fast`  | The fast gate above            |
| 2     | `make test`        | PHPUnit (`tests/`)             |
| 3     | `make build-front` | Vite production build and PWA  |

## Gotchas

- Never add entries to `phpstan-baseline.neon` to make `make analyse` pass.
- CI (`.github/workflows/ci.yml`) runs PHPStan as informational and has no ESLint step; locally both are blocking.
- A failure that predates the change is reported by name, never hidden.

## Behavior

If a fix is needed, spawn 1 agent per failing assertion (e.g. PHPStan / PHPUnit / ESLint = 3 agents).
