<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Demo;

use Demo\User\UserStore;
use PHPUnit\Framework\TestCase;

final class UserStoreTest extends TestCase
{
    private string $dir = '';

    private string $file = '';

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        // The demo classes are loaded by the demo bootstrap; here we only need the store.
        require_once __DIR__ . '/../../../demo/application/model/User/UserStore.php';
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/talis-userstore-' . bin2hex(random_bytes(6));
        $this->file = $this->dir . '/data/users.json';
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
        foreach ([$this->dir . '/data', $this->dir] as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testMissingFileIsEmpty(): void
    {
        $store = new UserStore($this->file);

        self::assertSame([], $store->all());
        self::assertNull($store->find(1));
    }

    public function testEmptyFileIsEmpty(): void
    {
        mkdir($this->dir . '/data', 0777, true);
        file_put_contents($this->file, '');
        $store = new UserStore($this->file);

        self::assertSame([], $store->all());
        self::assertSame(['id' => 1, 'name' => 'Ann', 'email' => 'ann@example.com'], $store->create('Ann', 'ann@example.com'));
    }

    public function testCreateAssignsIncreasingIds(): void
    {
        $store = new UserStore($this->file);

        $ann = $store->create('Ann', 'ann@example.com');
        $bob = $store->create('Bob', 'bob@example.com');
        $store->delete(2);
        $cid = $store->create('Cid', 'cid@example.com');

        self::assertSame(1, $ann['id']);
        self::assertSame(2, $bob['id']);
        self::assertSame(3, $cid['id'], 'ids are never reused');
        self::assertSame([$ann, $cid], $store->all());

        $raw = json_decode((string) file_get_contents($this->file), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['next_id' => 4, 'users' => ['1' => $ann, '3' => $cid]], $raw);
    }

    public function testFindUpdateDelete(): void
    {
        $store = new UserStore($this->file);
        $store->create('Ann', 'ann@example.com');

        self::assertSame(['id' => 1, 'name' => 'Ann', 'email' => 'ann@example.com'], $store->find(1));

        $updated = $store->update(1, ['name' => 'Anna']);
        self::assertSame(['id' => 1, 'name' => 'Anna', 'email' => 'ann@example.com'], $updated);

        $updated = $store->update(1, ['email' => 'anna@example.com']);
        self::assertSame(['id' => 1, 'name' => 'Anna', 'email' => 'anna@example.com'], $updated);
        self::assertSame($updated, (new UserStore($this->file))->find(1));

        self::assertTrue($store->delete(1));
        self::assertNull($store->find(1));
        self::assertSame([], $store->all());
    }

    public function testUpdateUnknownIsNull(): void
    {
        $store = new UserStore($this->file);
        $store->create('Ann', 'ann@example.com');

        self::assertNull($store->update(7, ['name' => 'Nobody']));
        self::assertSame([['id' => 1, 'name' => 'Ann', 'email' => 'ann@example.com']], $store->all());
    }

    public function testDeleteUnknownIsFalse(): void
    {
        $store = new UserStore($this->file);
        $store->create('Ann', 'ann@example.com');

        self::assertFalse($store->delete(7));
        self::assertCount(1, $store->all());
    }

    public function testCorruptFileThrowsAndIsNotOverwritten(): void
    {
        mkdir($this->dir . '/data', 0777, true);
        $bytes = '{"next_id": 3, "users": {';
        file_put_contents($this->file, $bytes);
        $store = new UserStore($this->file);

        foreach ([
            static fn (): mixed => $store->all(),
            static fn (): mixed => $store->find(1),
            static fn (): mixed => $store->create('Ann', 'ann@example.com'),
            static fn (): mixed => $store->update(1, ['name' => 'Ann']),
            static fn (): mixed => $store->delete(1),
        ] as $call) {
            try {
                $call();
                self::fail('Expected a RuntimeException');
            } catch (\RuntimeException $e) {
                self::assertSame("Data file {$this->file} is not valid JSON. Not touching it.", $e->getMessage());
            }
        }

        self::assertSame($bytes, file_get_contents($this->file));
    }

    public function testWrongShapeThrowsAndIsNotOverwritten(): void
    {
        mkdir($this->dir . '/data', 0777, true);
        $bytes = '{"next_id": 2, "users": {"1": {"id": 1, "name": "Ann"}}}';
        file_put_contents($this->file, $bytes);
        $store = new UserStore($this->file);

        try {
            $store->create('Bob', 'bob@example.com');
            self::fail('Expected a RuntimeException');
        } catch (\RuntimeException $e) {
            self::assertSame("Data file {$this->file} does not hold users in the expected shape. Not touching it.", $e->getMessage());
        }

        self::assertSame($bytes, file_get_contents($this->file));
    }

    public function testUnicodeRoundTrip(): void
    {
        $store = new UserStore($this->file);
        $store->create('Ünal Şahin', 'unal@example.com');

        self::assertSame('Ünal Şahin', (new UserStore($this->file))->find(1)['name'] ?? null);
        self::assertStringContainsString('Ünal Şahin', (string) file_get_contents($this->file));
    }

    public function testConcurrentCreatesFromForkedProcessesKeepEveryUser(): void
    {
        $store = new UserStore($this->file);
        $children = [];

        for ($c = 0; $c < 4; $c++) {
            $pid = pcntl_fork();
            self::assertNotSame(-1, $pid, 'fork failed');
            if ($pid === 0) {
                $code = 0;
                try {
                    $childStore = new UserStore($this->file);
                    for ($i = 0; $i < 25; $i++) {
                        $childStore->create("child{$c}-{$i}", "c{$c}i{$i}@example.com");
                    }
                } catch (\Throwable) {
                    $code = 1;
                }
                exit($code);
            }
            $children[] = $pid;
        }

        foreach ($children as $pid) {
            $status = 0;
            pcntl_waitpid($pid, $status);
            self::assertIsInt($status);
            self::assertTrue(pcntl_wifexited($status));
            self::assertSame(0, pcntl_wexitstatus($status));
        }

        $all = $store->all();
        self::assertCount(100, $all);
        self::assertSame(range(1, 100), array_column($all, 'id'));
        self::assertCount(100, array_unique(array_column($all, 'email')));
    }
}
