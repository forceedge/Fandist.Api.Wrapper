<?php

namespace Src\Core;

require_once (__DIR__ . '/Curler.php');

class Connector {

    const PROTOCOL = 'http';
    const AUTH_URL = '/api/auth/{key}/{secret}';
    const LOGIN_URL = '/api/connector/login/{token}/{email}';
    const STATUS_URL = '/api/connector/status/{token}';
    const LOGOUT_URL = '/api/connector/logout/{token}';
    const DEBUG = false;

    private $key, $secret, $token, $domain, $curler, $testing;
    private static $fandist;

    /**
     *
     * @param type $key
     * @param type $secret
     */
    public function __construct($key, $secret, $curler, $fandist_domain = 'auth.fandi.st', $testing = false)
    {
        // Set object init properties
        $this->key = $key;
        $this->secret = $secret;
        $this->curler = $curler;
        $this->domain = $fandist_domain;
        $this->testing = $testing;
    }

    // --------------------------- Public methods --------------------------- //

    public static function getInstance($apiKey, $apiSecret, $fandist_domain = 'auth.fandi.st')
    {
        // If already instantiated return
        if(self::$fandist)
            return self::$fandist;

        // New Object for this class
        self::$fandist = new self($apiKey, $apiSecret, new Curler(), $fandist_domain);

        // Fetch token for following calls
        self::$fandist->fetchToken();

        return self::$fandist;
    }

    /**
     *
     * @param type $email
     * @throws \Exception
     */
    public function login($email)
    {
        // If token isnt defined throw an exception
        if(! $this->token)
        {
            throw new \Exception('Token is required to make the login call with fandist API, call fetchToken() first');
        }

        // Parse url params
        $parsedUrl = $this->getProperlyFormattedUrl(self::LOGIN_URL);
        
        // Set url-encoded email in url
        $url = str_replace('{email}', urlencode($email), $parsedUrl);

        // Go to url with browser to login as the browser needs the cookie to bind the session
        $this->redirect($url);

        return $url;
    }

    /**
     *
     * @throws \Exception
     */
    public function logout()
    {
        if(! $this->token)
        {
            throw new \Exception('Token is required to make the logout call with fandist API, call fetchToken() first');
        }

        // Set token in url
        $url = $this->getProperlyFormattedUrl(self::LOGOUT_URL);

        // Destroy session by going to the url via browser
        $this->redirect($url);

        return $url;
    }

    // --------------------------- End of Public methods --------------------------- //

    /**
     *
     * @throws \Exception
     * Sort of comes between private and public method
     */
    private function fetchToken()
    {
        // Place key and secret in the url
        $url = $this->getProperlyFormattedUrl(self::AUTH_URL);

        Connector::debugMessage('Fetching Token from: ' . $url);

        // cURL to get a valid token
        $this->token = $this->curler->curl($url);

        if(! $this->hasValidToken())
        {
            throw new \Exception("Invalid token provided '{$this->token}'");
        }

        Connector::debugMessage('Valid token fetched: ' . $this->token);

        return $this->token;
    }

    /**
     *
     * @param type $uri
     * @return type
     */
    private function getProperlyFormattedUrl($uri)
    {
        $formattedUri = $this->parseParams($uri);

        return self::PROTOCOL . '://' . $this->domain . $formattedUri;
    }

    /**
     *
     * @return \Src\Core\Connector|boolean
     */
    private function hasValidToken()
    {
        if($this->token and (strpos($this->token, '{') === false and strpos($this->token, ' ') === false))
        {
            return $this;
        }

        return false;
    }

    private function redirect($url)
    {
        header('Location: ' . $url);

        if(! $this->testing)
            exit();
    }

    /**
     *
     * @param type $string
     * @return type
     */
    private function parseParams($string)
    {
        return str_replace(array(
                '{token}',
                '{key}',
                '{secret}'
            ),
            array(
                $this->token,
                $this->key,
                $this->secret
            ),
            $string
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function debugMessage($message, $variable = null)
    {
    	if(Connector::DEBUG)
    	{
            echo $message . '<br />';

            if($variable)
            {
                echo '<pre>';
                print_r($variable);
                echo '</pre>';
            }
    	}
    }
}
