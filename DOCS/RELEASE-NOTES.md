# TalisMS release notes

## v5.0.0

### Summary

v5 is a rewrite of the library for PHP 8.5 and up. Nothing older is supported.

- Every file has `declare(strict_types=1)`, native types and `#[\Override]`.
- Methods and properties are camelCase. Spelling mistakes in class names are fixed.
- phpstan runs at level `max` with no ignores. Tests cover 100% of the lines in `src/`.
- It is a clean break: old names are gone. There are no deprecation shims, aliases or wrappers. Use the tables and sed rules below to move your app.

### Before you upgrade

- Your app must run on PHP 8.5 or later.
- Change your Composer pin to `"director-moav/talis-ms": "^5.0"` and run `composer update director-moav/talis-ms`.
- The library is now loaded with PSR-4 (`Talis\` → `src/Talis/`). If your app added Talis's `src/` to `include_path` to load it, you can drop that.
- Work on a branch. Run the sed rules, then your own phpstan and tests, then read the behaviour changes and check each one in your code.
- Your `iLogger` adapter needs no change. `iLogger` keeps the exact same methods. Only the setter is renamed (`set_logger()` → `setLogger()`).
- Setting a logger is now optional. Without one, `TalisMain::logger()` returns a `NullLogger`.
- `SHOW_EXCEPTIONS` is no longer read. Pass `showExceptions: true` to the `HTTP` or `Rest` door constructor instead.

### Rename tables

The `a` prefix on abstract classes, the `i` prefix on interfaces, the `CodeNNN` status classes, the `commons` namespace, `gogogo()` and the capitalized `$Request` and `$Response` all stay as they were.

#### Classes and constants

| v4 | v5 |
|---|---|
| `\Talis\Chain\DoneSuccessfull` | `\Talis\Chain\DoneSuccessful` |
| `\Talis\Chain\Errors\AccessForbbiden` | `\Talis\Chain\Errors\AccessForbidden` |
| `\Talis\Doors\CliEmbeded` | `\Talis\Doors\CliEmbedded` |
| `\Talis\Chain\Dependencies\ADependency` (wrong case) | `\Talis\Chain\Dependencies\aDependency` |
| `\Talis\Message\Response::RESPONSE_TYPE__RESPONSE` | `\Talis\Message\ResponseType::Response` |
| `\Talis\Message\Response::RESPONSE_TYPE__DEPENDENCY` | `\Talis\Message\ResponseType::Dependency` |
| `\Talis\Message\Response::RESPONSE_TYPE__ERROR` | `\Talis\Message\ResponseType::Error` |
| `\Talis\commons\NullLogger` in `iLogger.php` | same class, now in `NullLogger.php` (no change needed) |
| `\Talis\commons\iEmitter`, `iRenderable`, `iFilter`, `iLogger` | unchanged; implementers may add `#[\Override]` |
| `\Talis\Message\aStatus` subclasses (custom codes) | unchanged; `$code` and `$msg` stay protected and overridable |

#### Methods

| v4 | v5 | Notes |
|---|---|---|
| `Request::get_param_null()` | `Request::getParamNull()` | |
| `Request::get_param_default()` | `Request::getParamDefault()` | the default must be a string |
| `Request::get_param_exists()` | `Request::getParamExists()` | same as `getParamOrFail()` now |
| `Request::get_param_or_fail()` | `Request::getParamOrFail()` | |
| `Request::get_param()` | `Request::getParamNull()` or `Request::getParamDefault()` | removed; see "Removed APIs" |
| `aFilteredValidatedChainLink::get_next_bl()` | `getNextBl()` | |
| `aFilteredValidatedChainLink::load_chain_container()` | `loadChainContainer()` | |
| `aChainLink::bail_out()` | `bailOut()` | |
| `aChainLink::set_chain_container()` | `setChainContainer()` | |
| `aChainLink::append_chainlink()` | `appendChainLink()` | also takes a callable; returns `static` |
| `aChainLink::clone_response()` | `cloneResponse()` | |
| `aChainLink::nextLinkInchain()` | `aChainLink::nextLinkInChain()` | |
| `TalisMain::nextLinkInchain()` | `TalisMain::nextLinkInChain()` | |
| `aError::format_human_message()` | `formatHumanMessage()` | |
| `TalisMain::set_logger()` | `TalisMain::setLogger()` | |
| `Context::resource($name, $value)` | `Context::set()` / `get()` / `has()` | not a plain rename; see "Removed APIs" |
| `aRouter::generate_route()` | `generateRoute()` | custom routers only |
| `aRouter::generate_query()` | `generateQuery()` | custom routers only; returns `array<array-key, mixed>` |
| `aRouter::get_chainhead()` | `getChainHead()` | custom routers only |
| `HTTP::get_uri_from_server()` | `getUriFromServer()` | door subclasses only; add `#[\Override]` and `@return list<string>` |
| `HTTP::get_request_body()` | `getRequestBody()` | door subclasses only; the raw body is read by `readInput()` |
| `Response::getResponseType(): string` | `Response::getResponseType(): ResponseType` | returns the enum; use `->value` for the string |
| `Cli::gogogo(): void` | `Cli::gogogo(): int` | returns an exit code; see "Behaviour changes" |
| `Rest::gogogo(string): void`, `HTTP::gogogo(string): void` | `gogogo(string $rootUri): Response` | you can ignore the return value |
| `CliEmbeded::gogogo(): void` | `CliEmbedded::gogogo(): Response` | |
| `Daemon::gogogo(string)` (base64) | `Daemon::gogogo(string $rawJson): Response` | see "Behaviour changes" |

#### Properties

| v4 | v5 | Notes |
|---|---|---|
| `TalisMain::$APP_PATH` | `TalisMain::$appPath` | your own global `APP_PATH` constant is not touched |
| `TalisMain::$registered_init_func` | `TalisMain::$registeredInitFunc` | typed `?\Closure` |
| `TalisMain::$registered_router` | `TalisMain::$registeredRouter` | `class-string` of an `aRouter` subclass |
| `TalisMain::$Context` | `TalisMain::$context` | |
| `aError::$http_code` | `aError::$httpCode` | default is now 500 (was 0) |
| `aChainLink::$chain_container` | `aChainLink::$chainContainer` | |
| `HTTP::$full_uri` | `HTTP::$fullUri` | door subclasses only |
| `HTTP::$root_uri` | `HTTP::$rootUri` | door subclasses only |
| `aRouter::$request_parts` | `aRouter::$requestParts` | custom routers only |
| `aRouter::$route` `['route', 'classname']` | `DefaultRouter::$route` `['file', 'className']` | moved to `DefaultRouter`, keys renamed |

#### Parameter names

Only calls that use named arguments need a change.

| v4 | v5 |
|---|---|
| `new Request(string $full_uri, array $get_params, ?\stdClass $body)` | `new Request(string $fullUri, array $getParams = [], ?\stdClass $body = null)` |
| `Request::addToBodyParams($k, $v)` | `Request::addToBodyParams(string $key, mixed $value)` |
| `BadUri`, `ClassNotFound`, `FileNotFound`, `ParamNotFound` constructors | typed: `(string ..., ?\Throwable $previous = null)` |

### sed rules

All rules are `sed -E` (ERE). They work with both GNU sed and BSD (macOS) sed. Word boundaries are written as `(^|[^A-Za-z0-9_])X([^A-Za-z0-9_]|$)`, which every sed understands.

Save the blocks below as one file, `talis-v5.sed`, and run it over your PHP files. `-i.bak` works the same on GNU and BSD sed:

```
grep -rlE 'Successfull|Forbbiden|Embeded|ADependency|RESPONSE_TYPE__|get_param|get_next_bl|bail_out|format_human_message|http_code|chain_container|clone_response|nextLinkInchain|set_chain_container|append_chainlink|TalisMain::|generate_route|generate_query|get_chainhead|get_uri_from_server|get_request_body|full_uri|root_uri|Doors.Cli' --include='*.php' . \
  | xargs sed -E -i.bak -f talis-v5.sed
```

Also run it on PHP scripts without a `.php` extension, such as a CLI entry script. Run the script twice: every rule is safe to repeat, and the second run catches two hits that are only one character apart. Then delete the `.bak` files.

Class names and constants:

```sed
s/DoneSuccessfull/DoneSuccessful/g
s/AccessForbbiden/AccessForbidden/g
s/CliEmbeded/CliEmbedded/g
s/Dependencies\\ADependency([^A-Za-z0-9_]|$)/Dependencies\\aDependency\1/g
s/(^|[^A-Za-z0-9_\\])ADependency([^A-Za-z0-9_]|$)/\1aDependency\2/g
s/Response::RESPONSE_TYPE__RESPONSE/ResponseType::Response/g
s/Response::RESPONSE_TYPE__DEPENDENCY/ResponseType::Dependency/g
s/Response::RESPONSE_TYPE__ERROR/ResponseType::Error/g
```

If you wrote `Response::RESPONSE_TYPE__ERROR` after a `use \Talis\Message\Response;`, add `use \Talis\Message\ResponseType;` too.

Request getters:

```sed
s/->get_param_null\(/->getParamNull(/g
s/->get_param_default\(/->getParamDefault(/g
s/->get_param_exists\(/->getParamExists(/g
s/->get_param_or_fail\(/->getParamOrFail(/g
```

Chain links:

```sed
s/(^|[^A-Za-z0-9_])get_next_bl([^A-Za-z0-9_]|$)/\1getNextBl\2/g
s/(^|[^A-Za-z0-9_])bail_out([^A-Za-z0-9_]|$)/\1bailOut\2/g
s/(^|[^A-Za-z0-9_])format_human_message([^A-Za-z0-9_]|$)/\1formatHumanMessage\2/g
s/->chain_container([^A-Za-z0-9_]|$)/->chainContainer\1/g
s/(^|[^A-Za-z0-9_])clone_response([^A-Za-z0-9_]|$)/\1cloneResponse\2/g
s/nextLinkInchain/nextLinkInChain/g
s/(^|[^A-Za-z0-9_])set_chain_container([^A-Za-z0-9_]|$)/\1setChainContainer\2/g
s/(^|[^A-Za-z0-9_])append_chainlink([^A-Za-z0-9_]|$)/\1appendChainLink\2/g
s/(^|[^A-Za-z0-9_])load_chain_container([^A-Za-z0-9_]|$)/\1loadChainContainer\2/g
```

`$http_code` in `aError` subclasses. This rule only touches `protected int $http_code` and `@var int $http_code`, so other classes with their own `http_code` are left alone. If an `aError` subclass reads `$this->http_code`, rename that by hand:

```sed
s/(protected int[[:space:]]+|@var int(eger)?[[:space:]]+)\$http_code([^A-Za-z0-9_]|$)/\1$httpCode\3/g
```

TalisMain statics and setter. These are anchored on `TalisMain::`, so your own `APP_PATH` constant is not touched:

```sed
s/TalisMain::\$APP_PATH([^A-Za-z0-9_]|$)/TalisMain::$appPath\1/g
s/TalisMain::set_logger\(/TalisMain::setLogger(/g
s/TalisMain::\$registered_init_func([^A-Za-z0-9_]|$)/TalisMain::$registeredInitFunc\1/g
s/TalisMain::\$registered_router([^A-Za-z0-9_]|$)/TalisMain::$registeredRouter\1/g
s/TalisMain::\$Context([^A-Za-z0-9_]|$)/TalisMain::$context\1/g
```

Custom routers and door subclasses. The `->full_uri` and `->root_uri` rules only match the property form, so a config key such as `['root_uri']` is not touched. Still, run them only on your door subclasses if other classes in your app have a `full_uri` or `root_uri` property:

```sed
s/function generate_route\(/function generateRoute(/g
s/function generate_query\(/function generateQuery(/g
s/function get_chainhead\(/function getChainHead(/g
s/(^|[^A-Za-z0-9_])get_uri_from_server([^A-Za-z0-9_]|$)/\1getUriFromServer\2/g
s/(^|[^A-Za-z0-9_])get_request_body([^A-Za-z0-9_]|$)/\1getRequestBody\2/g
s/->full_uri([^A-Za-z0-9_]|$)/->fullUri\1/g
s/->root_uri([^A-Za-z0-9_]|$)/->rootUri\1/g
```

Do not rename `$request_parts` with sed. Only the property on a custom router changes; local variables with that name are fine as they are.

The CLI entry script. `Cli::gogogo()` now returns the exit code, so pass it to `exit()`. Without this change the script still runs, but always exits 0:

```sed
s/^\(new \\Talis\\Doors\\Cli\)->gogogo\((.*)\);$/exit((new \\Talis\\Doors\\Cli)->gogogo(\1));/
```

`Request::get_param()`. This rule is only right for calls with one argument. Check first with `grep -rn -- '->get_param(' .` and change any call with a default to `getParamDefault($key, $default)` by hand:

```sed
s/->get_param\(/->getParamNull(/g
```

### Removed APIs

| Removed | Use instead |
|---|---|
| `Request::get_param($key, $default)` | `getParamNull($key)`, or `getParamDefault($key, $default)` |
| `Response::setBody()` and the `$body` property | Nothing. It never changed the output, because `getBody()` builds the body from the status, type, message and payload. Use `setMessage()`, `setPayload()`, `setStatus()` and `markError()`. |
| `Response::RESPONSE_TYPE__*` constants | the `\Talis\Message\ResponseType` enum |
| `aStatus::dynamic_message()` and `$dyn_error_msg` | `Response::setMessage()` |
| `Context::resource($name, $value)` | `set($name, $value)` to store, `get($name, $default = null)` to read, `has($name)` to test |
| `Context::NaN` | `has()` |
| `\Talis\Chain\handleFunctionalChainlink()` | Nothing; it was dead code. Callables in a chain are wrapped in `FunctionalChainlinkWrapper`. |
| `aChainLink::$valid` | Nothing; it was never used. |
| `SHOW_EXCEPTIONS` constant | `new \Talis\Doors\HTTP(showExceptions: true)` or `new \Talis\Doors\Rest(showExceptions: true)` |
| `director-moav/zimlogger` dev dependency | Nothing. Bring your own `iLogger`. |

### Behaviour changes

Check each of these in your app.

- **Form bodies.** The `HTTP` and `Rest` doors do not JSON decode a body sent as `application/x-www-form-urlencoded` or `multipart/form-data`. The body is null and the data stays in `$_POST`, as in v4. Any other non-empty body that is not a JSON object now gives a 400 instead of a silent null body.
- **CLI bodies.** The `Cli` door gives a 400 for a body that is not a JSON object (for example `{n:1}`) or is not valid base64 when the base64 flag is set.
- **Cli exit code.** `Cli::gogogo()` returns `0` for a normal response and `1` for an error, a dependency failure, bad input or an exception. It never calls `exit()` itself. In v4 the script exited 0 unless an exception was thrown. If your entry script has `declare(strict_types=1)`, pass `isset($argv[3])` as the third argument, not the raw string.
- **Daemon door.** `Daemon::gogogo()` takes the raw JSON of one message: `{"url": "/a/b/c", "params": {...}}`. There is no base64. The Request body is the whole message minus `url`, so it is `{"params": {...}}`, the same shape the Rest and Cli doors send. In v4 the body was only the `params` value, so API classes that read the daemon body must now read `->params`, as they do for Rest and Cli. The default emitter is `Renderers\Log`, not `Renderers\HTTP`.
- **`GetFieldUInt`.** A missing field is now a 400 dependency failure (it was a warning and then a 500). Only a string of plain digits passes: `+5`, ` 5`, `1.0`, an array, and a value that a filter set as an `int` all fail with a 400.
- **`aError` 500 fallback.** `aError` builds the status class `Code{$httpCode}`. If that class does not exist, it logs an error and sends a 500. The default `$httpCode` is 500 (was 0).
- **`getStatus()` default.** `Response::getStatus()` returns a new `Code500` when no status was set (v4 threw an `Error`). It does not store it. Use `hasStatus()` to check.
- **`getParamExists()` throws.** `getParamExists()` and `getParamOrFail()` now do the same thing: a missing or null key throws `\Talis\Exception\ParamNotFound` (v4 gave a warning and then a `TypeError`). The doors turn an uncaught `ParamNotFound` into a 400.
- **Array GET params.** `getParamNull()`, `getParamDefault()`, `getParamExists()` and `getParamOrFail()` throw `\Talis\Exception\InvalidParam` when the value is an array or object (for example `?id[]=1`). Scalars are cast to string. The doors turn an uncaught `InvalidParam` into a 400. `getAllGetParams()` returns the values as they are (`array<array-key, mixed>`).
- **`getBodyParamExists()`** throws `ParamNotFound` for a missing key (v4 returned null with a warning). A key that is present with a null value still returns null.
- **`getBodyParams()`** casts a JSON array `params` to a `stdClass` (v4 threw a `TypeError`). A scalar `params` value throws `InvalidParam`.
- **`getPayload()`.** Only a null payload becomes a new `stdClass`. A falsy payload (`''`, `0`, `false`, `[]`) is returned as set. Code that calls `getPayload()->x` after setting a falsy payload must change.
- **`ResponseType`.** `getResponseType()` returns the enum. Compare with `=== ResponseType::Error`; use `->value` for the string. The JSON body still says `"type": "error"`.
- **`bailOut()`** returns `aChainLink` (was `aError`) and takes any `aChainLink&iRenderable`, so you can bail out with `DoneSuccessful` too. A good replacement for `$this->chain_container->clear(); return (new DoneSuccessfull(...))->process();` is `return $this->bailOut(new \Talis\Chain\DoneSuccessful($this->Request, $this->Response));`.
- **`nextLinkInChain()`** on `TalisMain` returns `aChainLink&iRenderable`. It throws `\Talis\Exception\LastLinkNotRenderable` if the last link cannot render (v4 had a `TypeError`).
- **Bad chain entries** throw `\Talis\Exception\InvalidChainEntry` (an entry that is not a callable or an `aChainLink` class, an abstract class, or a missing or non-string link param).
- **Routes.** `DefaultRouter` checks the first three URL segments against `^[A-Za-z0-9_]+$`. A segment with `-`, `.`, `%` and so on is now a 404. The extra key/value segments after them are not checked. The loaded class must extend `aChainLink`, or the route is a 404.
- **Root URI.** `HTTP` and `Rest` strip the root URI only when it is a whole path part: with root `/api`, `/api` and `/api/x/y/z` are stripped, but `/apiary/x/y` is left as is (and ends as a 404). v4 stripped any matching prefix.
- **Filters.** `AddGetQueryStringOverriding` and `FormPostIntoGetOverride` merge with `array_replace`, so numeric keys and array values are kept. The superglobal still wins.
- **`CLIOnly`** passes for any CLI SAPI, not only `./lord_commander`.
- **`registeredInitFunc`** must be a `\Closure` or null. A string or array callable must become a closure, for example `foo(...)`.
- **`Context`** stores falsy values (0, '', false, null).
- **Exception messages** are reworded: `Bad URI [x]`, `Param [x] not found`. Code that matches the old text must change.
- **Hidden exceptions.** With `showExceptions` off, a 500 says `An error has occurred.`. With it on, `TalisErrorStack` in the payload is a list of trace lines.
- **Invalid UTF-8** in an error message is scrubbed, so the 500 or 400 can still be sent as JSON.
- **JSON errors.** `PayloadToJson200`, `PayloadToString200` and the renderers encode with `JSON_THROW_ON_ERROR`. A payload that cannot be encoded (for example `INF`) throws `\JsonException`, and the door sends a 500. Nothing is sent before the encode, so the 500 is clean.
- **`Code403`** text is `Forbidden` (was `Forbbiden`). `Code200` keeps `Oook`.
- **`Renderers\HTTP`** sends every header (v4 dropped one when merging two lists). Its constructor takes an optional `?\Closure $sendHeader`, for tests.
- **`Request::__unserialize()`** only allows `stdClass` objects in the stored body.

### phpstan

The library is typed for phpstan level `max`, so phpstan in your app now checks your chain entries.

- A chain entry has the type alias `ChainEntry`, defined on `\Talis\Chain\ChainContainer`:
  `array{0: class-string<aChainLink>|(callable(Request, Response, array<mixed>): mixed), 1: array<mixed>}`.
- `aFilteredValidatedChainLink::$filters` is `list<array{0: class-string<\Talis\Chain\Filters\aFilter>, 1: array<mixed>}>`.
- `aFilteredValidatedChainLink::$dependencies` is `list<array{0: class-string<\Talis\Chain\Dependencies\aDependency>, 1: array<mixed>}>`.
- `getNextBl()` returns `list<ChainEntry>`.

The native type is still `array`, so your code runs without changes. phpstan will flag a filter entry that is not an `aFilter` class, a dependency entry that is not an `aDependency` class, and an old `@return array` on `getNextBl()`. Write the docblocks like this:

```php
/**
 * @phpstan-import-type ChainEntry from \Talis\Chain\ChainContainer
 */
class ShopItemUpdate extends \Talis\Chain\aFilteredValidatedChainLink
{
    /** @var list<array{0: class-string<\Talis\Chain\Filters\aFilter>, 1: array<mixed>}> */
    protected array $filters = [[\Talis\Chain\Filters\DefaultValueGet::class, ['field' => 'page', 'default' => '1']]];

    /** @var list<array{0: class-string<\Talis\Chain\Dependencies\aDependency>, 1: array<mixed>}> */
    protected array $dependencies = [[\Talis\Chain\Dependencies\GetFieldUInt::class, ['field' => 'id']]];

    /**
     * @return list<ChainEntry>
     */
    #[\Override]
    protected function getNextBl(): array
    {
        return [[\Talis\Chain\DoneSuccessful::class, []]];
    }
}
```

If you leave out the `@var` on `$filters` and `$dependencies`, phpstan uses the parent's type. Other things phpstan may now report:

- `ADependency` written with the wrong case in a docblock.
- `getResponseType()` compared with a string.
- A docblock that still says `bailOut()` returns `aError`.

`cloneResponse()` and `getResponse()` on the result of `TalisMain::nextLinkInChain()` need no `instanceof` check now, because the result is typed `aChainLink&iRenderable`.

### New

- **`\Talis\Doors\SpoolWorker`**: a long-running PHP worker that feeds spool files to the `Daemon` door. It watches `incoming/`, claims each `*.json` file with an atomic `rename()` into `processing/`, and moves it to `done/` or `failed/` (keeping its name). It stops cleanly on SIGTERM/SIGINT after the current file. `runOnce()` handles what is there and returns. Run it under systemd or supervisord. Files left in `processing/` after a crash are not retried; the worker logs a warning at start and you move them by hand. See `demo/doors/daemon/worker.php`.
- **`\Talis\Doors\aDoor`**: the shared base of every door. Every door takes its emitter in the constructor, with the old renderer as the default.
- **`\Talis\Message\ResponseType`**: an enum with `Response`, `Dependency` and `Error`.
- **`Context::get()`, `set()`, `has()`**.
- **`Response::hasStatus()`**.
- **Status classes `Code405`, `Code409` and `Code501`**.
- **`NullLogger` fallback**: `TalisMain::logger()` works without `setLogger()`.
- **Exceptions** `\Talis\Exception\InvalidParam`, `InvalidChainEntry` and `LastLinkNotRenderable`. All Talis exceptions take an optional `?\Throwable $previous`.
- **`HTTP::readInput()`**: override it to feed the request body in tests.
- **A demo app** in `demo/`: one user CRUD resource served through every door. See [demo/README.md](../demo/README.md).

### Deleted

- The Python daemon listener (`listener.py`, `environment.py`) and `the_devil.php`. Use `SpoolWorker`.
- The root `application/`, `config/` and `doors/` folders and the old `demo/*_client` folders. Their working parts are now the demo app in `demo/`.
- `.github/workflows/phpstan.yml`. CI is now `.github/workflows/ci.yml` (phpstan and tests with 100% line coverage).

### Outdated dependencies found

- `phpunit/phpunit` is pinned to `^12` (12.5 installed) and 13.x is out. The other packages `composer outdated` lists are phpunit's own dependencies and move with it. This is a dev dependency only; moving to 13 is left for a later release.
- `phpstan/phpstan ^2` and `phpstan/phpstan-phpunit ^2` are up to date.
- `director-moav/zimlogger` was removed from the dev dependencies; Talis no longer needs it.
