<?php
namespace Core\Domains\PaymentProcess\Entity;
use \Core\Domains\PaymentProcess\Entity\Account;
class EventWithdraw
{
    const TYPE = 'withdraw';
    public function __construct(
        public Account $account,
    ) {}
}