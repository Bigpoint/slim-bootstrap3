<?php

namespace SlimBootstrap\Authentication;

use Lcobucci;
use Monolog;
use Psr\Http\Message;
use SlimBootstrap;

class Jwt implements SlimBootstrap\AuthenticationInterface
{
    /**
     * @var string
     */
    private $publicKey = '';

    /**
     * @var string
     */
    private $encryption = '';

    /**
     * @var array
     */
    private $clientDataClaims = [];

    /**
     * @var array
     */
    private $claimsConfig = [];

    /**
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * @param string         $publicKey
     * @param string         $encryption
     * @param array          $clientDataClaims
     * @param array          $claimsConfig
     * @param Monolog\Logger $logger
     */
    public function __construct(
        string $publicKey,
        string $encryption,
        array $clientDataClaims,
        array $claimsConfig,
        Monolog\Logger $logger
    ) {
        $this->publicKey        = $publicKey;
        $this->encryption       = $encryption;
        $this->clientDataClaims = $clientDataClaims;
        $this->claimsConfig     = $claimsConfig;
        $this->logger           = $logger;
    }

    /**
     * @param Message\ServerRequestInterface $request The object holding information about the current request.
     *
     * @return array
     *
     * @throws SlimBootstrap\Exception When the passed access $token is invalid.
     */
    public function authenticate(Message\ServerRequestInterface $request): array
    {
        $jwtConfig = Lcobucci\JWT\Configuration::forAsymmetricSigner(
            $this->determineSigner($this->encryption),
            Lcobucci\JWT\Signer\Key\InMemory::plainText($this->getPublicKey()), // setting signKey empty, as we only verify tokens here
            Lcobucci\JWT\Signer\Key\InMemory::plainText($this->getPublicKey())
        );
        $jwtConfig->setValidationConstraints(...$this->evaluateJwtContstrains());

        try {
            $tokenString = \str_ireplace('bearer ', '', $request->getHeaderLine('Authorization'));
            $token       = $jwtConfig->parser()->parse($tokenString);


            $jwtConfig->validator()->assert(
                $token,
                ...$jwtConfig->validationConstraints()
            );

            return [
                'clientId' => $token->claims()->get($this->clientDataClaims['clientId']),
                'role'     => $token->claims()->get($this->clientDataClaims['role']),
            ];
        } catch (\Throwable $exception) {
            $this->logger->warning($exception->getMessage());

            throw new SlimBootstrap\Exception('JWT invalid', 401, Monolog\Logger::INFO);
        }
    }

    /**
     * @return string
     *
     * @throws SlimBootstrap\Exception
     */
    protected function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $encryptionName
     *
     * @return Lcobucci\JWT\Signer
     */
    protected function determineSigner(string $encryptionName): Lcobucci\JWT\Signer
    {
        switch ($encryptionName) {
            case 'HS256':
                $encryption = new Lcobucci\JWT\Signer\Hmac\Sha256();
                break;
            case 'HS384':
                $encryption = new Lcobucci\JWT\Signer\Hmac\Sha384();
                break;
            case 'HS512':
                $encryption = new Lcobucci\JWT\Signer\Hmac\Sha512();
                break;
            case 'RS256':
                $encryption = new Lcobucci\JWT\Signer\Rsa\Sha256();
                break;
            case 'RS384':
                $encryption = new Lcobucci\JWT\Signer\Rsa\Sha384();
                break;
            case 'RS512':
                $encryption = new Lcobucci\JWT\Signer\Rsa\Sha512();
                break;
            case 'ES256':
                $encryption = new Lcobucci\JWT\Signer\Ecdsa\Sha256();
                break;
            case 'ES384':
                $encryption = new Lcobucci\JWT\Signer\Ecdsa\Sha384();
                break;
            case 'ES512':
                $encryption = new Lcobucci\JWT\Signer\Ecdsa\Sha512();
                break;
            default:
                $encryption = new Lcobucci\JWT\Signer\Ecdsa\Sha256();
                break;
        }

        return $encryption;
    }

    private function evaluateJwtContstrains(): array
    {
        $constrains = [
            new Lcobucci\JWT\Validation\Constraint\LooseValidAt(Lcobucci\Clock\SystemClock::fromUTC()),
        ];

        if (
            true === array_key_exists('audience', $this->claimsConfig)
            && false === empty($this->claimsConfig['audience'])
        ) {
            $constrains[] = new Lcobucci\JWT\Validation\Constraint\PermittedFor($this->claimsConfig['audience']);
        }

        if (
            true === array_key_exists('issuer', $this->claimsConfig)
            && false === empty($this->claimsConfig['issuer'])
        ) {
            $constrains[] = new Lcobucci\JWT\Validation\Constraint\IssuedBy($this->claimsConfig['issuer']);
        }

        return $constrains;
    }
}
