<?php

namespace Core\Domains\PaymentProcess\UseCases;

use Core\Domains\PaymentProcess\Entity\Account;
use Core\Domains\PaymentProcess\Entity\EventDeposit;
use Core\Domains\PaymentProcess\Repository\AccountRepository;
use Core\Domains\PaymentProcess\ValueObject\AMOUNT;
use Core\Domains\PaymentProcess\ValueObject\ID;
use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;

class DepositUseCase
{
    public function execute(
        ResponseInterface $response,
        ID $destination,
        AMOUNT $amount,
        EventDispatcherHandlerInterface $exceptionDispatcherHandler
    ){
        $accountRepository = new AccountRepository(ApplicationContext::getContainer());
        try{
            $eventDeposit = new EventDeposit(
                new Account($destination, $amount),
            );

            $errors = $exceptionDispatcherHandler->dispatch();
            if(!empty($errors)){
                throw new \Exception(json_encode($errors),422);
            }

            $account = $accountRepository->findById($destination->id);

            if(empty($account)){

                $account = $accountRepository->create(
                    $eventDeposit->account->id->id,
                    $eventDeposit->account->balance->amount,
                );

                return $response->withStatus(201)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(
                        new SwooleStream(json_encode([
                            "destination" => [
                                "id" => (string) $account['id'],
                                "balance" => $account['amount']
                            ]
                        ]))
                    );
            }

            $currentAccount = new Account(
                new ID($account['id'],$exceptionDispatcherHandler),
                new AMOUNT($account['amount'],$exceptionDispatcherHandler)
            );

            $errors = $exceptionDispatcherHandler->dispatch();
            if(!empty($errors)){
                throw new \Exception(json_encode($errors),422);
            }

            $currentAccount->deposit($eventDeposit->account->balance->amount);


            $account = $accountRepository->update(
                $currentAccount->id->id,
                $currentAccount->balance->amount
            );

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    new SwooleStream(json_encode([
                        "destination" => [
                            "id" => (string) $account['id'],
                            "balance" => $account['amount']
                        ]
                    ]))
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