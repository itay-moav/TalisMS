# Tests

## Suites

- `Unit` (`tests/Unit`): isolated tests for a single class or function.
- `Integration` (`tests/Integration`): tests that exercise more than one class together.

## Commands

- `composer test` — run all tests.
- `vendor/bin/phpunit tests/Unit` — run only the Unit suite.
- `vendor/bin/phpunit tests/Integration` — run only the Integration suite.
- `composer stan` — run phpstan (level max) over `src`, `tests` and `demo`.
- `composer coverage` — run the tests with coverage and fail if line coverage of `src` drops below 100%.

## Coverage ignores

None yet.
