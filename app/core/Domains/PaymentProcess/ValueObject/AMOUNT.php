<?php

namespace Core\Domains\PaymentProcess\ValueObject;

use Core\Event\Exceptions\Handle\ExceptionHandler;
use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;

class AMOUNT
{
    public function __construct(
        public float $amount,
        public ?EventDispatcherHandlerInterface $eventDispatcherHandler = null
    ){
        $this->validate();
    }

    public function validate(){
        if($this->eventDispatcherHandler != null){
            try{
                if($this->amount <= 0){
                    throw new \Exception('ID must be greater than 0');
                }
            }catch (\Exception $exception){
                $this->eventDispatcherHandler->add(
                    new ExceptionHandler(
                        'amount',
                        $exception
                    )
                );
            }
        }
    }

    public function __toString()
    {
        return (string) $this->amount;
    }
}