<?php

namespace Core\Domains\PaymentProcess\ValueObject;

use Core\Event\Exceptions\Handle\ExceptionHandler;
use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;

class ID
{
    public function __construct(
        public int $id,
        public ?EventDispatcherHandlerInterface $eventDispatcherHandler = null,
        public string $domain = 'id'
    ){
        $this->validate();
    }

    public function validate(){
        if($this->eventDispatcherHandler != null){
           try{
               $id = (int) $this->id;
               if($id <= 0){
                   throw new \Exception('ID must be greater than 0');
               }
           }catch (\Exception $exception){
               $this->eventDispatcherHandler->add(
                   new ExceptionHandler(
                       $this->domain,
                       $exception
                   )
               );
           }
        }
    }

    public function __toString()
    {
        return $this->id;
    }
}