<?php

namespace Core\Domains\PaymentProcess\Repository;

use Core\Infra\Redis\Model\RedisClient;
use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;

class ResetRepository
{
    public function __construct(
        protected ContainerInterface $container
    ){}
    public function reset(){
        $this->container->get(RedisClient::class)->reset();
    }
}