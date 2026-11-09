# Doors

A door is the entry point of a request. It reads one kind of input
(a web request, CLI arguments, a queued message), turns it into request
parts, a body and a URI, runs the chain through `TalisMain`, and sends
the result out through an emitter.

Every door extends `aDoor`, which holds the shared logic:

- `dispatch()` runs the chain and renders the last link. It returns the
  `Response` that was sent. It never ends the process.
- A missing or bad request param (`ParamNotFound`, `InvalidParam`) thrown
  from the chain becomes a 400 error response.
- Any other exception is logged with `fatal()` and becomes a 500 error
  response.
- Bad input (bad JSON, bad base64, no url) gets a 400 error response and
  the chain is not run.
- A `render()` that throws must not have sent headers or output first, so
  the door can still send the 500.

## Emitter injection

Every door takes the emitter as its first constructor argument. The
default is the renderer the door always used:

| Door        | Default emitter                         |
|-------------|-----------------------------------------|
| HTTP, Rest  | `\Talis\Message\Renderers\HTTP`         |
| Cli         | `\Talis\Message\Renderers\Cli`          |
| CliEmbedded | `\Talis\Message\Renderers\Cli`          |
| Daemon      | `\Talis\Message\Renderers\Log`          |

Pass your own `iEmitter` to send the output somewhere else, or to keep it
quiet and read the returned `Response`.

## HTTP

```php
(new \Talis\Doors\HTTP())->gogogo('/talisroot');
```

The argument is the folder the door is served from (`''` for the domain
root). It is cut from the start of the request URI; a URI that does not
start with it is used as is. The path must hold the three route parts,
for example `/event/repeat/read`.

`new HTTP($emitter, showExceptions: true)` puts the exception text and
stack (`payload.TalisErrorStack`) into 500 responses. Keep it off in
production; the default message is `An error has occurred.`

## Rest

Same as HTTP, but the URI holds only two route parts and the HTTP method
gives the third: POST = create, GET = read, PUT = update,
PATCH = partialupdate, DELETE = delete, HEAD = head, OPTIONS = options.
Any other method is read.

## Form bodies (HTTP and Rest)

When the Content-Type is `application/x-www-form-urlencoded` or
`multipart/form-data`, the body is not JSON decoded. The Request body is
empty and the form data stays in `$_POST` (see the
`FormPostIntoGetOverride` filter). Any other non-empty body must be a JSON
object, or the door answers with a 400.

## Cli

```php
exit((new \Talis\Doors\Cli())->gogogo($url, $rawBody, $isBase64));
```

`$rawBody` is a JSON object, or the base64 of one when `$isBase64` is
true. `gogogo()` returns an exit code and does not call `exit()`:

- `Cli::EXIT_OK` (0): the final response is a normal response.
- `Cli::EXIT_ERROR` (1): an error or dependency response, bad input (400)
  or an exception (500).

## CliEmbedded

For calling Talis from inside other PHP code. The caller builds the body:

```php
$Response = (new \Talis\Doors\CliEmbedded($emitter))->gogogo('/a/b/c', $body);
```

## Daemon

Takes the raw JSON of one queued message (no base64):

```json
{"url": "/event/repeat/create", "params": {"id": 5}}
```

`url` picks the route. The rest of the object, without `url`, is the
Request body. An empty message, bad JSON or a missing url gives a 400.
`gogogo()` returns the `Response`, so the caller can tell success from
failure.
