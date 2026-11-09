# TalisMS

TalisMS is a small PHP framework for building APIs and middleware-style services.
A request comes in through a **door** (Rest, HTTP, Cli, CliEmbedded or Daemon)
and runs through a **chain of links**: filters first, then dependencies
(validators), then your business-logic links. The last link renders the
response. The same API classes work behind every door.

v5 needs PHP 8.5 or later.

```
composer require director-moav/talis-ms
```

- Upgrading from v4? Read the [release notes](DOCS/RELEASE-NOTES.md). They have the rename tables, sed rules and behaviour changes.
- Want to see it run? The [demo app](demo/README.md) serves one user CRUD resource through every door, with copy-paste commands.

## Layout

```
src/Talis/                the library (what you get from Composer)
  Chain/                  chain links, filters, dependencies, errors, result links
  Doors/                  Rest, HTTP, Cli, CliEmbedded, Daemon, SpoolWorker
  Message/                Request, Response, ResponseType, status codes, renderers
  Router/                 aRouter and DefaultRouter
  Exception/              Talis exceptions
  commons/                interfaces (iLogger, iEmitter, iRenderable, iFilter) and NullLogger
  TalisMain.php           starts a request: router, init hook, logger
  Context.php             shared process state (get/set/has)
tests/Unit/               one test class per src class
tests/Integration/        the demo app driven through every door
tests/Support/            spy logger and spy emitter for tests
tests/Fixtures/           small API classes and routers used by the tests
demo/                     a small runnable app (see demo/README.md)
bin/check-coverage.php    fails the build if line coverage of src/ is under 100%
DOCS/RELEASE-NOTES.md     release notes and the v4 to v5 migration guide
```

## How a request runs

1. A door turns its input into URL parts and a JSON body, then calls
   `(new TalisMain)->begin($parts, $body, $uri)->nextLinkInChain()->render($emitter)`.
2. `DefaultRouter` maps `/{a}/{b}/{c}` to `api/{a}/{b}/{c}.php` under
   `TalisMain::$appPath` and to the class `\Api\{a}{b}{c}`. Extra segments are
   read as key/value pairs and merged with `$_GET`.
3. The API class extends `Chain\aFilteredValidatedChainLink` and lists its
   `$filters`, `$dependencies` and `getNextBl()` links. A dependency that fails
   ends the chain with a 400-style response. `bailOut()` ends it with any
   renderable link.

## Development

```
composer install
composer test        # all tests
composer stan        # phpstan, level max, over src, tests and demo
composer coverage    # tests with coverage; fails under 100% of src/
```

See [tests/README.md](tests/README.md) for the test suites.
