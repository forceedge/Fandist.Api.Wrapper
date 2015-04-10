<?php

namespace Src\Core;


class Curler {

    private $headers;

    /**
     *
     * @param type $url
     * @return type
     */
    public function curl($url)
    {
    	Connector::debugMessage('<br />Curling url: '. $url . '<br />');

        $ch = curl_init();
        $this->configureCurl(urlencode($url), $ch);
        $result = curl_exec($ch);

        if( ! $result )
        {
            trigger_error(curl_error($ch));
        }

        Connector::debugMessage('Curl raw result: '. $result . '<br />');

        // Separate body from headers
        list($this->headers, $result) = explode("\r\n\r\n", $result, 2);

        Connector::debugMessage('Curl headers: '. $this->headers);
        Connector::debugMessage('Curl result: '. $result);

        curl_close($ch);

        return $result;
    }

    public function injectCURLCookiesInResponse()
    {
        $cookies = $this->getcURLCookies();

        // Set all cookies in response
        foreach($cookies as $name => $value)
        {
	    Connector::debugMessage('Setting cookie: '. $name . ':' . $value);

            // Set the php session id
            setcookie($name, $value);
        }

        return $cookies;
    }

    private function getcURLCookies()
    {
        $cookies = $matches = $temp = array();
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $this->headers, $matches);

        Connector::debugMessage('Cookies matched from header', $matches[1]);

        foreach($matches[1] as $cookie)
        {
            parse_str($cookie, $temp);
            $cookies = array_merge($cookies, $temp);
        }

        Connector::debugMessage('Extracted cookies from header', $cookies);

        return $cookies;
    }

    private function configureCurl($url, $ch)
    {
        $defaults = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_HEADER => TRUE, // Not to be used in conjunction with CURLOPT_HEADERFUNCTION
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => 'COOIKIE_JAR',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        );

        curl_setopt_array($ch, $defaults);

        return $this;
    }
}