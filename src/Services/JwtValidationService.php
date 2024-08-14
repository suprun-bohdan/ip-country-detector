<?php

namespace wtg\IpCountryDetector\Services;

use Exception;

class JwtValidationService
{
    protected string $publicKey;

    public function __construct(string $publicKeyPath)
    {
        $this->publicKey = file_get_contents($publicKeyPath);
    }

    /**
     * @throws Exception
     */
    public function parseToken(string $jwt): array
    {
        $tokenParts = explode('.', $jwt);

        if (count($tokenParts) !== 3) {
            throw new Exception('Invalid token structure');
        }

        list($header, $payload, $signature) = $tokenParts;

        $header = json_decode(base64_decode($header), true);
        $payload = json_decode(base64_decode($payload), true);

        if (isset($payload['exp']) && time() >= $payload['exp']) {
            throw new Exception('Token has expired');
        }

        if (isset($payload['nbf']) && time() < $payload['nbf']) {
            throw new Exception('Token is not valid yet');
        }

        if (!$this->verifySignature($header, $payload, $signature)) {
            throw new Exception('Invalid token signature');
        }

        return $payload;
    }

    /**
     * Checking token signature
     *
     * @param array $header
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    protected function verifySignature(array $header, array $payload, string $signature): bool
    {
        $data = base64_encode(json_encode($header)) . '.' . base64_encode(json_encode($payload));
        $decodedSignature = base64_decode($signature);

        return openssl_verify($data, $decodedSignature, $this->publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}
