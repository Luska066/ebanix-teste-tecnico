<?php

declare(strict_types=1);

namespace App\Middleware\Firebase;

use App\Auth\Firebase\V1\Helpers\JwtFirebaseHelper;
use Cassandra\Uuid;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use \Hyperf\HttpMessage\Server\Response;
use \Hyperf\HttpMessage\Stream\SwooleStream;

class AuthMiddlewareFirebaseV1 implements MiddlewareInterface
{
    private $CONTENT_TYPE_JSON = 'application/json';
    private JwtFirebaseHelper $jwtHelper;
    public Response $response;

    // Sugestões para implementação
    // -> Salvar temporáriamente as sessões logadas em banco de dados relacional 
    //    para obter o maior controler sobre os acessos a api
    
    public function __construct(protected ContainerInterface $container)
    {
        $this->jwtHelper = $this->container->get(JwtFirebaseHelper::class);
        $this->response = $this->container->get(Response::class);

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isValidToken = false;
        $authHeader = $request->getHeaderLine('X-Authorization-Api');
        $contentType =$request->getHeaderLine('Content-Type');
        
        $paramsValidated = $this->validateParams($authHeader,$contentType);
        
        if ( $paramsValidated  ) {
            
            $token = str_replace('Bearer ', '', $authHeader);
            try {
                $this->jwtHelper->validate($token);
                $isValidToken = true;
            } catch (\Exception $e) {
                $isValidToken = false;
            }

        }

        if (!$isValidToken) {
            return $this->response
                ->withStatus(401)
                ->withHeader('Content-Type', $this->CONTENT_TYPE_JSON)
                ->withBody(
                    new SwooleStream(
                        json_encode(
                            [
                                'time' => time(),
                                'status' => 401,
                                'error' => 'Unauthorized',
                            ]
                        )
                    )
                );
        }

        return $handler->handle($request);

    }

    private function validateParams($authHeader, $contentType){
        return $authHeader 
            && $contentType === $this->CONTENT_TYPE_JSON
            && str_starts_with($authHeader, 'Bearer ');
    }
}
