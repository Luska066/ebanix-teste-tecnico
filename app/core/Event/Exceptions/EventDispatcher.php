<?php

namespace Core\Event\Exceptions;


use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;
use Core\Event\Exceptions\Interfaces\EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public EventDispatcherHandlerInterface $e;

    public function __construct(EventDispatcherHandlerInterface $e){
        $this->e = $e;
    }

    public function dispatch($returns = false){
        if($this->hasEvents()){
            $aux = $this->e->dispatch();
            return $returns ? $aux: null;
        }
    }

    public function getDispatcher(): EventDispatcherHandlerInterface{
        return $this->e;
    }

    public function hasEvents(): bool
    {
        return $this->e->count() > 0;
    }
}