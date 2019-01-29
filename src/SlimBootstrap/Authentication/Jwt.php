<?php
namespace SlimBootstrap\Authentication;

use \Lcobucci;
use \Monolog;
use \Psr\Http\Message;
use \SlimBootstrap;

class Jwt implements SlimBootstrap\Authentication
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
        try {
            $publicKey = $this->getPublicKey();
            $token     = $this->getToken($request);

            $this->verifyToken($token, $publicKey);
            $this->validateToken($token);

            return [
                'clientId' => $token->getClaim($this->clientDataClaims['clientId']),
                'role'     => $token->getClaim($this->clientDataClaims['role']),
            ];
        } catch (\Throwable $exception) {
            $this->logger->addInfo($exception->getMessage());

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
     * @param Message\ServerRequestInterface $request
     *
     * @return Lcobucci\JWT\Token
     */
    private function getToken(Message\ServerRequestInterface $request): Lcobucci\JWT\Token
    {
        $tokenString = \str_replace('bearer ', '', $request->getHeaderLine('Authorization'));
        $jwtParser   = new Lcobucci\JWT\Parser();

        return $jwtParser->parse($tokenString);
    }

    /**
     * @param Lcobucci\JWT\Token $token
     * @param string             $publicKey
     *
     * @throws SlimBootstrap\Exception
     */
    private function verifyToken(Lcobucci\JWT\Token $token, string $publicKey)
    {
        $result = $token->verify($this->determineSigner($this->encryption), $publicKey);

        if (false === $result) {
            throw new SlimBootstrap\Exception(
                'JWT signature invalid',
                401,
                Monolog\Logger::INFO
            );
        }
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

    /**
     * @param Lcobucci\JWT\Token $token
     *
     * @throws SlimBootstrap\Exception
     */
    private function validateToken(Lcobucci\JWT\Token $token)
    {
        $data = new Lcobucci\JWT\ValidationData();

        foreach ($this->claimsConfig as $claim => $value) {
            $function = \sprintf('set%s', \ucfirst($claim));

            if (true === \method_exists($data, $function)) {
                $data->$function($value);
            }
        }

        $result = $token->validate($data);

        if (false === $result) {
            throw new SlimBootstrap\Exception(
                'JWT validation failed',
                401,
                Monolog\Logger::INFO
            );
        }
    }
}
