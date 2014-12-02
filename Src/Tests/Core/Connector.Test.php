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
    const TOKEN = 'asdf9798asdf987as7df987s98d7faoisdfas08df08as90d8fas9df';

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

        $this->tm('logout')->with('something@example.com');
    }

    /**
     * @covers ::logout
     * @covers ::parseParams
     * @covers ::redirect
     */
    public function test_logout_withToken()
    {
        $this->setTestObjectProperty('token', 'fudged_token');

        $this->tm('logout')->with()
                ->assert('true', 'http://auth.fandi.st/api/connector/logout/fudged_token');
    }

    /**
     * @covers ::fetchToken
     * @covers ::hasValidToken
     */
    public function test_fetchToken_invalidToken()
    {
        $this->setExpectedException('\Exception', 'Invalid token');

        $url = 'http://auth.fandi.st/api/auth/KEY/SECRET';
        $curler = $this->getMock('\Src\Core\Curler');
        $this->setmo($curler)
                ->mm('curl', [
                    'with' => $url,
                    'will' => $this->returnValue('{')
                ]);

        $this->setTestObjectProperty('curler', $curler);
        $this->tm('fetchToken')->with();
    }

    /**
     * @covers ::fetchToken
     * @covers ::hasValidToken
     */
    public function test_fetchToken_invalidToken2()
    {
        $this->setExpectedException('\Exception', 'Invalid token');

        $url = 'http://auth.fandi.st/api/auth/KEY/SECRET';
        $curler = $this->getMock('\Src\Core\Curler');
        $this->setmo($curler)
                ->mm('curl', [
                    'with' => $url,
                    'will' => $this->returnValue('you are not authorised')
                ]);

        $this->setTestObjectProperty('curler', $curler);
        $this->tm('fetchToken')->with();
    }

    /**
     * @covers ::fetchToken
     * @covers ::hasValidToken
     */
    public function test_fetchToken_invalidToken3()
    {
        $this->setExpectedException('\Exception', 'Invalid token');

        $url = 'http://auth.fandi.st/api/auth/KEY/SECRET';
        $curler = $this->getMock('\Src\Core\Curler');
        $this->setmo($curler)
                ->mm('curl', [
                    'with' => $url,
                    'will' => $this->returnValue('')
                ]);

        $this->setTestObjectProperty('curler', $curler);
        $this->tm('fetchToken')->with();
    }

    /**
     * @covers ::fetchToken
     * @covers ::hasValidToken
     */
    public function test_fetchToken()
    {
        $url = 'http://auth.fandi.st/api/auth/KEY/SECRET';
        $curler = $this->getMock('\Src\Core\Curler');
        $this->setmo($curler)
                ->mm('curl', [
                    'with' => $url,
                    'will' => $this->returnValue(self::TOKEN)
                ]);

        $this->setTestObjectProperty('curler', $curler);
        $this->tm('fetchToken')->with()
                ->assert('true', self::TOKEN);
    }
}