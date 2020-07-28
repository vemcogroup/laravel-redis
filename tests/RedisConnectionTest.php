<?php

namespace Vemcogroup\Redis\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithRedis;

class RedisConnectionTest extends TestCase
{
    use InteractsWithRedis;

    public function testItRunsRawCommand(): void
    {
        foreach ($this->connections() as $redis) {
            $redis->executeRaw(['SET', 'test:raw:1', 'hello world']);

            $this->assertEquals(
                'hello world', $redis->executeRaw(['GET', 'test:raw:1'])
            );

            $redis->flushall();
        }
    }

    public function testItDeletesKeys(): void
    {
        foreach ($this->connections() as $redis) {
            $redis->set('one', 'mohamed');
            $redis->set('two', 'mohamed');
            $redis->set('three', 'mohamed');

            $redis->del('one');
            $this->assertNull($redis->get('one'));
            $this->assertNotNull($redis->get('two'));
            $this->assertNotNull($redis->get('three'));

            $redis->del('two', 'three');
            $this->assertNull($redis->get('two'));
            $this->assertNull($redis->get('three'));

            $redis->flushall();
        }
    }
}
