<?php

namespace FandistApiWrapper\Src;

class Connector {

    const AUTH_URL = '/api/auth/{key}/{secret}';
    const LOGIN_URL = '/api/connector/login/{token}/{email}';
    const STATUS_URL = '/api/connector/status/{token}';
    const LOGOUT_URL = '/api/connector/logout/{token}';

    private $key, $secret, $token, $domain;
    private static $fandist;
    
    /**
     *
     * @param type $key
     * @param type $secret
     */
    public function __construct($key, $secret, $fandist_domain = 'http://auth.fandi.st')
    {
        // Set object init properties
        $this->key = $key;
        $this->secret = $secret;
        $this->domain = $fandist_domain;
    }
    
    // --------------------------- Public methods --------------------------- //
    
    public static function getInstance($apiKey, $apiSecret, $fandist_domain = 'http://auth.fandi.st')
    {
        // If already instantiated return
        if(self::$fandist)
            return self::$fandist;
    
        // New Object for this class
        self::$fandist = new self($apiKey, $apiSecret, $fandist_domain);
        
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
        $parsedUrl = $this->parseParams(self::LOGIN_URL);
        
        // Set url-encoded email in url
        $url = str_replace('{email}', urlencode($email), $parsedUrl);

        // Go to url with browser to login as the browser needs the cookie to bind the session
        $this->redirect($url);
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
        $url = $this->parseParams(self::LOGOUT_URL);

        // Destroy session by going to the url via browser
        $this->redirect($url);
    }

    /**
     *
     * @return type
     * @throws \Exception
     */
    public function status()
    {
        if(! $this->token)
        {
            throw new \Exception('Token is required to make the login call with fandist API, call getToken() first');
        }

        // Set token in url
        $url = $this->parseParams(self::STATUS_URL);

        // Get the status of the fandist session, logged in/authenticated/not logged in
        return $this->curl($url);
    }
    
    // --------------------------- End of Public methods --------------------------- //

    /**
     *
     * @return \Fandist\Connector
     */
    private function fetchToken()
    {
        // Place key and secret in the url
        $url = $this->parseParams(self::AUTH_URL);
        
        // cURL to get a valid token
        $this->token = $this->curl($this->domain . $url);
        
        if(! $this->hasValidToken())
        {
            throw new \Exception("Invalid token provided '{$this->token}'");
        }
        
        return $this->token;
    }
 
    /**
     *
     * @return \Fandist\Connector|boolean
     */
    private function hasValidToken()
    {
        if($this->token and (strpos($this->token, '{') === false and strpos($this->token, ' ') === false))
        {
            return $this;
        }

        return false;
    }
    
    private function redirect($uri)
    {
        header('Location: ' . $this->domain . $uri);
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
     *
     * @param type $url
     * @return type
     */
    private function curl($url)
    {    
        $ch = curl_init();
        $this->configureCurl($url, $ch);
        $result = curl_exec($ch);

        if( ! $result )
        {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    private function configureCurl($url, $ch)
    {
        $defaults = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPGET => TRUE,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array('Accept: application/json'),
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => 'COOIKIE_JAR'
        );
        
        curl_setopt_array($ch, $defaults);

        return $this;
    }
}