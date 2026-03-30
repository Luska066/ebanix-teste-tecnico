<?php

namespace Core\Event\Exceptions\Handle;



use Core\Event\Exceptions\Interfaces\EventHandlerInterface;

class ExceptionHandler implements EventHandlerInterface
{
    private string $id = '';
    public function __construct(
        public string $domain,
        public \Exception $exception,
    ){}

    public function getDomain()
    {
        return $this->domain;
    }

    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    public function getCode()
    {
       return $this->exception->getCode();
    }

    public function getExceptionName()
    {
        return (new \ReflectionClass($this->exception))->getShortName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getData()
    {
       return [
           'domain' => $this->domain,
           'exception' => $this->exception,
           "class" => get_class($this->exception),
           'message' => $this->getMessage(),
           'code' => $this->getCode(),
       ];
    }

    public function getException(): \Exception
    {
        return $this->exception;
    }
}