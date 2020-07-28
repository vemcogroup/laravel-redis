<?php

namespace Vemcogroup\Redis\Tests;

use Redis;
use Illuminate\Foundation\Testing\Concerns\InteractsWithRedis;

class RedisCompressionTest extends TestCase
{
    use InteractsWithRedis;

    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->connections()['compression'];
    }

    public function testCompressionLZF(): void
    {
        if (!defined('Redis::COMPRESSION_LZF')) {
            $this->markTestSkipped();
        }

        $this->checkCompression(Redis::COMPRESSION_LZF, 0);
    }

    public function testCompressionZSTD(): void
    {
        if (!defined('Redis::COMPRESSION_ZSTD')) {
            $this->markTestSkipped();
        }

        $this->checkCompression(Redis::COMPRESSION_ZSTD, 0);
        $this->checkCompression(Redis::COMPRESSION_ZSTD, 9);
    }

    public function testCompressionLZ4(): void
    {
        if (!defined('Redis::COMPRESSION_LZ4')) {
            $this->markTestSkipped();
        }

        $this->checkCompression(Redis::COMPRESSION_LZ4, 0);
        $this->checkCompression(Redis::COMPRESSION_LZ4, 9);
    }

    private function checkCompression($mode, $level): void
    {
        $this->assertTrue($this->client->setOption(Redis::OPT_COMPRESSION, $mode) === true);  // set ok
        $this->assertTrue($this->client->getOption(Redis::OPT_COMPRESSION) === $mode);    // get ok

        $this->assertTrue($this->client->setOption(Redis::OPT_COMPRESSION_LEVEL, $level) === true);
        $this->assertTrue($this->client->getOption(Redis::OPT_COMPRESSION_LEVEL) === $level);

        $val = 'xxxxxxxxxx';
        $this->client->set('key', $val);
        $this->assertEquals($val, $this->client->get('key'));

        /* Empty data */
        $this->client->set('key', '');
        $this->assertEquals('', $this->client->get('key'));

        /* Iterate through class sizes */
        for ($i = 1; $i <= 65536; $i *= 2) {
            foreach ([str_repeat('A', $i), random_bytes($i)] as $val) {
                $this->client->set('key', $val);
                $this->assertEquals($val, $this->client->get('key'));
            }
        }
    }
}
