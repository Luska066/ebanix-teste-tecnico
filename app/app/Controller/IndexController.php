<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use _PHPStan_8c66d8255\Nette\Neon\Exception;
use Core\Domains\PaymentProcess\Entity\Account;
use Core\Domains\PaymentProcess\Entity\EventDeposit;
use Core\Domains\PaymentProcess\Entity\EventTransfer;
use Core\Domains\PaymentProcess\Entity\EventWithdraw;
use Core\Domains\PaymentProcess\UseCases\ConsultAccountBalanceUseCase;
use Core\Domains\PaymentProcess\UseCases\DepositUseCase;
use Core\Domains\PaymentProcess\UseCases\TransferUseCase;
use Core\Domains\PaymentProcess\UseCases\WithdrawUseCase;
use Core\Domains\PaymentProcess\ValueObject\AMOUNT;
use Core\Domains\PaymentProcess\ValueObject\ID;
use Core\Event\Exceptions\Dispatcher\ExceptionDispatcherHandler;
use Core\Infra\Redis\Model\AccountModelRedis;
use Hyperf\Collection\Arr;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Di\Annotation\Inject;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function reset(){
        try{
            $redis = $this->container->get(AccountModelRedis::class);
            $redis->reset();
            return $this->response->withStatus(200);
        }catch (\Exception $e){
            return $this->response->withStatus(500)
                ->withBody(
                    new SwooleStream(json_encode([
                        'error' => $e->getMessage()
                    ]))
                );
        }
    }

    public function balance(){
        $eventDipatcherHandler = new ExceptionDispatcherHandler();
        $consultAccountBalanceUseCase =  new ConsultAccountBalanceUseCase();

        $account_id = (int) $this->request->query('account_id');
        return $consultAccountBalanceUseCase->execute(
                $this->response,
                new ID(
                    $account_id,
                    $eventDipatcherHandler
                )
            );
    }

    public function event(){
        $eventDipatcherHandler = new ExceptionDispatcherHandler();

        $body = $this->request->getBody()->getContents();
        $data = json_decode($body, true) ?? [];


        switch ($data['type']){

            case EventDeposit::TYPE:
                $depositUseCase = new DepositUseCase();
                return $depositUseCase->execute(
                    $this->response,
                    new ID(
                        Arr::get($data,'destination',0),
                        $eventDipatcherHandler,
                        'destination'
                    ),
                    new AMOUNT(
                        Arr::get($data,'amount',0),
                        $eventDipatcherHandler
                    ),
                    $eventDipatcherHandler
                );
            case EventWithdraw::TYPE:
                $withdrawUseCase = new WithdrawUseCase();
                return $withdrawUseCase->execute(
                    $this->response,
                    new ID(
                        Arr::get($data,'destination',0),
                        $eventDipatcherHandler,
                        'destination'
                    ),
                    new AMOUNT(
                        Arr::get($data,'amount',0),
                        $eventDipatcherHandler
                    ),
                    $eventDipatcherHandler
                );
            case EventTransfer::TYPE:
                $transferUseCase = new TransferUseCase();
                $origin = (int) Arr::get($data,'origin');
                $destination = (int) Arr::get($data,'destination');
                $amount = (float) Arr::get($data,'amount');

                return $transferUseCase->execute(
                    $this->response,
                    new ID($origin,$eventDipatcherHandler),
                    new ID($destination,$eventDipatcherHandler),
                    new AMOUNT($amount,$eventDipatcherHandler),
                    $eventDipatcherHandler
                );

            default:
                return $this->response
                    ->withStatus(422)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(
                        new SwooleStream(
                            json_encode(
                                [
                                    'status' => false,
                                    'code' => 422,
                                    'error' => 'Invalid event type'
                                ]
                            )
                        )
                    );
        }

    }
}
