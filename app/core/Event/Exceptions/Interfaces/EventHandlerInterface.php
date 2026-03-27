<?php

namespace Core\Event\Exceptions\Interfaces;

interface EventHandlerInterface
{
    public function getId();
    public function getDomain();
    public function getExceptionName();
    public function getException(): \Exception;
    public function getMessage();
    public function getCode();
    public function getData();
}