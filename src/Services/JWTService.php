<?php

namespace wtg\IpCountryDetector\Services;

use Exception;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JWTService
{
    protected Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
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
