<?php

declare(strict_types=1);

namespace Talis\Tests\Unit\Doors;

use Talis\Doors\Daemon;
use Talis\Doors\SpoolWorker;
use Talis\Tests\Fixtures\Doors\ScriptedSpoolWorker;
use Talis\Tests\Fixtures\Doors\SpyDaemon;

final class SpoolWorkerTest extends DoorTestCase
{
    private const string OK = '{"url":"/fix/ping/echo","params":{"a":1}}';

    private string $dir = '';

    private SpyDaemon $door;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . '/talis-spool-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
        $this->door = new SpyDaemon($this->emitter);
    }

    #[\Override]
    protected function tearDown(): void
    {
        self::removeTree($this->dir);
        parent::tearDown();
    }

    private static function removeTree(string $path): void
    {
        if (is_file($path) || is_link($path)) {
            unlink($path);
            return;
        }
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) ?: [] as $name) {
            if ($name !== '.' && $name !== '..') {
                self::removeTree($path . '/' . $name);
            }
        }
        rmdir($path);
    }

    private function worker(int $idleSleepMs = 1000): ScriptedSpoolWorker
    {
        return new ScriptedSpoolWorker($this->dir, $this->door, $idleSleepMs);
    }

    private function drop(string $name, string $content, string $folder = SpoolWorker::INCOMING): string
    {
        $folderPath = $this->dir . '/' . $folder;
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0775, true);
        }
        file_put_contents($folderPath . '/' . $name, $content);
        return $folderPath . '/' . $name;
    }

    /**
     * @return list<string>
     */
    private function filesIn(string $folder): array
    {
        $names = array_values(array_diff(scandir($this->dir . '/' . $folder) ?: [], ['.', '..']));
        sort($names);
        return $names;
    }

    // Constructor and path

    public function testConstructorRejectsEmptySpoolDir(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SpoolWorker('');
    }

    public function testConstructorRejectsNegativeSleep(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SpoolWorker($this->dir, new Daemon(), -1);
    }

    public function testDefaultDoorIsDaemon(): void
    {
        $door = (new \ReflectionProperty(SpoolWorker::class, 'door'))->getValue(new SpoolWorker($this->dir));
        self::assertInstanceOf(Daemon::class, $door);
    }

    public function testPathJoinsFolder(): void
    {
        self::assertSame('/tmp/s/done', (new SpoolWorker('/tmp/s/'))->path(SpoolWorker::DONE));
        self::assertSame('/tmp/s/incoming', (new SpoolWorker('/tmp/s'))->path(SpoolWorker::INCOMING));
    }

    // Layout

    public function testLayoutIsCreated(): void
    {
        self::assertSame(0, $this->worker()->runOnce());

        foreach (['incoming', 'processing', 'done', 'failed'] as $folder) {
            self::assertDirectoryExists($this->dir . '/' . $folder);
        }
    }

    public function testEnsureLayoutThrowsWhenFolderCannotBeCreated(): void
    {
        $file = $this->dir . '/plain-file';
        file_put_contents($file, 'x');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot create spool folder [' . $file . '/sub/incoming]');
        (new ScriptedSpoolWorker($file . '/sub', $this->door))->runOnce();
    }

    // Picking files

    public function testTmpFilesAreIgnored(): void
    {
        $this->drop('a.txt', self::OK);
        $this->drop('a.json.tmp', self::OK);

        self::assertSame(0, $this->worker()->runOnce());

        self::assertSame(['a.json.tmp', 'a.txt'], $this->filesIn('incoming'));
        self::assertSame([], $this->door->seen);
    }

    public function testFilesProcessedInNameOrder(): void
    {
        $this->drop('b.json', '{"url":"/fix/ping/echo","params":{"n":"b"}}');
        $this->drop('a.json', '{"url":"/fix/ping/echo","params":{"n":"a"}}');

        self::assertSame(2, $this->worker()->runOnce());

        self::assertSame(
            ['{"url":"/fix/ping/echo","params":{"n":"a"}}', '{"url":"/fix/ping/echo","params":{"n":"b"}}'],
            $this->door->seen,
        );
    }

    // Done or failed

    public function testRunOnceMovesSuccessToDone(): void
    {
        $this->drop('ok.json', self::OK);

        self::assertSame(1, $this->worker()->runOnce());

        self::assertSame(['ok.json'], $this->filesIn('done'));
        self::assertSame(self::OK, file_get_contents($this->dir . '/done/ok.json'));
        self::assertSame([], $this->filesIn('incoming'));
        self::assertSame([], $this->filesIn('processing'));
        self::assertSame([], $this->filesIn('failed'));
    }

    public function testErrorResponseMovesToFailed(): void
    {
        $this->drop('lost.json', '{"url":"/no/such/route"}');

        self::assertSame(1, $this->worker()->runOnce());

        self::assertSame(['lost.json'], $this->filesIn('failed'));
        self::assertSame([], $this->filesIn('done'));
        self::assertSame([], $this->filesIn('processing'));
    }

    public function testDependencyResponseMovesToFailed(): void
    {
        $this->drop('dep.json', '{"url":"/fix/ping/needsbody"}');

        self::assertSame(1, $this->worker()->runOnce());

        self::assertSame(['dep.json'], $this->filesIn('failed'));
    }

    public function testInvalidJsonMovesToFailed(): void
    {
        $this->drop('bad.json', '{bad');

        self::assertSame(1, $this->worker()->runOnce());

        self::assertSame(['bad.json'], $this->filesIn('failed'));
        self::assertSame(400, self::code($this->emittedOnce()));
    }

    public function testUnreadableFileMovesToFailed(): void
    {
        $this->drop('x.json', self::OK);
        $worker = $this->worker();
        $worker->unreadable = true;

        self::assertSame(1, $worker->runOnce());

        self::assertSame(['x.json'], $this->filesIn('failed'));
        self::assertSame([], $this->door->seen);
        self::assertCount(1, $this->logged('error'));
    }

    public function testDoorThrowableMovesToFailed(): void
    {
        $this->drop('x.json', self::OK);
        $boom = new \LogicException('boom');
        $this->door->throw = $boom;

        self::assertSame(1, $this->worker()->runOnce());

        self::assertSame(['x.json'], $this->filesIn('failed'));
        $this->assertLoggedWith('fatal', $boom, true);
    }

    public function testDoneOverwritesFileWithSameName(): void
    {
        $this->drop('x.json', 'old', SpoolWorker::DONE);
        $this->drop('x.json', self::OK);

        self::assertSame(1, $this->worker()->runOnce());

        self::assertSame(self::OK, file_get_contents($this->dir . '/done/x.json'));
    }

    // Claims and moves

    public function testClaimLostToAnotherWorkerIsSkipped(): void
    {
        $this->drop('x.json', self::OK);
        $worker = $this->worker();
        $worker->move = static fn(string $from, string $to): bool => false;

        self::assertSame(0, $worker->runOnce());

        self::assertSame(['x.json'], $this->filesIn('incoming'));
        self::assertSame([], $this->door->seen);
        self::assertSame([], $this->logged('error'));
        self::assertSame([], $this->logged('fatal'));
        self::assertSame([], $this->logged('warning'));
        self::assertCount(1, array_filter(
            $this->logged('debug'),
            static fn(mixed $line): bool => is_string($line) && str_contains($line, 'x.json'),
        ));
    }

    public function testFinalMoveFailureLeavesFileInProcessing(): void
    {
        $this->drop('x.json', self::OK);
        $worker = $this->worker();
        $calls = 0;
        $worker->move = static function (string $from, string $to) use (&$calls): ?bool {
            $calls++;
            return $calls === 2 ? false : null;
        };

        self::assertSame(1, $worker->runOnce());

        self::assertSame(['x.json'], $this->filesIn('processing'));
        self::assertSame([], $this->filesIn('done'));
        self::assertCount(1, $this->logged('error'));
    }

    public function testLogsInfoPerFile(): void
    {
        $this->drop('ok.json', self::OK);

        $this->worker()->runOnce();

        $info = $this->logged('info');
        self::assertCount(1, $info);
        self::assertIsString($info[0]);
        self::assertStringContainsString('ok.json', $info[0]);
        self::assertStringContainsString('done', $info[0]);
        self::assertStringContainsString('200', $info[0]);
        $this->assertLoggedWith('info', $info[0], false);
    }

    // Stopping

    public function testStopFinishesCurrentFileThenExits(): void
    {
        $this->drop('a.json', self::OK);
        $this->drop('b.json', self::OK);
        $worker = $this->worker();
        $this->door->before = static function () use ($worker): void {
            $worker->stop();
        };

        self::assertSame(1, $worker->runOnce());

        self::assertSame(['a.json'], $this->filesIn('done'));
        self::assertSame(['b.json'], $this->filesIn('incoming'));
        self::assertTrue($worker->isStopRequested());
    }

    public function testRunOnceAfterStopProcessesNothing(): void
    {
        $this->drop('a.json', self::OK);
        $worker = $this->worker();
        $worker->stop();

        self::assertSame(0, $worker->runOnce());
        self::assertTrue($worker->isStopRequested());
        self::assertSame(['a.json'], $this->filesIn('incoming'));
    }

    public function testIsStopRequestedFalseAtStart(): void
    {
        self::assertFalse($this->worker()->isStopRequested());
    }

    // The run() loop

    public function testRunLoopsUntilStopped(): void
    {
        $this->drop('a.json', self::OK);
        $worker = $this->worker();
        $worker->spyHandlers = true;
        $worker->onSleep = static function () use ($worker): void {
            $worker->stop();
        };

        $worker->run();

        self::assertSame([1000], $worker->sleeps);
        self::assertSame(['a.json'], $this->filesIn('done'));
    }

    public function testRunUsesConfiguredSleep(): void
    {
        $worker = $this->worker(25);
        $worker->spyHandlers = true;
        $worker->onSleep = static function () use ($worker): void {
            $worker->stop();
        };

        $worker->run();

        self::assertSame([25], $worker->sleeps);
    }

    public function testRunSkipsSleepWhenWorkWasDone(): void
    {
        $this->drop('a.json', self::OK);
        $worker = $this->worker();
        $worker->spyHandlers = true;
        $this->door->before = static function () use ($worker): void {
            $worker->stop();
        };

        $worker->run();

        self::assertSame([], $worker->sleeps);
        self::assertSame(['a.json'], $this->filesIn('done'));
    }

    public function testRunResetsStopFlag(): void
    {
        $this->drop('a.json', self::OK);
        $worker = $this->worker();
        $worker->spyHandlers = true;
        $worker->onSleep = static function () use ($worker): void {
            $worker->stop();
        };
        $worker->stop();

        $worker->run();

        self::assertSame(['a.json'], $this->filesIn('done'));
        self::assertSame([1000], $worker->sleeps);
    }

    public function testRunInstallsAndRestoresHandlers(): void
    {
        $worker = $this->worker();
        $worker->spyHandlers = true;
        $worker->onSleep = static function () use ($worker): void {
            $worker->stop();
        };

        $worker->run();

        self::assertSame(1, $worker->installCalls);
        self::assertSame(1, $worker->restoreCalls);
    }

    public function testRunRestoresHandlersWhenRunOnceThrows(): void
    {
        $file = $this->dir . '/plain-file';
        file_put_contents($file, 'x');
        $worker = new ScriptedSpoolWorker($file . '/sub', $this->door);
        $worker->spyHandlers = true;

        try {
            $worker->run();
            self::fail('run() should throw');
        } catch (\RuntimeException) {
        }

        self::assertSame(1, $worker->installCalls);
        self::assertSame(1, $worker->restoreCalls);
    }

    public function testRunWarnsAboutLeftoversInProcessing(): void
    {
        $this->drop('old1.json', self::OK, SpoolWorker::PROCESSING);
        $this->drop('old2.json', self::OK, SpoolWorker::PROCESSING);
        $worker = $this->worker();
        $worker->spyHandlers = true;
        $worker->onSleep = static function () use ($worker): void {
            $worker->stop();
        };

        $worker->run();

        $this->assertLoggedWith(
            'warning',
            'Spool has 2 file(s) left in processing/ from an earlier run; check and move them by hand',
            false,
        );
        self::assertSame(['old1.json', 'old2.json'], $this->filesIn('processing'));
    }

    public function testRunDoesNotWarnWhenProcessingIsEmpty(): void
    {
        $worker = $this->worker();
        $worker->spyHandlers = true;
        $worker->onSleep = static function () use ($worker): void {
            $worker->stop();
        };

        $worker->run();

        self::assertSame([], $this->logged('warning'));
    }

    // Signals

    public function testSigtermSetsStop(): void
    {
        $worker = $this->worker();
        $worker->onSleep = static function (): void {
            posix_kill(posix_getpid(), SIGTERM);
        };

        $worker->run();

        self::assertTrue($worker->isStopRequested());
        self::assertSame([1000], $worker->sleeps);
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGTERM));
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGINT));
    }

    public function testSigintStopsRun(): void
    {
        $worker = $this->worker();
        $worker->onSleep = static function (): void {
            posix_kill(posix_getpid(), SIGINT);
        };

        $worker->run();

        self::assertTrue($worker->isStopRequested());
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGINT));
    }

    public function testWithoutPcntlLogsWarning(): void
    {
        $worker = $this->worker();
        $worker->pcntl = false;
        $handlersSeen = [];
        $worker->onSleep = static function () use ($worker, &$handlersSeen): void {
            $handlersSeen[] = pcntl_signal_get_handler(SIGTERM);
            $worker->stop();
        };

        $worker->run();

        $this->assertLoggedWith('warning', 'pcntl is not loaded; a signal will stop the worker mid-file', false);
        self::assertSame([SIG_DFL], $handlersSeen);
        self::assertSame(SIG_DFL, pcntl_signal_get_handler(SIGTERM));
    }

    // Real seams

    public function testHasPcntlIsTrueHere(): void
    {
        self::assertTrue($this->worker()->realHasPcntl());
    }

    public function testSleepMsSleeps(): void
    {
        $start = hrtime(true);
        $this->worker()->realSleepMs(1);
        self::assertGreaterThanOrEqual(1_000_000, hrtime(true) - $start);
    }

    public function testMoveFileFalseWhenSourceMissing(): void
    {
        self::assertFalse($this->worker()->realMoveFile($this->dir . '/missing', $this->dir . '/other'));
    }

    public function testReadFileNullWhenMissing(): void
    {
        self::assertNull($this->worker()->realReadFile($this->dir . '/missing'));
    }
}
