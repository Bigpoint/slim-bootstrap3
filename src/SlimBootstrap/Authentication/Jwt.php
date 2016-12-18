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
    public function authenticate(Message\ServerRequestInterface $request)
    {
        try {
            $publicKey = $this->getPublicKey();
            $token     = $this->getToken($request);

            $this->verifyToken($token, $publicKey);
            $this->validateToken($token);

            return [
                'clientId' => $token->getClaim('name'),
                'role' => $token->getClaim('role'),
            ];
        } catch (\InvalidArgumentException $exception) {
            $this->logger->addInfo($exception->getMessage());

            throw new SlimBootstrap\Exception('JWT invalid', 401, Monolog\Logger::INFO);
        }
    }

    /**
     * @return string
     *
     * @throws SlimBootstrap\Exception
     */
    private function getPublicKey(): string
    {
        $result = $this->httpCaller->get(
            $this->providerUrl
        );

        if (200 !== $result['responseCode']) {
            throw new SlimBootstrap\Exception(
                \sprintf(
                    'provider returned invalid response: %s - %s',
                    $result['responseCode'],
                    \var_export($result['body'], true)
                ),
                401,
                Monolog\Logger::ERROR
            );
        }

        $data = \json_decode($result['body'], true);

        if (false === \is_array($data)
            || false === \array_key_exists('Pubkey', $data)
            || true === empty($data['Pubkey'])
        ) {
            throw new SlimBootstrap\Exception(
                \sprintf(
                    'provider returned invalid result: %s - %s',
                    $result['responseCode'],
                    \var_export($result['body'], true)
                ),
                401,
                Monolog\Logger::ERROR
            );
        }

        return $data['Pubkey'];
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
    private function verifyToken(Lcobucci\JWT\Token $token, string $publicKey)
    {
        $signer = new Lcobucci\JWT\Signer\Ecdsa\Sha256();
        $result = $token->verify($signer, $publicKey);

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
