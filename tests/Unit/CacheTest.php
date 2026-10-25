<?php

declare(strict_types=1);

namespace Tests\Unit;

use Drivers\Cache\Apc;
use Drivers\Cache\Dummy;
use Drivers\Cache\Memcache;
use Drivers\Cache\Memcached;
use Drivers\Cache\Redis;
use Drivers\Session\CacheHandler;
use PHPUnit\Framework\TestCase;
use Resources\Cache;
use Resources\Interfaces\Cache as CacheDriver;
use Resources\RunException;

/**
 * Every driver passes the same contract. The ones that need a server run
 * only when it is given, e.g.
 * PANADA_TEST_REDIS=127.0.0.1:6379 PANADA_TEST_MEMCACHED=127.0.0.1:11211
 */
final class CacheTest extends TestCase
{
    private function contract(CacheDriver $cache): void
    {
        $this->assertTrue($cache->flushValues());

        $this->assertTrue($cache->setValue('name', 'Budi'));
        $this->assertSame('Budi', $cache->getValue('name'));
        $this->assertTrue($cache->setValue('zero', 0));
        $this->assertSame(0, $cache->getValue('zero'), 'Falsy values survive');
        $this->assertTrue($cache->setValue('list', ['a' => [1, 2]]));
        $this->assertSame(['a' => [1, 2]], $cache->getValue('list'));
        $this->assertFalse($cache->getValue('missing'));

        $this->assertFalse($cache->addValue('name', 'Ani'), 'addValue() keeps an existing key');
        $this->assertSame('Budi', $cache->getValue('name'));
        $this->assertTrue($cache->addValue('city', 'Bandung'));
        $this->assertTrue($cache->updateValue('city', 'Bekasi'));
        $this->assertSame('Bekasi', $cache->getValue('city'));

        $this->assertTrue($cache->setValue('views', 5));
        $this->assertSame(7, $cache->incrementBy('views', 2));
        $this->assertSame(6, $cache->decrementBy('views'));
        $this->assertSame(6, $cache->getValue('views'));

        // Deleting the namespace key drops all of its values at once.
        $this->assertTrue($cache->setValue('item', 'kopi', 0, 'products'));
        $this->assertSame('kopi', $cache->getValue('item', 'products'));
        $this->assertFalse($cache->getValue('item'));
        $this->assertTrue($cache->deleteValue('products'));
        $this->assertFalse($cache->getValue('item', 'products'));

        $this->assertTrue($cache->deleteValue('name'));
        $this->assertFalse($cache->getValue('name'));

        $this->assertTrue($cache->flushValues());
        $this->assertFalse($cache->getValue('city'));
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function server(string $env): array
    {
        $server = (string) getenv($env);

        if ($server === '') {
            $this->markTestSkipped('Set ' . $env . '=host:port to run this test.');
        }

        [$host, $port] = explode(':', $server) + [1 => 0];

        return [$host, (int) $port];
    }

    public function testDummy(): void
    {
        $this->contract(new Dummy());
    }

    public function testRedis(): void
    {
        [$host, $port] = $this->server('PANADA_TEST_REDIS');

        $this->contract(new Redis(['server' => [['host' => $host, 'port' => $port]]]));
    }

    public function testMemcached(): void
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('memcached extension is not installed.');
        }

        [$host, $port] = $this->server('PANADA_TEST_MEMCACHED');

        $this->contract(new Memcached(['server' => [['host' => $host, 'port' => $port]]]));
    }

    public function testMemcache(): void
    {
        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('memcache extension is not installed.');
        }

        [$host, $port] = $this->server('PANADA_TEST_MEMCACHED');

        $this->contract(new Memcache(['server' => [['host' => $host, 'port' => $port]]]));
    }

    public function testApcu(): void
    {
        if (!extension_loaded('apcu') || !ini_get('apc.enable_cli')) {
            $this->markTestSkipped('apcu extension with apc.enable_cli=1 is needed.');
        }

        $this->contract(new Apc());
    }

    public function testFacadePicksTheDriverAndForwards(): void
    {
        $cache = new Cache();
        $cache->setValue('answer', 42);

        $this->assertSame(42, $cache->getValue('answer'));
        $this->assertSame(42, (new Cache('default', 'dummy'))->getValue('answer'), 'Dummy shares memory per request');
    }

    public function testFacadeErrors(): void
    {
        foreach ([fn() => new Cache('nope'), fn() => new Cache('default', 'nope'), fn() => new Cache('default', '../Session/Cookie')] as $i => $create) {
            try {
                $create();
                $this->fail('Attempt #' . $i . ' did not fail');
            } catch (RunException $e) {
                $this->assertMatchesRegularExpression('/not defined|Unknown cache driver/', $e->getMessage());
            }
        }

        if (!extension_loaded('memcache')) {
            $this->expectException(RunException::class);
            $this->expectExceptionMessage('needs the memcache PHP extension');
            new Cache('default', 'memcache');
        }
    }

    public function testSessionsCanBeStoredInTheCache(): void
    {
        $handler = new CacheHandler(new Cache(), 'sessions_', 60);

        $this->assertTrue($handler->write('abc', 'user|s:4:"budi";'));
        $this->assertSame('user|s:4:"budi";', $handler->read('abc'));
        $this->assertTrue($handler->destroy('abc'));
        $this->assertSame('', $handler->read('abc'));
    }
}
