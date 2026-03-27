<?php

namespace Core\Event\Exceptions\Interfaces;


interface EventDispatcherHandlerInterface
{
    public function dispatch();

    public function list();

    public function add(EventHandlerInterface $exception);

    public function remove(EventHandlerInterface $exception);

    public function count();

    public function clear();
}