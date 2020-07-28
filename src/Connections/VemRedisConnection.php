<?php

namespace Vemcogroup\Redis\Connections;

use Redis;
use RedisException;
use Illuminate\Support\Str;
use Illuminate\Redis\Connections\PhpRedisConnection;

class VemRedisConnection extends PhpRedisConnection
{
    /**
     * Returns the score of member in the sorted set at key.
     *
     * @param  string  $key
     * @param  mixed|string  $member
     * @return int
     */
    public function zscore($key, $member): ?int
    {
        $key = $this->applyPrefix($key);

        $result = $this->executeRaw(array_merge(['zscore', $key, $member]));

        return $result !== false ? $result : null;
    }

    private function applyPrefix($key): string
    {
        $prefix = (string) $this->client->getOption(Redis::OPT_PREFIX);

        return $prefix . $key;
    }

    /**
     * Execute a raw command.
     *
     * @param  array  $parameters
     * @return mixed
     */
    public function executeRaw(array $parameters)
    {
        if ($this->shouldExecuteAsRaw()) {
            return $this->command('rawCommand', $parameters);
        }

        return $this->rearrangeParametersAndRunCommand($parameters);
    }

    /**
     * Phpredis requires second argument "options" to be an array (when using special NX XX CH INCR modifiers).
     * Note that there can be more than one special modifier per command.
     *
     * @param  array  $parameters
     * @return mixed
     */
    private function rearrangeParametersAndRunCommand($parameters)
    {
        $specialMethods = ['NX', 'XX', 'CH', 'INCR'];
        $options = [];
        $args = [];

        $method = array_shift($parameters);

        while (! empty($parameters)) {
            if ((in_array($parameters[0], $specialMethods, true))) {
                $args[] = array_shift($parameters);

                continue;
            }

            if (! empty($args)) {
                $options[] = $args;
                $args = [];
            }

            $options[] = array_shift($parameters);
        }

        return $this->command($method, $options);
    }

    /**
     * If config option 'serializer' is set, we cannot rely on rawCommand.
     */
    private function shouldExecuteAsRaw(): bool
    {
        return $this->client->getOption(Redis::OPT_SERIALIZER) === 0;
    }
}

