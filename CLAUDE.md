# CLAUDE.md

This file guides Claude Code (claude.ai/code) when working with code in this repository.

## What this is

TalisMS v5 is a small PHP 8.5+ API / middleware-style framework. The library lives in `src/Talis` (PSR-4, `Talis\` → `src/Talis/`). Requests come in through "doors" (Rest, HTTP, Cli, CliEmbedded, Daemon) and run through a chain of links: filters, then dependencies (validators), then business-logic (BL) links. `demo/` is a small runnable app that uses every door. Release notes and the v4 → v5 migration guide are in `DOCS/RELEASE-NOTES.md`.

## Commands

```bash
composer install
composer test                                   # all tests (Unit + Integration suites)
composer stan                                   # phpstan level max over src, tests, demo; must say [OK] No errors
composer coverage                               # tests with pcov coverage; fails if src/ line coverage < 100%
vendor/bin/phpunit tests/Unit                   # one suite
vendor/bin/phpunit --filter testName            # a single test
vendor/bin/phpstan analyse src/Talis/Doors      # phpstan on some paths
php -S 127.0.0.1:8002 demo/doors/standalone/router.php   # demo: /api/* -> Rest, /http/* -> HTTP, else the web page
```

`demo/README.md` has copy-paste commands for every door. CI (`.github/workflows/ci.yml`) runs `composer stan` and `composer coverage` on PHP 8.5 with pcov.

## Layout

```
src/Talis/          the library: Chain/, Doors/, Message/, Router/, Exception/, commons/, TalisMain.php, Context.php
tests/Unit/         one test class per src class
tests/Integration/  the demo app driven end to end through every door (php -S, proc_open, worker --once)
tests/Support/      SpyLogger, SpyEmitter
tests/Fixtures/     small API classes and routers used by tests
demo/               config/, application/api/demo/user/*.php, application/model/User/, doors/, web/
demo/var/           git-ignored runtime data (users.json, spool folders, logs)
bin/check-coverage.php   reads the clover file and fails under the given line coverage
DOCS/               RELEASE-NOTES.md, design spec and plan
```

## Request flow

1. **Door** (`src/Talis/Doors/*`, all extend `aDoor`) parses its input into URL parts, a JSON body (`?\stdClass`) and the URI, then `aDoor::dispatch()` runs `(new TalisMain)->begin($parts, $body, $uri)->nextLinkInChain()->render($emitter)`. Every door takes its emitter in the constructor (defaults: `Renderers\HTTP` for HTTP/Rest, `Renderers\Cli` for Cli/CliEmbedded, `Renderers\Log` for Daemon). Bad input gives a 400; `ParamNotFound`/`InvalidParam` give a 400; any other `Throwable` gives a 500. A `render()` that throws must not have sent output yet.
   - `Rest` maps the HTTP method onto the 3rd URI segment: GET→`read`, POST→`create`, PUT→`update`, PATCH→`partialupdate`, DELETE→`delete`, HEAD→`head`, OPTIONS→`options`.
   - HTTP/Rest skip JSON decoding for form bodies (urlencoded, multipart); the data stays in `$_POST`.
   - `Cli::gogogo()` returns an exit code (0 ok, 1 error). `Daemon::gogogo(string $rawJson)` takes `{"url": ..., ...}`; the body is the message minus `url`. `SpoolWorker` feeds spool files (`incoming/ → processing/ → done/|failed/`) to the Daemon door.
2. **TalisMain** creates a new `Context` (`TalisMain::$context`, with `get/set/has`) and a `Response`, asks the router (`TalisMain::$registeredRouter`, default `Router\DefaultRouter`) for the chain head and runs the optional `TalisMain::$registeredInitFunc` closure. A `BadUri` becomes a `Chain\Errors\ApiNotFound` (404) head.
3. **DefaultRouter**: `/{a}/{b}/{c}` loads `TalisMain::$appPath . "/api/{a}/{b}/{c}.php"` and builds class `\Api\{a}{b}{c}` (case-insensitive). The three segments must match `^[A-Za-z0-9_]+$` and the class must extend `aChainLink`. Extra segments are key/value pairs merged with `$_GET`.
4. **Chain** (`aChainLink::nextLinkInChain()`) calls `process()`. If it returns `$this` and the `ChainContainer` is not empty, it pops the next `ChainEntry` (`[class-string<aChainLink>|callable, array params]`), builds it with the shared Request/Response and goes on. A callable is wrapped in `FunctionalChainlinkWrapper`. A bad entry throws `InvalidChainEntry`. The last link must implement `commons\iRenderable`, or `TalisMain` throws `LastLinkNotRenderable`.

## Writing an API endpoint

API classes extend `Chain\aFilteredValidatedChainLink`, live in `<appPath>/api/{a}/{b}/{c}.php` under namespace `Api`, and declare:
- `protected array $filters` — `list<array{0: class-string<Filters\aFilter>, 1: array<mixed>}>`; filters change the Request.
- `protected array $dependencies` — `list<array{0: class-string<Dependencies\aDependency>, 1: array<mixed>}>`; each implements `validate(): bool` and renders its own 400-style failure.
- `protected function getNextBl(): array` — `@return list<ChainEntry>` (`@phpstan-import-type ChainEntry from \Talis\Chain\ChainContainer`). End with `DoneSuccessful`, `PayloadToJson200`, `ResourceCreated`, etc., or your own renderable link.

To stop with an error (or any renderable link) from inside a link, `return $this->bailOut(new Errors\Xxx($this->Request, $this->Response, [...]));`. `appendChainLink()` adds links at run time. `aError` subclasses set `protected int $httpCode`; there must be a matching `Message\Status\CodeNNN` class or it falls back to 500.

## Rules for code in this repo

- PHP 8.5 only. Every PHP file, including extension-less scripts like `demo/doors/cli/lord_commander`, starts with `declare(strict_types=1);`.
- Native types on every property, parameter and return. `#[\Override]` on every override and interface method.
- phpstan level `max` on `src`, `tests`, `demo`. No `ignoreErrors`, no baseline, no `@phpstan-ignore`.
- 100% line coverage of `src/`. Prefer small overridable seams (`readInput()`, `sendHeader()`, an injected emitter) to `@codeCoverageIgnore`. Any ignore is listed with its reason in `tests/README.md`.
- **Logging only through `\Talis\TalisMain::logger()->debug/info/warning/error/fatal(...)`.** No global `dbg()`/`info()`/`error()` helpers anywhere. `TalisMain::logger()` falls back to `NullLogger`. Tests use `TalisMain::setLogger(new SpyLogger())` and reset to `NullLogger` in tearDown.
- `iLogger` keeps its exact methods: `debug(mixed $inp): void` and `info|warning|error|fatal(mixed $inp, bool $full_stack): void`.
- Naming: methods and properties camelCase, constants UPPER_SNAKE_CASE. Kept house rules: the `a` prefix on abstract classes (`aChainLink`), the `i` prefix on interfaces (`iRenderable`), `CodeNNN` status classes, the lowercase `commons` namespace, `gogogo()` on doors, and capitalized `$Request` / `$Response`.
- v5 names to use (v4 names are gone, no aliases): `getNextBl`, `bailOut`, `formatHumanMessage`, `$httpCode`, `$chainContainer`, `nextLinkInChain`, `appendChainLink`, `setLogger`, `$appPath`, `$registeredRouter`, `$registeredInitFunc`, `getParamNull/getParamDefault/getParamExists/getParamOrFail`, `DoneSuccessful`, `AccessForbidden`, `CliEmbedded`, `ResponseType` enum.
- Comments and docblocks are short, plain English and say what or why. Delete docblocks that only repeat the signature.
- A user-visible change gets an entry in `DOCS/RELEASE-NOTES.md`.
