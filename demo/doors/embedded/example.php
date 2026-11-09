<?php

declare(strict_types=1);

/**
 * Shows calling Talis from inside another PHP script, through
 * CliEmbedded. No HTTP, no shell-out: just build a body and call
 * gogogo(). The default emitter (Renderers\Cli) prints each Response.
 *
 * Run with: php demo/doors/embedded/example.php
 */

require __DIR__ . '/../../config/bootstrap.php';

use Talis\Doors\CliEmbedded;

$door = new CliEmbedded();

echo "Creating a user...\n";
$createBody = (object) ['params' => (object) ['name' => 'Ann', 'email' => 'ann@example.com']];
$created = $door->gogogo('/demo/user/create', $createBody);

$payload = $created->getPayload();
$id = is_array($payload) ? ($payload['id'] ?? null) : null;
if (!is_int($id)) {
    fwrite(STDERR, "Create did not return a user id; stopping.\n");
    exit(1);
}

echo "\nReading the user back...\n";
$door->gogogo("/demo/user/read/id/{$id}", new \stdClass());
