<?php

namespace Core\Domains\PaymentProcess\Repository;

use Core\Infra\Redis\Model\AccountModelRedis;
use Core\Infra\Redis\Model\RedisClient;
use Psr\Container\ContainerInterface;

class AccountRepository
{
    public function __construct(
        protected ContainerInterface $container
    )
    {
    }

    public function findById(int $id)
    {
        return $this->container->make(AccountModelRedis::class)->get($id);
    }

    public function create(int $id, float $amount)
    {
        $this->watch($id);
        $this->multi();
        $this->container
            ->make(AccountModelRedis::class)
            ->set(
                $id,
                [
                    'id' => $id,
                    'amount' => $amount
                ]
            );
        $result = $this->exec();
        if(!$result){
            throw new \Exception('Failed to create account',422);
        }

        return $result ? $this->findById($id) : [];
    }

    public function update(int $id, float $amount)
    {
        $this->watch($id);
        $this->multi();
        $this->container
            ->make(AccountModelRedis::class)
            ->set(
                $id,
                [
                    'id' => $id,
                    'amount' => $amount
                ]
            );
        $result = $this->exec();
        if(!$result){
            throw new \Exception('Failed to create account',422);
        }

        return $result ? $this->findById($id) : [];
    }

    public function watch(int $id)
    {
        return $this->container->make(AccountModelRedis::class)->watch($id);
    }

    public function multi()
    {
        return $this->container->make(AccountModelRedis::class)->multi();
    }

    public function exec()
    {
        return $this->container->make(AccountModelRedis::class)->exec();
    }
}