<?php

namespace Core\Infra\Redis\Model;

use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;

class WithdrawModelRedis extends RedisClient
{
   public function __construct()
   {
       $this->table = 'withdraw';
   }
}