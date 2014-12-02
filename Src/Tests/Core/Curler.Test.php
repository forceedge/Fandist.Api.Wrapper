<?php

namespace Src\Core;

function curl_exec($ch)
{
    return "Headers:: something here\r\n\r\nfudged_curl";
}

function setcookie($name, $value)
{
    return $name . $value;
}

namespace Src\Tests\Core;

use Src\Tests\ExtendedTestCase;

/**
 * @coversDefaultClass \Src\Core\Curler
 */
class CurlerTests extends ExtendedTestCase {

    public function __construct()
    {
        $this->setTestObject('\Src\Core\Curler');
    }

    /**
     * @covers ::curl
     * @covers ::configureCurl
     */
    public function test_curl()
    {
        $url = 'http://someurl.com';

        $this->tm('curl')->with($url)
                ->assert('true', 'fudged_curl');
    }

    /**
     * @covers ::injectCURLCookiesInResponse
     * @covers ::getcURLCookies
     */
    public function test_injectCURLCookiesInResponse()
    {
        $headers = 'Set-Cookie: PHPSESSID=hlaksjdhfahslkdhfahsdkfh;';

        $this->setTestObjectProperty('headers', $headers);
        $this->tm('injectCURLCookiesInResponse')->with()
                ->assert('isarray')
                ->assert('arrayHasKey', 'PHPSESSID')
                ->assert('true', '[PHPSESSID]==hlaksjdhfahslkdhfahsdkfh');
    }
}