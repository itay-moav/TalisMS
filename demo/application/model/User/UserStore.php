<?php

declare(strict_types=1);

namespace Demo\User;

/**
 * Keeps the demo users in one JSON file:
 * {"next_id": 3, "users": {"1": {...}, "2": {...}}}
 *
 * Every call opens the file and locks it (shared lock to read, exclusive
 * lock to change it), so several doors can use the same file at once.
 * A missing or empty file means "no users yet".
 *
 * @phpstan-type User array{id: int, name: string, email: string}
 * @phpstan-type Data array{next_id: int, users: array<int, User>}
 */
final class UserStore
{
    private const int JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    public function __construct(private readonly string $filePath)
    {
    }

    /**
     * @return list<User>
     */
    public function all(): array
    {
        return array_values($this->read()['users']);
    }

    /**
     * @return User|null
     */
    public function find(int $id): ?array
    {
        return $this->read()['users'][$id] ?? null;
    }

    /**
     * @return User
     */
    public function create(string $name, string $email): array
    {
        return $this->change(static function (array $data) use ($name, $email): array {
            $user = ['id' => $data['next_id'], 'name' => $name, 'email' => $email];
            $data['users'][$user['id']] = $user;
            $data['next_id']++;
            return [$data, $user];
        });
    }

    /**
     * @param array{name?: string, email?: string} $changes
     * @return User|null null when there is no such user
     */
    public function update(int $id, array $changes): ?array
    {
        return $this->change(static function (array $data) use ($id, $changes): array {
            if (!isset($data['users'][$id])) {
                return [$data, null];
            }
            $user = $data['users'][$id];
            $user['name'] = $changes['name'] ?? $user['name'];
            $user['email'] = $changes['email'] ?? $user['email'];
            $data['users'][$id] = $user;
            return [$data, $user];
        });
    }

    /**
     * @return bool false when there is no such user
     */
    public function delete(int $id): bool
    {
        return $this->change(static function (array $data) use ($id): array {
            if (!isset($data['users'][$id])) {
                return [$data, false];
            }
            unset($data['users'][$id]);
            return [$data, true];
        });
    }

    /**
     * @return Data
     */
    private function read(): array
    {
        $handle = $this->open(LOCK_SH);
        try {
            return $this->decode($handle);
        } finally {
            $this->close($handle);
        }
    }

    /**
     * Reads, changes and writes the data while holding the exclusive lock.
     * $edit gets the data and returns [the new data, the result for the caller].
     *
     * @template T
     * @param callable(Data): array{0: Data, 1: T} $edit
     * @return T
     */
    private function change(callable $edit): mixed
    {
        $handle = $this->open(LOCK_EX);
        try {
            [$data, $result] = $edit($this->decode($handle));
            $json = json_encode(['next_id' => $data['next_id'], 'users' => (object) $data['users']], self::JSON_FLAGS);
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $json . "\n");
            fflush($handle);
            return $result;
        } finally {
            $this->close($handle);
        }
    }

    /**
     * Opens (or creates) the file with "c+", so nothing is truncated before we hold the lock.
     *
     * @param LOCK_SH|LOCK_EX $lock
     * @return resource
     */
    private function open(int $lock)
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Can not create folder {$dir}");
        }

        $handle = fopen($this->filePath, 'c+');
        if ($handle === false) {
            throw new \RuntimeException("Can not open data file {$this->filePath}");
        }
        if (!flock($handle, $lock)) {
            fclose($handle);
            throw new \RuntimeException("Can not lock data file {$this->filePath}");
        }
        return $handle;
    }

    /**
     * @param resource $handle
     */
    private function close($handle): void
    {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * @param resource $handle
     * @return Data
     */
    private function decode($handle): array
    {
        rewind($handle);
        $raw = (string) stream_get_contents($handle);
        if (trim($raw) === '') {
            return ['next_id' => 1, 'users' => []];
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Data file {$this->filePath} is not valid JSON. Not touching it.", 0, $e);
        }

        return $this->checkShape($data);
    }

    /**
     * @return Data
     */
    private function checkShape(mixed $data): array
    {
        $bad = new \RuntimeException("Data file {$this->filePath} does not hold users in the expected shape. Not touching it.");
        if (!is_array($data) || !is_int($data['next_id'] ?? null) || !is_array($data['users'] ?? null)) {
            throw $bad;
        }

        $users = [];
        foreach ($data['users'] as $user) {
            if (!is_array($user) || !is_int($user['id'] ?? null) || !is_string($user['name'] ?? null) || !is_string($user['email'] ?? null)) {
                throw $bad;
            }
            $users[$user['id']] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
        }

        return ['next_id' => $data['next_id'], 'users' => $users];
    }
}
