<?php

namespace Core\Infra\Redis\Model;

use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

class RedisClient
{
    public string $table = '';

    protected Redis $redis;
    public int $ttl = 3600 * 24;
    const PREFIX = 'ebanix:';

    public function __construct(public ContainerInterface $container)
    {
        $this->redis = $this->container->get(Redis::class);
    }
    public function set($key, array $value = [])
    {
        return $this->redis->setex($this->getTableName($key), $this->ttl, json_encode($value));
    }

    public function get($key)
    {
        $data = $this->redis->get($this->getTableName($key));
        // Se for string, faz decode, senão retorna []
        if (is_string($data)) {
            return $data ? json_decode($data, true) : [];
        }
        
        // Se for objeto Redis (dentro de transação) ou outros tipos, retorna []
        return $data;
    }

    public function del($key)
    {
        return $this->redis->del($key);
    }

    public function reset(){
        return $this->redis->flushdb();
    }

    protected function getTableName(string $key){
        if(!empty($key)){
            return self::PREFIX.$this->table.":".$key;
        }
        return self::PREFIX.$this->table;
    }

    public function watch($key)
    {
        return $this->redis->watch($this->getTableName($key));
    }

    public function multi()
    {
        return $this->redis->multi();
    }

    public function exec()
    {
        return $this->redis->exec();
    }
}