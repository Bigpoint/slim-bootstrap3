<?php
namespace SlimBootstrap\Authentication;

use \Http;
use \Monolog;
use \SlimBootstrap;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Monolog\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock = null;

    /**
     * @var SlimBootstrap\Authentication\Factory
     */
    private $authenticationFactory = null;

    public function setUp()
    {
        parent::setUp();

        $this->loggerMock = $this->getMockBuilder('\Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $this->authenticationFactory = new SlimBootstrap\Authentication\Factory(
            $this->loggerMock
        );
    }

    public function testCreateOauth()
    {
        $actual = $this->authenticationFactory->createOauth(
            [
                'oauth' => [
                    'authenticationUrl' => 'mockUrl',
                ]
            ]
        );

        $this->assertInstanceOf('\SlimBootstrap\Authentication\Oauth', $actual);
    }

    /**
     * @param array $config
     *
     * @dataProvider createOauthInvalidConfigDataProvider
     *
     * @expectedException \SlimBootstrap\Exception
     * @expectedExceptionMessage "oauth" config invalid
     */
    public function testCreateOauthInvalidConfig(array $config)
    {
        $this->authenticationFactory->createOauth($config);
    }

    /**
     * @return array
     */
    public function createOauthInvalidConfigDataProvider(): array
    {
        return [
            [
                'config' => [],
            ],
            [
                'config' => [
                    'oauth' => [],
                ],
            ],
            [
                'config' => [
                    'oauth' => [
                        'authenticationUrl' => null,
                    ],
                ],
            ],
            [
                'config' => [
                    'oauth' => [
                        'authenticationUrl' => [],
                    ],
                ],
            ],
        ];
    }

    public function testCreateJwt()
    {
        $actual = $this->authenticationFactory->createJwt(
            [
                'jwt' => [
                    'publicKey' => 'mockSecret',
                    'claims'    => [
                        'issuer' => 'mockIssuer',
                    ],
                ]
            ]
        );

        $this->assertInstanceOf('\SlimBootstrap\Authentication\Jwt', $actual);
    }

    /**
     * @param array $config
     *
     * @dataProvider createJwtInvalidConfigDataProvider
     *
     * @expectedException \SlimBootstrap\Exception
     * @expectedExceptionMessage "jwt" config invalid
     */
    public function testCreateJwtInvalidConfig(array $config)
    {
        $this->authenticationFactory->createJwt($config);
    }

    /**
     * @return array
     */
    public function createJwtInvalidConfigDataProvider(): array
    {
        return [
            [
                'config' => [],
            ],
            [
                'config' => [
                    'jwt' => [],
                ],
            ],
            [
                'config' => [
                    'jwt' => [
                        'providerUrl' => [],
                    ],
                ],
            ],
            [
                'config' => [
                    'jwt' => [
                        'providerUrl' => '',
                    ],
                ],
            ],
            [
                'config' => [
                    'jwt' => [
                        'providerUrl' => 'mockUrl',
                    ],
                ],
            ],
            [
                'config' => [
                    'jwt' => [
                        'providerUrl' => '',
                        'claims'      => null,
                    ],
                ],
            ],
            [
                'config' => [
                    'jwt' => [
                        'providerUrl' => 'mockUrl',
                        'claims'      => [],
                    ],
                ],
            ],
        ];
    }
}
