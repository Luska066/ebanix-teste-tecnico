<?php
namespace Core\Domains\PaymentProcess\Entity;
use Core\Domains\PaymentProcess\Entity\Account;

class EventTransfer
{
    const TYPE = 'transfer';
    public function __construct(
        public Account $originAccount,
        public Account $destinationAccount,
        public float $amount
    ){}
}