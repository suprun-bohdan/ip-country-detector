<?php

namespace wtg\IpCountryDetector\Services;

use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\Clock\SystemClock;

class JWTService
{
    protected Configuration $config;

    public function __construct(string $publicKeyPath, string $privateKeyPath = null)
    {
        $publicKey = InMemory::file($publicKeyPath);
        $privateKey = $privateKeyPath ? InMemory::file($privateKeyPath) : InMemory::plainText('');

        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            $privateKey,
            $publicKey
        );
    }

    /**
     * @throws Exception
     */
    public function parseToken($jwt): array
    {
        $token = $this->config->parser()->parse($jwt);

        $this->validateToken($token);

        return $token->claims()->all();
    }

    /**
     * @throws Exception
     */
    protected function validateToken(Plain $token): void
    {
        $signedWith = new SignedWith($this->config->signer(), $this->config->verificationKey());
        $validAt = new LooseValidAt(SystemClock::fromUTC());

        $constraints = [$signedWith, $validAt];

        if (!$this->config->validator()->validate($token, ...$constraints)) {
            throw new Exception('Token validation failed');
        }
    }
}
