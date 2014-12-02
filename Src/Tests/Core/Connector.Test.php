<?php

namespace Src\Core;

function header($string)
{
    return $string;
}

namespace Src\Tests\Core;

use \Src\Tests\ExtendedTestCase;

/**
 * @coversDefaultClass Src\Core\Connector
 */
class ConnectorTest extends ExtendedTestCase {

    const APIKEY = 'KEY';
    const APISECRET = 'SECRET';

    /**
     * @covers ::__construct
     * @covers ::fetchToken
     * @covers ::hasValidToken
     */
    public function __construct()
    {
        $this->setTestObject('\\Src\\Core\\Connector', array(
            'apiKey' => self::APIKEY,
            'apiSecret' => self::APISECRET,
            'curler' => $this->getMock('\Src\Core\Curler')
        ));
    }

    /**
     * @covers ::getInstance
     * @covers ::fetchToken
     * @covers ::hasValidToken
     */
    public function test_getInstance()
    {
        $this->setTestResult(\Src\Core\Connector::getInstance(self::APIKEY, self::APISECRET))
            ->assert('isobject', '\\Src\\Core\\Connector');
    }

    /**
     * @covers ::login
     */
    public function test_login_notToken()
    {
        $this->setTestObjectProperty('token', '');

        // Token is required
        $this->setExpectedException('\Exception', 'fetchToken()');

        $this->tm('login')->with('something@example.com');
    }

    /**
     * @covers ::login
     * @covers ::parseParams
     * @covers ::redirect
     */
    public function test_login_withToken()
    {
        $this->setTestObjectProperty('token', 'fudged_token');

        $this->tm('login')->with('something@example.com')
                ->assert('true', 'http://auth.fandi.st/api/connector/login/fudged_token/something%40example.com');
    }

    /**
     * @covers ::logout
     */
    public function test_logout_noToken()
    {
        $this->setTestObjectProperty('token', '');

        // Token is required
        $this->setExpectedException('\Exception', 'fetchToken()');

        $this->tm('login')->with('something@example.com');
    }

    /**
     * @covers ::logout
     * @covers ::parseParams
     * @covers ::redirect
     */
    public function test_logout_withToken()
    {
        $this->setTestObjectProperty('token', 'fudged_token');

        $this->tm('logout')->with('something@example.com')
                ->assert('true', 'http://auth.fandi.st/api/connector/logout/fudged_token');
    }
}