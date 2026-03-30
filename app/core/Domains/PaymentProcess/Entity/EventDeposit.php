<?php
namespace Core\Domains\PaymentProcess\Entity;
use \Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;
use \Core\Domains\PaymentProcess\Entity\Account;
class EventDeposit
{
    const TYPE = 'deposit';
    public function __construct(
        public Account $account,
    ){}
}