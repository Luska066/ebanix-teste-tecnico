<?php

namespace Core\Infra\Redis\Model;

use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;

class AccountModelRedis extends RedisClient
{
  public string $table = 'accounts';

}