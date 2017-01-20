<?php
namespace SlimBootstrap\Authentication;

use \Http;
use \Lcobucci;
use \Monolog;
use \SlimBootstrap;

class Sombra extends SlimBootstrap\Authentication\Jwt
{
    /**
     * @var string
     */
    private $sombraUrl = '';

    /**
     * @var Http\Caller
     */
    private $httpCaller = null;

    /**
     * @param string          $sombraUrl
     * @param string          $encryption
     * @param array           $clientDataClaims
     * @param array           $claimsConfig
     * @param \Http\Caller    $httpCaller
     * @param \Monolog\Logger $logger
     */
    public function __construct(
        string $sombraUrl,
        string $encryption,
        array $clientDataClaims,
        array $claimsConfig,
        Http\Caller $httpCaller,
        Monolog\Logger $logger
    ) {
        parent::__construct('', $encryption, $clientDataClaims, $claimsConfig, $logger);

        $this->sombraUrl  = $sombraUrl;
        $this->httpCaller = $httpCaller;
    }

    /**
     * @param string $encryptionName
     *
     * @return Lcobucci\JWT\Signer
     */
    protected function determineSigner(string $encryptionName): Lcobucci\JWT\Signer
    {
        return new Lcobucci\JWT\Signer\Ecdsa\Sha256();
    }

    /**
     * @return string
     *
     * @throws SlimBootstrap\Exception
     */
    protected function getPublicKey(): string
    {
        $result = $this->httpCaller->get($this->sombraUrl);

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
}
