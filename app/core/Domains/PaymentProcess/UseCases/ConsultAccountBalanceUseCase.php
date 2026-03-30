<?php

namespace Core\Domains\PaymentProcess\UseCases;

use Core\Domains\PaymentProcess\Repository\AccountRepository;
use Core\Domains\PaymentProcess\ValueObject\ID;
use Core\Infra\Redis\Model\AccountModelRedis;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;

class ConsultAccountBalanceUseCase
{
    public function execute(
        ResponseInterface $response,
        ID $id
    ){
        $accountRepository = ApplicationContext::getContainer()->get(AccountRepository::class);

        try{
           $errors = $id->eventDispatcherHandler->dispatch();

           if(!empty($errors)){
               throw new \Exception(json_encode($errors),422);
           }

           $account = $accountRepository->findById($id->id);

           if(empty($account)){
               return $response->withStatus(404)
                   ->withHeader('Content-Type', 'application/json')
                   ->withBody(
                       new SwooleStream('0')
                   );
           }

           return $response->withStatus(200)
               ->withHeader('Content-Type', 'application/json')
               ->withBody(
                   new SwooleStream(
                       $account['amount']
                   )
               );
       }catch (\Exception $e){
           return $response->withStatus($e->getCode())
               ->withHeader('Content-Type', 'application/json')
               ->withBody(
                   new SwooleStream(json_encode([
                       'status' => false,
                       'code' => $e->getCode(),
                       'error' => json_decode($e->getMessage()) ?? $e->getMessage(),
                   ]))
               );
       }
    }
}