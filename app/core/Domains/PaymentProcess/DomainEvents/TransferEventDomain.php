<?php

namespace Core\Domains\PaymentProcess\DomainEvents;

use Core\Domains\PaymentProcess\Entity\Account;

class TransferEventDomain
{
    public static function transferAmountBetweenAccounts(
        Account $originAccount,
        Account $destinationAccount,
        float $amount
    )
    {
        if($originAccount->balance->amount >= $amount){
            $originAccount->withdraw($amount);
            $destinationAccount->deposit($amount);
        }

        return [
            "destination" => $destinationAccount,
            "origin" => $originAccount
        ];
    }
}