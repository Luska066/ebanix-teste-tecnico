<?php

namespace App\Auth\Firebase\V1\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtFirebaseHelper
{
    private string $alg;
    private string $iss_key;
    private string $secret_key;

    public function __construct()
    {
        $this->alg = env('JWT_KEY_ALG', 'HS256');
        $this->iss_key = env('JWT_ISS_KEY', 'your-issuer');
        $this->secret_key = env('JWT_SECRET_KEY', 'your-secret-key');
    }

    public function generate(array $data)
    {
        $payload = [
            'iss' => $this->iss_key,
            'iat' => time(),
            'exp' => time() + (3600 * 24),
            'data' => $data,
        ];

        return JWT::encode($payload, $this->secret_key, $this->alg);
    }

    public function validate(string $token)
    {
        return JWT::decode($token, new Key($this->secret_key, $this->alg));
    }
}