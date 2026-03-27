<?php

namespace Core\Event\Exceptions\Dispatcher;


use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;
use Core\Event\Exceptions\Interfaces\EventHandlerInterface;
use Ramsey\Uuid\Uuid;

class ExceptionDispatcherHandler implements EventDispatcherHandlerInterface
{
    /**@var Array<EventHandlerInterface> $exceptions */
    public array $exceptions = [];

    public function dispatch()
    {
        $message = "";
        $data = [];
        foreach ($this->exceptions as $aux) {
            /**@var EventHandlerInterface $exception */
            $total = count($aux) - 1;
            foreach ($aux as $key => $exception) {
                $message .= $exception->getDomain() . "." . $exception->getExceptionName() . ".".$exception->getCode().": " . $exception->getMessage() . ",";
                $data[] = $this->logExceptionAndReturnResponse($exception);
                if($total == $key){
                    $message = substr($message, 0, -2);
                }
            }
        }
        if(!empty($message)){
           return $this->resourceOutput($message, $data);
        }
    }

    public function resourceOutput($message, $data){
        $explode_by = array_filter(explode(',',$message),fn($item) => !empty($item));
        $dataColumn = $this->resourceMessageOutput($explode_by);
        return [
            "success" => false,
            "code" => 422,
            "error" => [
                "message" => $message,
                "message_object" => $dataColumn,
                "code" => 422,
                "details" => $data
            ],
        ];
    }

    private function resourceMessageOutput($explode_by){
        $dataColumn = [];
        foreach ($explode_by as $value) {
            $item = explode('.', $value);
            $messageAndCode = explode(':',$item[2]);
            $dataColumn[$item[0]][$item[1]] = [
                "domain" => $item[0],
                "attributte" => $item[1],
                "exception" => $item[2],
                "code" => $messageAndCode[0],
                "messase" => $messageAndCode[1],
            ];
        }
        return $dataColumn;
    }

    public function count(){
        return count($this->exceptions);
    }

    public function add(EventHandlerInterface $exception)
    {
        $domain = $exception->getDomain();
        $uuid = Uuid::uuid4()->toString();
        $exception->setId($uuid);
        $this->exceptions[$domain][$uuid] = $exception;
    }

    public function list(){
        return $this->exceptions;
    }

    public function remove(EventHandlerInterface $exception)
    {
        unset($this->exceptions[$exception->getDomain()][$exception->getId()]);
    }

    public function clear()
    {
        $this->exceptions = [];
    }

    public function getTotalExceptionsPerDomain(string $domain): int
    {
        return count($this->exceptions[$domain] ?? []);
    }

    private function logExceptionAndReturnResponse(EventHandlerInterface $exception)
    {
        if($exception->getException() instanceof ApiException){
            $apiException = [
                "domain" => $exception->getDomain(),
                "exception_class" => $exception->getExceptionName(),
                "message" => $exception->getMessage(),
                "response_body" => $exception->getException()->getResponseBody() ?? [],
                "response_headers" => $exception->getException()->getHeaders() ?? [],
                "code" => $exception->getCode(),
                "type" => str_replace('.php','',basename($exception->getException()->getFile())),
                "line" => $exception->getException()->getLine(),
            ];
            return $apiException;
        }else{
            $genericException = [
                "domain" => $exception->getDomain(),
                "exception_class" => $exception->getExceptionName(),
                "message" => $exception->getMessage(),
                "code" => $exception->getCode(),
                "type" => str_replace('.php','',basename($exception->getException()->getFile())),
                "line" => $exception->getException()->getLine(),
            ];
            return $genericException;
        }
        return [];
    }
}