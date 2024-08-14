<?php

namespace wtg\IpCountryDetector\Services;

use Exception;

class JWTService
{
    protected $publicKey;

    public function __construct($publicKeyPath)
    {
        $this->publicKey = file_get_contents($publicKeyPath);
    }

    /**
     * @throws Exception
     */
    public function parseToken($jwt)
    {
        $tokenParts = explode('.', $jwt);

        if (count($tokenParts) !== 3) {
            throw new Exception('Invalid token structure');
        }

        list($header, $payload, $signature) = $tokenParts;

        $header = json_decode(base64_decode($header), true);
        $payload = json_decode(base64_decode($payload), true);

        if ($this->verifySignature($header, $payload, $signature)) {
            return $payload;
        } else {
            throw new Exception('Invalid token signature');
        }
    }

    protected function verifySignature($header, $payload, $signature): bool
    {
        $signatureProvided = base64_decode(str_replace(['-', '_'], ['+', '/'], $signature));

        $data = base64_encode(json_encode($header)) . '.' . base64_encode(json_encode($payload));

        return openssl_verify($data, $signatureProvided, $this->publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}