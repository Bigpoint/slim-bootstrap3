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
    private $providerUrl = '';

    /**
     * @var array
     */
    private $claimsConfig = [];

    /**
     * @var SlimBootstrap\Caller\Http
     */
    private $httpCaller = null;

    /**
     * @var Monolog\Logger
     */
    private $logger = null;

    /**
     * @TODO: get from jwt-provider.
     *
     * @var string
     */
    private $publicKey = "-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAElAfxdt6MZxXc4TsZROhm8QPnoDm5\nILVK9el6kU9xd+3Pnb3yOBsLTnuX9/x2c8HIQIoxEs8IlreBQndy3CvRJQ==\n-----END PUBLIC KEY-----\n";

    /**
     * @param string                    $providerUrl
     * @param array                     $claimsConfig
     * @param SlimBootstrap\Caller\Http $httpCaller
     * @param Monolog\Logger            $logger
     */
    public function __construct(
        string $providerUrl,
        array $claimsConfig,
        SlimBootstrap\Caller\Http $httpCaller,
        Monolog\Logger $logger
    ) {
        $this->providerUrl  = $providerUrl;
        $this->claimsConfig = $claimsConfig;
        $this->httpCaller   = $httpCaller;
        $this->logger       = $logger;
    }


    /**
     * @param Message\ServerRequestInterface $request The object holding information about the current request.
     *
     * @return string The clientId of the calling client.
     *
     * @throws SlimBootstrap\Exception When the passed access $token is invalid.
     */
    public function authenticate(Message\ServerRequestInterface $request): string
    {
        try {
            $token = $this->getToken($request);

            $this->verifyToken($token);
            $this->validateToken($token);

            var_dump('Name: ' . $token->getClaim('name'));
            var_dump('Role: ' . $token->getClaim('role'));
        } catch (\InvalidArgumentException $exception) {
            $this->logger->addInfo($exception->getMessage());

            throw new SlimBootstrap\Exception('JWT invalid', 401, Monolog\Logger::INFO);
        }

        die;
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
     *
     * @throws SlimBootstrap\Exception
     */
    private function verifyToken(Lcobucci\JWT\Token $token)
    {
        $signer = new Lcobucci\JWT\Signer\Ecdsa\Sha256();
        $result = $token->verify($signer, $this->publicKey);

        if (false === $result) {
            throw new SlimBootstrap\Exception('JWT invalid', 401, Monolog\Logger::INFO);
        }
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
            throw new SlimBootstrap\Exception('JWT invalid', 401, Monolog\Logger::INFO);
        }
    }
}
