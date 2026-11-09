# Errors

An error link ends a chain and tells the caller something went wrong. It is
the opposite of a Dependency: it never checks anything, it just renders.

Each error class extends `aError` and sets:

- `$httpCode`: the HTTP status code to send (for example 404). `aError`
  looks up `\Talis\Message\Status\Code{httpCode}` to build the status. If
  there is no such class, it logs an error and falls back to a 500.
- `formatHumanMessage()`: returns the message shown to the caller. It can
  use `$this->Request` (for example to mention the URI) and `$this->params`.

`aError::render()` logs the error, sets the Response's message and status,
marks the Response as an error, and emits it.

## Adding a new error

1. Create a class in this folder that extends `aError`.
2. Set `$httpCode` to the status you want.
3. Implement `formatHumanMessage()`.

That is enough for most cases. If you need a status this library does not
ship, or something more involved, override `buildStatus()` (to build a
custom status while keeping the rest of `render()`) or `render()` itself
(to take over fully).

## A word of caution

Some chains decide partway through that the request should get an error
response instead of continuing (for example, an auth check after some work
already ran). Rendering at that point still sends whatever was built into
the Response so far. In general, do checks that can end in an error as
early in the chain as possible.
