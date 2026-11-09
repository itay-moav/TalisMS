# TalisMS v5: design

Date: 2026-11-09
Status: approved by owner 2026-11-09

## 1. Why

TalisMS was written for PHP 7. It still runs on PHP 8.5 today because Itay built it well. Several parts are broken:

- `HasBody` calls `count()` on a `stdClass`. Since PHP 8 that throws.
- The library calls global ZimLogger functions (`dbgn`, `dbgr`).
- The demo clients don't load. `rest_client/bootstrap.php` declares a function twice, and the sample `test/filter/read.php` endpoint is a fatal error when its class loads.
- The daemon door depends on a Python 2.7 listener.
- phpstan is pinned to `^1.11` locally and `^0.12` in CI. There are no tests.

v5 makes TalisMS a clean, typed, tested PHP 8.5 library. It also ships a small demo app that a developer can read in one sitting to learn how to build a TalisMS service.

Consumers: the owner's private apps, all pinned to v4.1.2. The owner migrates them to v5 after it is tagged. They do not need to keep working against v5 unchanged.

## 2. Goals

1. **PHP >= 8.5 only.** Nothing below 8.5 is supported.
2. **phpstan at level `max`** over `src/`, `tests/` and the demo code, with no `ignoreErrors` and no baseline.
3. **phpunit covering all of `src/`.** The target is 100% line coverage of `src/`, measured with pcov, which is installed locally. Any line that truly can't be covered is listed in the test docs with the reason.
4. **A demo app that does CRUD on users.** User records are stored as JSON in a single file on disk, and the app is reachable through **every door shipped in the repo**: Rest, HTTP, Cli, CliEmbedded and Daemon. The standalone `php -S` server is how the Rest and HTTP doors get served locally.
5. **`DOCS/RELEASE-NOTES.md`**, starting with v5.0.0. Each new release gets its own entry from then on. The v5.0.0 entry is a migration guide: every consumer-facing change as before → after → what to change in your app.
6. **Plain-English comments.** Every docblock and comment is short and simple, says what the code does or why, and uses no fancy language. Docblocks that only repeat the signature are removed.

## 3. Non-goals

- Message-queue doors for SNS, SQS or ActiveMQ. Those come later.
- Migrating the consumer apps. The owner does that after v5 is tagged.
- Changing `iLogger`'s methods. It stays exactly as it is, so the consumers' existing logger adapters keep working.
- A database or framework in the demo. Users go in a JSON file.

## 4. Anti-goals (failure even if it "works")

- Any `#[\Deprecated]` attribute, BC alias, `class_alias` or compatibility shim. v5 is a clean break.
- Calls to global logger shortcuts (`dbg`, `dbgn`, `dbgr`, `info`, `error`, ...) anywhere. All logging goes through `\Talis\TalisMain::logger()->method(...)`.
- Talis depending on ZimLogger or any other specific logger package.
- A demo that needs Docker, a message broker, Python or a JS build step to run.
- Docblocks written in heavy or showy language.

## 5. Constraints

- PHP 8.5.12 and Composer 2.11 are installed locally. pcov, pcntl and posix are available.
- `declare(strict_types=1);` goes in every PHP file in the repo.
- The owner may install or upgrade any Composer package. Any outdated dependency gets reported to the owner. If it is a `director-moav/*` package, the owner upgrades it.

## 6. Decisions made by the owner

### 6.1 Naming

Follow PHP-FIG (PSR-1, PSR-12 / PER Coding Style), **except for these Talis conventions, which stay:**

- The `a` prefix on abstract classes (`aChainLink`, `aError`, `aRouter`, `aStatus`, `aFilter`, `aDependency`, `aFilteredValidatedChainLink`).
- The `i` prefix on interfaces (`iLogger`, `iRenderable`, `iEmitter`, `iFilter`).
- Capitalized `$Request` and `$Response` properties and variables.
- Similar existing house conventions: the `CodeNNN` status class names, the lowercase `commons` namespace, and the `\Api\{a}{b}{c}` route class names.
- `gogogo()` keeps its name on every door.

Everything else follows the standard:

- **Methods** are camelCase. `get_param_exists` becomes `getParamExists`, `get_next_bl` becomes `getNextBl`, `set_logger` becomes `setLogger`, `format_human_message` becomes `formatHumanMessage`, `bail_out` becomes `bailOut`, `nextLinkInchain` becomes `nextLinkInChain`, and so on.
- **Properties** are camelCase. `$chain_container` becomes `$chainContainer`, `$http_code` becomes `$httpCode`, and so on. **Exception: `$Request` and `$Response` stay capitalized.**
- **Constants** are UPPER_SNAKE_CASE.
- **Spelling fixes:** `DoneSuccessfull` becomes `DoneSuccessful`, `AccessForbbiden` becomes `AccessForbidden`, `CliEmbeded` becomes `CliEmbedded`, `BLLinkMissingInChain` stays, and misspelled messages get corrected.

### 6.2 API break policy

- Clean break: rename and remove freely, with no deprecations.
- Precise phpstan types everywhere, including `aFilteredValidatedChainLink::$filters`, `$dependencies` and `getNextBl()`. Consumers will fix their own docblocks.
- `iLogger` keeps its exact method names and signatures: `debug(mixed $inp): void`, plus `info`, `warning`, `error` and `fatal` as `(mixed $inp, bool $full_stack): void`. Only its docblocks change. `TalisMain::setLogger()` takes it as before; the only change there is the camelCase method name.

### 6.3 Daemon door

- Pure PHP. The Python listener (`listener.py`, `environment.py`) and `the_devil.php` are deleted.
- A long-running PHP worker watches a **spool folder**. Each `.json` file dropped there holds one request: `{"url": "/demo/user/create", "params": {...}}`.
- The Request body is the whole decoded file minus `url`. So the body is `{"params": {...}}`, the same shape Rest and Cli send, and the same API classes work unchanged. v4 passed only `params` as the body; the migration guide notes this.
- `Daemon::gogogo(string $rawJson)` takes the raw file contents. Base64 is removed from the Daemon door (it was only there for the STOMP transport). No consumer calls the Daemon door today.
- The worker logic is a library class, `\Talis\Doors\SpoolWorker` in `src/`, so consumers get it through Composer as the replacement for the Python listener. `demo/doors/daemon/worker.php` is a thin script that builds it. Because it is in `src/`, it falls under the 100% coverage goal: signals, sleeping and file moves go through small overridable methods so tests can drive them.
- The worker:
  - claims each file with an atomic `rename()` into `processing/`;
  - passes it to `\Talis\Doors\Daemon::gogogo()`;
  - logs the response;
  - moves the file to `done/`, or to `failed/` if the response is an error or the file isn't valid JSON;
  - stops cleanly on SIGTERM/SIGINT through pcntl, after finishing the current file.
- It is meant to run under systemd or supervisord, and has no daemonizing code of its own.
- It takes a `--once` flag that processes what's in the folder and exits. Tests use it.
- The Daemon door no longer uses the HTTP renderer. It emits through a log/CLI emitter, and the worker can pass in its own emitter.

### 6.4 Release notes

- Location: `DOCS/RELEASE-NOTES.md`.
- The v5.0.0 entry at the top is the full migration guide for consumers, including complete rename tables (classes, methods, properties) and sed rules where a plain rename works.
- The file starts at v5.0.0. Past releases are not filled in (owner decision).

## 7. Library changes (src/)

This comes from the audit in the API-impact workflow. Each item is required unless marked optional.

### 7.1 Tooling

- `composer.json`:
  - `php: >=8.5`
  - PSR-4 autoload, `Talis\\` → `src/Talis/`
  - require-dev: `phpstan/phpstan ^2`, `phpunit/phpunit ^12` (latest stable), and `phpstan/phpstan-phpunit` if useful
  - `director-moav/zimlogger` removed
  - Composer scripts: `test`, `stan`, `coverage`
- `phpstan.neon.dist`: level `max`; paths `src`, `tests`, `demo`; no `ignoreErrors`.
- `phpunit.xml.dist`: `Unit` and `Integration` suites, source = `src`, pcov coverage.
- `.github/workflows/ci.yml` replaces `phpstan.yml`. It uses `actions/checkout@v4` and `shivammathur/setup-php` with PHP 8.5 and pcov, runs `composer install`, then phpstan, then phpunit. CI fails if coverage of `src/` is below 100% (`--coverage-text` plus a small threshold check, or `phpunit --coverage-*` with a check script).

### 7.2 Code-wide

- `declare(strict_types=1)` in every file. Native types on every property, parameter and return. `#[\Override]` on every override or interface implementation.
- Constructor promotion where it makes the code shorter. No `readonly` on properties that subclasses redeclare.
- Logging only through `TalisMain::logger()`. `TalisMain::logger()` falls back to a `NullLogger` (moved into its own file, `src/Talis/commons/NullLogger.php`) when no logger was set.

### 7.3 Specific fixes

- **Chain:**
  - `aChainLink::nextLinkInChain()` compares with `===`. It checks every chain entry: a callable gets wrapped in `FunctionalChainlinkWrapper`; a class-string of an `aChainLink` subclass gets instantiated; anything else throws a clear Talis exception.
  - `ChainContainer`: phpstan type alias for a chain entry, `[class-string<aChainLink>|callable, array<mixed>]`. `pop()` on an empty container throws instead of a TypeError.
  - `aDependency::process()` and `bailOut()` work even when no chain container is set.
  - `bailOut()` returns `aChainLink`.
  - The dead function `\Talis\Chain\handleFunctionalChainlink()` is deleted.
- **TalisMain:**
  - `nextLinkInChain()` checks that the final link is `iRenderable` and throws a clear Talis exception if it isn't. This replaces the TypeError and the old phpstan ignore.
  - `$registeredRouter` is typed `class-string<aRouter>`.
  - `$registeredInitFunc` is `?\Closure`.
- **DefaultRouter:**
  - Validates **only** the three route segments (`request_parts[1..3]`) against `^[A-Za-z0-9_]+$`, which closes the path-traversal hole. The extra key/value segments are not validated, because consumers pass `-`, `,` and `%20` in them.
  - Checks that the loaded class exists and is an `aChainLink`. Throws `BadUri` otherwise.
- **Request:**
  - Constructor promotion. camelCase method names.
  - `get_param()` is **removed**, since `getParamNull()` and `getParamDefault()` cover it.
  - Body-param methods get honest `mixed` types.
  - `__unserialize` limits `allowed_classes`.
- **Response:**
  - `getPayload()` only creates a `stdClass` when the payload is `null`, not when it's merely falsy.
  - `getStatus()`/`getBody()` are safe when no status was set.
  - The write-only `$body` and `setBody()` are removed.
  - New `ResponseType` enum. The `RESPONSE_TYPE__*` constants are removed in favour of the enum.
- **Status:** No `HttpStatus` enum.
  - `aError` keeps building the class name `Code{$httpCode}`. If that class doesn't exist, it logs an error through `TalisMain::logger()` and uses `Code500`. The default `$httpCode` is 500.
  - Add the missing common codes as `CodeNNN` classes: 405, 409 and 501.
  - The `CodeNNN` classes stay, and subclasses may still override `render()` with custom codes, as consumer apps do with their own custom status codes.
  - `aStatus::$code` and `$msg` stay overridable, not readonly.
  - The unused `dynamic_message` is removed.
- **Context:** `get()`, `set()` and `has()` replace `resource()`, which is removed. Falsy values are stored correctly.
- **Dependencies:**
  - `HasBody` works with a `stdClass` body.
  - `GetFieldUInt` returns a 400 dependency failure, not a 500, when the field is missing or isn't an unsigned int.
  - `CLIOnly` uses `PHP_SAPI`.
- **PayloadToString200 / PayloadToJson200:** encode safely with `JSON_THROW_ON_ERROR`.
- **Doors:**
  - HTTP and Rest strip the root URI with `str_starts_with` + `substr`. Rest's method mapping becomes a `match`.
  - All doors handle bad input (invalid JSON, invalid base64, a missing url) with a 400-style error response instead of a fatal.
  - The emitter can be passed in on every door, with each door's current renderer as the default.
- **Renderers\HTTP:** fixes the header-merge bug (`+` on two lists drops a header).
- **Exceptions:** typed constructors, plus an optional `?\Throwable $previous`.

### 7.4 Comments

Every file gets clean docblocks in plain English. The repeated "Responsebility: Parses the user input to identify the API class to instantiate" text in API classes is removed. Author and date tags can stay.

## 8. Repo layout

The repo root keeps only the library and its tooling. Everything app-shaped moves under `demo/`, so a developer sees one complete, runnable example app.

```
src/Talis/                 the library (the only thing consumers get via Composer)
tests/Unit/                one test class per src class
tests/Integration/         demo app driven end-to-end through every door
demo/
  README.md                how to run each door, with copy-paste commands
  config/                  bootstrap.php (sets APP_PATH, logger adapter, autoload)
  application/api/demo/user/{create,read,update,delete,options}.php
  application/model/User/  UserStore (JSON file + flock) and the BL chain links
  doors/
    standalone/router.php  php -S router: /api/* → Rest, /http/* → HTTP, else static web page
    rest/index.php         Rest door entry (for Apache/nginx)
    http/index.php         HTTP door entry (for Apache/nginx)
    cli/lord_commander     Cli door
    embedded/example.php   CliEmbedded door called from plain PHP code
    daemon/worker.php      spool-folder worker (Daemon door)
  web/index.html           tiny vanilla-JS page that does CRUD via the Rest door
  var/                     git-ignored runtime data: data/users.json, spool/{incoming,processing,done,failed}, logs
```

The old root folders `application/`, `config/` and `doors/`, and the old `demo/*_client` folders, are deleted. Their working parts move into `demo/`. The old `web_client` (Bootstrap 3, React 15 and Babel from CDNs) is replaced by the vanilla-JS page.

## 9. Demo app

### 9.1 Data

A user is `{ "id": int, "name": string, "email": string }`. Users are stored in `demo/var/data/users.json` as `{"next_id": N, "users": {"1": {...}}}`. `UserStore` reads and writes the file under `flock` (`LOCK_EX` for writes, `LOCK_SH` for reads), so two doors writing at once don't corrupt it. The file path comes from config, which lets tests point it at a temp folder.

### 9.2 API (one set of classes for every door)

| Route class | Rest call | Other doors use the URL | Chain |
|---|---|---|---|
| `Api\DemoUserCreate` | `POST /api/demo/user` body `{"params":{"name","email"}}` | `/demo/user/create` | deps: `BodyParamsFieldExist` name, email; a demo dependency checks the email format (422). BL: `CreateUser` → `ResourceCreated` (201, payload = new user) |
| `Api\DemoUserRead` | `GET /api/demo/user` (list) or `GET /api/demo/user/id/5` | `/demo/user/read[/id/5]` | dep: id is uint when present. BL: `ReadUser`/`ListUsers` → `PayloadToJson200`; missing id → `UserNotFound` (404) via `bailOut` |
| `Api\DemoUserUpdate` | `PUT /api/demo/user/id/5` body `{"params":{...}}` | `/demo/user/update/id/5` | deps: id uint, has body. BL: `UpdateUser` → `ResourceUpdated`; 404 if missing |
| `Api\DemoUserDelete` | `DELETE /api/demo/user/id/5` | `/demo/user/delete/id/5` | dep: id uint. BL: `DeleteUser` → `ResourceDeleted`; 404 if missing |
| `Api\DemoUserOptions` | `OPTIONS /api/demo/user` | n/a | `OptionsAllowed` |

The demo also shows two smaller patterns: one closure chain link (for example, trimming the name) and one filter.

### 9.3 Doors in the demo

| Door | How the developer runs it |
|---|---|
| Rest | `php -S localhost:8002 demo/doors/standalone/router.php` then `curl -X POST localhost:8002/api/demo/user -d '{"params":{"name":"Ann","email":"ann@example.com"}}'`, or open `localhost:8002/` for the web page |
| HTTP | same server: `curl localhost:8002/http/demo/user/read/id/1` |
| Cli | `demo/doors/cli/lord_commander /demo/user/create '{"params":{...}}'` (the optional 3rd arg means the body is base64) |
| CliEmbedded | `php demo/doors/embedded/example.php` shows calling Talis from inside another PHP script |
| Daemon | `php demo/doors/daemon/worker.php` (or `--once`); drop `demo/var/spool/incoming/x.json`, then see the result in the log and the file in `done/` |

`demo/README.md` walks through each door with copy-paste commands.

## 10. Tests

- **Unit (`tests/Unit`):** every class in `src/`. Doors are tested in-process: set `$_SERVER`, feed `php://input` through a seam (the door reads the body through an overridable method), and capture output with output buffering and a test emitter.
- **Integration (`tests/Integration`):** the demo app end to end through every door. Each test uses its own temp data file and temp spool folder.
  - Rest and HTTP over a real `php -S` process started in the test setup.
  - Cli through `proc_open` on `lord_commander`.
  - CliEmbedded in-process.
  - Daemon through `worker.php --once`.
  - Each covers create → read → update → delete, plus the error cases: missing fields, a bad email, an unknown id, a bad route.
- `composer test`, `composer stan` and `composer coverage` are the commands. CLAUDE.md is updated to match.

## 11. Left to the builder

- The exact phpstan type alias names, and the internal structure of the worker.
- How to reach 100% coverage on hard spots (`header()`, `exit`, signals). Prefer small seams, such as an injectable emitter or an overridable `readInput()`, over `@codeCoverageIgnore`. Any ignore is listed in the test docs with the reason.
- The visual style of the demo web page (keep it minimal).
- Report any outdated dependency found along the way.

## 12. Owner decisions on the review (2026-11-09)

1. Release notes start at v5.0.0. No entries for past releases.
2. All app-shaped code moves under `demo/`. Approved.
3. `ResponseType` enum, plus removing `Request::get_param()`, `Response::setBody()`, `Context::resource()` and `aStatus::dynamic_message()`. Approved.
4. **`$Request` and `$Response` stay capitalized** (a Talis house convention). Every other property and static follows camelCase: `TalisMain::$APP_PATH` becomes `$appPath`, `$Context` becomes `$context`, `$registered_router` becomes `$registeredRouter`, `$registered_init_func` becomes `$registeredInitFunc`, `$chain_container` becomes `$chainContainer`, `$http_code` becomes `$httpCode`, and so on. Capitalized local variables that hold objects, such as `$Response` in doors and `$NewLastChainLink`, may stay capitalized for consistency with `$Request`/`$Response`.
5. Vanilla-JS demo page. Approved.
6. Daemon door body is the whole file minus `url`; raw JSON; no base64; `SpoolWorker` lives in the library. Approved.
7. No `HttpStatus` enum. `Code{N}` lookup with a 500 fallback, plus `Code405`, `Code409` and `Code501`. Approved.
8. The spec lives in `DOCS/superpowers/specs/`, because `docs/` would collide with `DOCS/` on macOS.
