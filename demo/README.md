# Talis demo app

A small "user" CRUD resource, built once as one set of API classes
(`demo/application/api/demo/user/*.php`) and business-logic links
(`demo/application/model/User/`), then served through every door the
library ships. Run the same door twice (or two different doors) and you
get the same answer: the door only decides how a request comes in and
how the response goes out.

All commands below are run from the repo root, after `composer install`.

## What each door teaches

| Door | What it shows |
|---|---|
| Rest | one URL per resource, the HTTP method picks create/read/update/delete |
| HTTP | the method and action are both in the URL |
| Cli | the same chain, called from a shell, with a JSON body on the command line |
| CliEmbedded | calling Talis from inside another PHP script, no shell-out |
| Daemon / SpoolWorker | the same chain again, fed from files dropped in a folder |

## Rest

```
php -S 127.0.0.1:8002 demo/doors/standalone/router.php &

curl -s -X POST 127.0.0.1:8002/api/demo/user \
  -H 'Content-Type: application/json' \
  -d '{"params":{"name":"Ann","email":"ann@example.com"}}'
# -> 201, payload is the new user

curl -s 127.0.0.1:8002/api/demo/user/id/1
# -> 200, Ann

curl -s 127.0.0.1:8002/api/demo/user
# -> 200, payload.users is the full list

curl -s -X PUT 127.0.0.1:8002/api/demo/user/id/1 \
  -H 'Content-Type: application/json' \
  -d '{"params":{"name":"Ann Smith"}}'
# -> 204 No Content (curl shows no body for 204; GET the id to see the change)

curl -s -X DELETE 127.0.0.1:8002/api/demo/user/id/1
# -> 204 No Content

curl -s -X OPTIONS 127.0.0.1:8002/api/demo/user
# -> lists the allowed methods

kill %1   # stop the server
```

Open `http://127.0.0.1:8002/` in a browser for the same CRUD operations
through `demo/web/index.html` (list, create, edit, delete; vanilla JS,
no build step).

## HTTP

Same server as Rest; the URL carries the action instead of the HTTP method.

```
php -S 127.0.0.1:8002 demo/doors/standalone/router.php &

curl -s 127.0.0.1:8002/http/demo/user/read/id/1
curl -s 127.0.0.1:8002/http/demo/user/read
curl -s -X POST 127.0.0.1:8002/http/demo/user/create \
  -H 'Content-Type: application/json' \
  -d '{"params":{"name":"Bo","email":"bo@example.com"}}'

kill %1
```

## Cli

```
demo/doors/cli/lord_commander /demo/user/read '{}'
echo $?   # 0

demo/doors/cli/lord_commander /demo/user/create \
  '{"params":{"name":"Cy","email":"cy@example.com"}}'

demo/doors/cli/lord_commander /demo/user/read/id/1 '{}'
```

The 3rd argument, if present at all, means the 2nd argument is base64:

```
demo/doors/cli/lord_commander /demo/user/read/id/1 "$(echo -n '{}' | base64)" 1
```

## CliEmbedded

```
php demo/doors/embedded/example.php
```

Creates a user, then reads it back, both through `CliEmbedded` called
straight from PHP (no HTTP, no shelling out). Each call prints its own
`Response` (pretty JSON, then a `FIN with [code]` line).

## Daemon / SpoolWorker

```
mkdir -p demo/var/spool/incoming
cat > demo/var/spool/incoming/1.json.tmp <<'JSON'
{"url": "/demo/user/create", "params": {"name": "Daemon Dan", "email": "dan@example.com"}}
JSON
mv demo/var/spool/incoming/1.json.tmp demo/var/spool/incoming/1.json

php demo/doors/daemon/worker.php --once
# -> "Handled 1 file(s)"
ls demo/var/spool/done/   # 1.json is here on success, demo/var/spool/failed/ otherwise
```

`php demo/doors/daemon/worker.php` (no `--once`) runs until `SIGTERM`/`SIGINT`.

## Data and logs

- Users: `demo/var/data/users.json` (one JSON file, read/written under `flock`).
- Log: `demo/var/log/demo.log` (one line per log call).
- Spool: `demo/var/spool/{incoming,processing,done,failed}/`.

Set `TALIS_DEMO_VAR` to point all of the above somewhere else (the test
suite uses a temp folder).

## Reset

```
rm -rf demo/var
```

Every door recreates whatever it needs on the next request.
