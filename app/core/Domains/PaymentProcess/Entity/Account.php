<?php

namespace Core\Domains\PaymentProcess\Entity;

use Core\Domains\PaymentProcess\ValueObject\AMOUNT;
use Core\Domains\PaymentProcess\ValueObject\ID;

class Account
{
    public function __construct(
        public ID $id,
        public AMOUNT $balance
    ){}

    public function deposit(float $amount){
        $this->balance->amount += $amount;
    }
    public function withdraw(float $amount){
        $this->balance->amount -= $amount;
    }
}