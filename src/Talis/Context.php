<?php

declare(strict_types=1);

namespace Talis;

/**
 * Shared state for the current process: resources, settings, anything that
 * is not part of the Request/Response and so is not sent to another system.
 */
class Context
{
    /**
     * @var array<string, mixed>
     */
    private array $resources = [];

    /**
     * Returns the stored value, or $default when has($name) is false.
     * A stored falsy value (0, '', false, null, ...) is returned as is.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        if (!$this->has($name)) {
            return $default;
        }

        return $this->resources[$name];
    }

    /**
     * Stores a value, falsy ones included. Overwrites any existing value.
     */
    public function set(string $name, mixed $value): void
    {
        $this->resources[$name] = $value;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->resources);
    }
}
