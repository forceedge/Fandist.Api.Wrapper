Fandist.Api.Wrapper
===================

Wrapper for fandist endpoints, allows you to quickly implement login and logout with fandist tech

Usage/Implementation
--------------------

```
<?php

require_once 'Fandist.Api.Wrapper.php';

use StreetTeam\Classes\Api\Fandist\Connector;


class FandistConnectionHandler {

	// Set apikey and secret
	const API_KEY = 'xxxxxxxx';
	const API_SECRET = 'xxxxxxxx';
	
	private function getConnector()
	{
		try {
			// Get fandist connector object
			return Connector::getInstance(self::API_KEY, self::API_SECRET);
		}
		catch(\Exception $e) {
			// Gracefully fail here
			echo $e->getMessage();
		}
	}

	/**
	 * Login to fandist with an email
	 */
	public function login() 
	{
		// Get the fandist connector
		$fandist = $this->getConnector();
		
		// login to fandist with an email
		$fandist->login($email);
	}
	
	/**
	 *
	 */
	public function logout()
	{
		// Get the fandist connector
		$fandist = $this->getConnector();
		
		// login to fandist with an email
		$fandist->logout();
	}
}
```