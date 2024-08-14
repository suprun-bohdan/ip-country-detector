<?php

namespace wtg\IpCountryDetector\Services;

use Exception;

class JWTService
{
    protected string|false $publicKey;

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

        $this->validateTokenTimestamps($payload);

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

    /**
     * Check exp
     *
     * @param array $payload
     * @throws Exception
     */
    protected function validateTokenTimestamps(array $payload): void
    {
        $currentTime = time();

        if (isset($payload['exp']) && $currentTime >= $payload['exp']) {
            throw new Exception('Token has expired');
        }

        if (isset($payload['nbf']) && $currentTime < $payload['nbf']) {
            throw new Exception('Token is not valid yet');
        }

        if (isset($payload['iat']) && $currentTime < $payload['iat']) {
            throw new Exception('Token issued in the future');
        }
    }
}
