<?php

namespace Core\Event\Exceptions\Interfaces;

interface EventDispatcherInterface
{
    public function dispatch();
    public function getDispatcher();
}