<?php

namespace Core\Domains\PaymentProcess\UseCases;

use _PHPStan_8c66d8255\Nette\Neon\Exception;
use Core\Domains\PaymentProcess\DomainEvents\TransferEventDomain;
use Core\Domains\PaymentProcess\Entity\Account;
use Core\Domains\PaymentProcess\Entity\EventDeposit;
use Core\Domains\PaymentProcess\Entity\EventTransfer;
use Core\Domains\PaymentProcess\Repository\AccountRepository;
use Core\Domains\PaymentProcess\ValueObject\AMOUNT;
use Core\Domains\PaymentProcess\ValueObject\ID;
use Core\Event\Exceptions\Interfaces\EventDispatcherHandlerInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;

class TransferUseCase
{
    public function execute(
        ResponseInterface $response,
        ID $originAccount,
        ID $destinationAccount,
        AMOUNT $amount,
        EventDispatcherHandlerInterface $exceptionDispatcherHandler
    ){
        $accountRepository = new AccountRepository(ApplicationContext::getContainer());
        try{

            $originAccount = $accountRepository->findById($originAccount->id);
            $destinationAccount = $accountRepository->findById($destinationAccount->id);

            if(empty($originAccount) == true || empty($destinationAccount) == true){

                throw new Exception('',404);
            }

            $errors = $exceptionDispatcherHandler->dispatch();
            if(!empty($errors)){
                throw new \Exception(json_encode($errors),422);
            }

            $eventDeposit = new EventTransfer(
                new Account(new ID($originAccount['id']), new AMOUNT($originAccount['amount'])),
                new Account(new ID($destinationAccount['id']), new AMOUNT( $destinationAccount['amount'])),
                $amount->amount
            );

            $resultTransfer = TransferEventDomain::transferAmountBetweenAccounts(
                $eventDeposit->originAccount,
                $eventDeposit->destinationAccount,
                $amount->amount
            );

            $originAccount = $accountRepository->update(
                $resultTransfer['origin']->id->id,
                $resultTransfer['origin']->balance->amount
            );

            $destinationAccount = $accountRepository->update(
                $resultTransfer['destination']->id->id,
                $resultTransfer['destination']->balance->amount
            );

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    new SwooleStream(json_encode([
                        "origin" => $originAccount,
                        "destination" => $destinationAccount
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