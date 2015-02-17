Fandist Connect
===============

If you are using an embedded campaign, you can reduce the steps to share for a user logged into your website by using Fandist Connect. You simply send us the email address of the current user and we will log them into fandist seamlessly, allowing that user to share without having to explicitly sign up.

There are two choices when using Fandist Connect: using the [PHP SDK](#phpsdk), or [calling our connect endpoints](#connectendpoints) directly. The former is quicker and easier to inregrate, however the latter allows you to use whichever language is best for you.

The first step, regardless of the route you choose is to [register a fandist application](#registserapp).

## <a id="registerapp"></a>Register a fandist application
Navigate to the [application list](http://my.fandi.st/api/app/list) page in your fandist account and click the *New App* button to show the new application form. Fill in the form with the desired details for your application:
 * **App Name** This is a unique name to identify your application
 * **Login Landing URL** This is the URL on *your site* that you want your user to land on after they have been signed in to fandist (browser redirect)
 * **Logout Landing URL** This is the URL on *your site* that you want your user to land on after they have been signed out of fandist (browser redirect)
 * **Session Time Out** This is the timeout (in seconds) for the fandist login. In this version of fandist, it is currently set to 1 hour (3600 seconds) and cannot be changed.

Click the **Submit** button and the application will be registered. You will be redirected back to the application list, where your application key and secret will be displayed. Make sure you keep these safe as they are used to authenticate your application to fandist. Your application secret will not be revealed again so ensure you store it safely. You must ensure that your secret is not revealed to users.

The next step is to choose either the [PHP SDK](#phpsdk) implementation or to [call our endpoints](#connectendpoints) directly using whatever language is most convenient for you.

## <a id="phpsdk"></a> PHP SDK

You simply need to include [Connector.php](https://github.com/digitalanimal/Fandist.Api.Wrapper/blob/master/Src/Core/Connector.php) in your application. This class then needs instantiating and has two public methods available, `login` and `logout`.

* `$instance->login(String $email)` -  logs the user defined by `$email`  into fandist and redirects back to your application, based on the application configuration.

* `$instance->logout();` - logs the current user out of fandist and redirects back to your application, based on the application configuration.

An example implementation follows. Note that you will need to replace the variables `$apiKey` and `$apiSecret` with those generated when you create a fandist application.

### 1. Instantiate the connector
```php
// Your/Application/Example/ConnectToFandist.php
require_once 'Src/Connector.php';

use FandistApiWrapper\Src\Connector;

// Set the api key and secret
$apiKey = 'xxx';
$apiSecret = 'xxx';

// Instantiate new fandist connector object
$connector = Connector::getInstance($apiKey, $apiSecret);
```

### 2. User login call
```php
// Your/Application/Example/ConnectToFandist.php

// Instantiate new fandist connector object
$connector = ...

// Log user in with email user@example.com
$connector->login('user@example.com');
```

### 3. User logout call
```php
// Your/Application/Example/ConnectToFandist.php

// Instantiate new fandist connector object
$connector = ...

// Log user out, assuming that the user is already logged in
$connector->logout();
```

## <a id="connectendpoints"></a>Raw API endpoints

#### 1. Retrieve an application token
Now that you have registered an application, you will need to authenticate your application with fandist. The route which does this is:
```bash
$ curl http://my.fandi.st/api/auth/{app_key}/{app_secret}
```
This returns a string which is your application token. This token must be sent with all requests to sign users in and out of fandist.

#### 2. Fandist user sign-in
Once your application has an application token, it can call the fandist connector to sign users in or out.
```bash
$ curl http://my.fandi.st/api/connector/login/{application_token}/{user_email_address}
```
This will result in the specified user being signed in to fandist and then the browser will redirect to the URI defined in your application configuration.

#### 3. Fandist user sign-out
This works much like the sign-on call, but does not require an email address as it simply ends the current fandist session.
```bash
$ curl http://my.fandi.st/api/connector/logout/{application_token}
```
This will result in the current user being signed out of fandist and then the browser will redirect to the URI defined in your application configuration.

## Variables
| Variable               | Definition | Example |
|------------------------|------------|---------|
| {app_key}              | Unique identifier for your application, obtained when application is registered. | `abcDEF1234g=` |
| {app_secret}           | Authorisation code to prove the application is the application it claims to be. | `abc123...456DEF` |
| {application\_token}   | A token used to identify the pre-authorised application so the key and secret do not have to be sent with every request to fandist. Tokens can be revoked upon request. | `abc123...456DEF` |
| {user\_email\_address} | The email address of the user that should be signed in/out of fandist. | `user@example.com ` |

## Api endpoints example

For this example I am starting with a simple express application in node. This application has a signup, a login and a logout. The first step is to register your application with fandi.st. Once you have created a fandi.st account and your app has been registered be sure to take a copy of your **app_key** and **app_secret**.

Once you have these you will need to install [request](https://github.com/request/request) from npm to handle the request object when logging in and out of fandist.
```bash
$ npm install request --save
```

This will add the latest version of request to your package.json file. 

#### 1. Handle Login
Once this has been installed you will need to find where in your application you handle the login.

After you have authenticated the user on you end, *note: fandist does not authenticate your users and will login whoever you pass to us*, and add the [request](https://github.com/request/request) syntax to the file.

Place this at the top of the file to require the [request](https://github.com/request/request) object
```bash
var request = require('request');
```

After your user authentification place the request function to curl fandist using the **app_key** and **app_secret** to retrieve current application token (these expire every 60 minutes) and use the returned value to redirect to the fandist login using the retrieved application token and the users email address from your system.
```bash
request('http://my.fandi.st/api/auth/{app_key}/{app_secret}', function (error, response, token) {
  if (!error && response.statusCode == 200) {
    
    // Create loginUrl using the returned token and the {user_email_address} from your user
    var loginUrl = 'http://my.fandi.st/api/connector/login/' + token + '/' + {user_email_address}
    
    // Redirect to the loginUrl created above
    res.redirect(loginUrl);
  }
})
```

Once complete you user will not only be logged into your system but to fandist as well.

#### 2. Handle Logout
The logout process is similar to the login process, except it does not require the users email address. 

The file you have placed your logout function place the [request](https://github.com/request/request) object at the top (if this is the same file as your login, please ignore this step).
```bash
var request = require('request');
```

Inside your user logout function place the request function to curl fandist using the **app_key** and **app_secret** to retrieve current application token (these expire every 60 minutes) and use the returned value to redirect to the fandist logout using the retrieved application token.
```bash
request('http://my.fandi.st/api/auth/{app_key}/{app_secret}', function (error, response, token) {
  if (!error && response.statusCode == 200) {
    
    // Create loginUrl using the returned token and the {user_email_address} from your user
    var logoutUrl = 'http://my.fandi.st/api/connector/logout/' + token
    
    // Redirect to the loginUrl created above
    res.redirect(logoutUrl);
  }
})
```
