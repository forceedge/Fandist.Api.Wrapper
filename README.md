Fandist.Api.Wrapper
===================

Wrapper for fandist endpoints, allows you to quickly implement login and logout with fandist tech

Calls available
 - login ( String $email ) // Log into fandist, redirects based on app configuration
 - logout () // logout of fandist, redirects based on app configuration
 - status () // Gives a status - loggedin/authenticated

Usage/Implementation
--------------------

Instantiate
```
// Your/Application/Example/ConnectToFandist.php
require_once 'Src/Connector.php';

use FandistApiWrapper\Src\Connector;

// Set the api key ad secret
$apiKey = 'xxx';
$apiSecret = 'xxx';

// Instantiate new fandist connector object
$connector = Connector::getInstance($apiKey, $apiSecret);
```

Login call
```
// Your/Application/Example/ConnectToFandist.php

// Instantiate new fandist connector object
$connector = ...

// Log user in with email abc@example.com
$connector->login('abc@example.com');
```

Logout call
```
// Your/Application/Example/ConnectToFandist.php

// Instantiate new fandist connector object
$connector = ...

// Log user out, assuming that the user is already logged in
$connector->logout();
```

Status call
```
// Your/Application/Example/ConnectToFandist.php

// Instantiate new fandist connector object
$connector = ...

// Get the status from fandist connect of the current user
$connector->status();
```