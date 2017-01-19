# Slim Bootstrap v3

These classes provide a simple way to bootstrap a slim v3 application with authentication.

It is an abstraction of the [Slim Framework](http://slimframework.com/) v3 and handles some stuff like output generation
in different formats and authentication / acl handling.


## Installation

slim-bootstrap3 is available via [packagist](https://packagist.org/packages/bigpoint/slim-bootstrap3):

~~~
composer require bigpoint/slim-bootstrap3
~~~


## Webserver configuration

In order to configure your webserver to pass all requests in a proper way to the slim application please read the
[Web Server](https://www.slimframework.com/docs/start/web-servers.html) section of the slim documentation.


## How to implement manually

In order to create a REST API based on this framework you need a structure similar to the following in your project:
~~~
├── composer.json
├── config
│   └── application.json
├── include
│   └── DummyApi
│       └── Endpoint
│           └── V1
│               ├── EndpointA.php
│               └── EndpointB.php
└── www
    └── index.php
~~~

### config/application.json
This file holds the main configuration for the implementation of the framework as a JSON. For documentation on the
`"monolog"` block in there see [MonologCreator](https://github.com/Bigpoint/monolog-creator).
This file doesn't have to be at this location, it is just the default location. If you change it you have to adapt
the example of the `www/index.php` that is shown later.

The following structure has to be present:

~~~json
{
  "displayErrorDetails": false,
  "cacheDuration": 900,
  "monolog": {
    "handler": {
      "stream": {
        "path": "/tmp/my-dummy-api.log"
      }
    },
    "logger": {
      "_default": {
        "handler": ["stream"],
        "level": "DEBUG"
      }
    }
  }
}
~~~

The `cacheDuration` defines the interval (in seconds) used for the cache expire headers of the response.

If the `displayErrorDetails` flag is set to true the slim framework will print out a stack trace if an error occurs.
Otherwise it will just show a "500 Internal Server Error".

### the include/ folder
This folder should contain your endpoint implementation. Read below about how to define an endpoint.

### www/index.php
This file is the main entry point for the application. Here is an example how this file should look like:

~~~php
<?php
require(__DIR__ . '/../vendor/autoload.php');

$applicationConfig = \json_decode(\file_get_contents(__DIR__ . '/../config/application.json'), true);

$loggerFactory = new \MonologCreator\Factory($applicationConfig['monolog']);
$logger        = $loggerFactory->createLogger('dummyApi');

\Monolog\ErrorHandler::register($logger);

$slimBootstrap = new \SlimBootstrap\Bootstrap(
    $applicationConfig
);

$slimApp = $slimBootstrap->init($logger);

$slimBootstrap->addEndpoint(
    SlimBootstrap\Bootstrap::HTTP_METHOD_GET,
    '/dummy/test/{id:[0-9]+}',
    'dummy',
    new \DummyApi\Endpoint\V1\EndpointA()
);

$slimApp->run();
~~~


## Create Endpoints

An endpoint is a PHP class that has to implement at least one of the
[`\SlimBootstrap\Endpoint\*`](https://github.com/Bigpoint/slim-bootstrap3/tree/master/src/SlimBootstrap/Endpoint)
interfaces depending on what HTTP method it should support (see below for more details).
All endpoints have to return a PHP array with the data they want to output in the end. The framework will then take
care of rendering this in the correct output format.

### Supported HTTP methods
At the moment the framework supports the following HTTP methods:

 - DELETE
 - GET
 - POST
 - PUT

For each of these methods the framework supplies an interface for the endpoints under
[`\SlimBootstrap\Endpoint\`](https://github.com/Bigpoint/slim-bootstrap3/tree/master/src/SlimBootstrap/Endpoint).

### Registering endpoints to the framework
The written endpoints have to be registered to the framework and the underlying Slim instance in order to be
accessible. This can be done by calling `addEndpoint()` on the `\SlimBootstrap\Bootstrap` instance after the `init()`
call and before the `run()` cal. The framework is using the basic form of slim to
[register a route](https://www.slimframework.com/docs/objects/router.html) and bind an endpoint to the route. However
at the moment slim-bootstrap3 doesn't allow grouping of endpoints. The `\SlimBootstrap\Bootstrap::addEndpoint()` method
has the following signature:

~~~php
/**
 * @param string                 $type           should be one of \SlimBootstrap\Bootstrap::HTTP_METHOD_*
 * @param string                 $route          pattern for the route to match
 * @param string                 $name           name of the route to add (used in ACL)
 * @param SlimBootstrap\Endpoint $endpoint       should be one of \SlimBootstrap\Endpoint\*
 * @param bool                   $authentication set this to false if you want no authentication for this endpoint
 *                                               (default: true)
 */
public function addEndpoint(
    string $type,
    string $route,
    string $name,
    SlimBootstrap\Endpoint $endpoint,
    bool $authentication = true
);
~~~

| parameter       | type                   | required | default | description |
| --------------- | ---------------------- | -------- | ------- | ----------- |
| $type           | string                 | yes      |         | should be one of the \SlimBootstrap\Bootstrap::HTTP_METHOD_* constants to define the method you want to match |
| $route          | string                 | yes      |         | pattern for the route to match. See [Route documentation](https://www.slimframework.com/docs/objects/router.html#route-placeholders) of slim for details |
| $name           | string                 | yes      |         | name to identify this (group of) endpoint(s). Used in the ACL handling |
| $endpoint       | SlimBootstrap\Endpoint | yes      |         | Instance of endpoint class. Has to match the Endpoint interface corresponding to the `$type` used |
| $authentication | bool                   | no       | true    | It is possible to disable authentication for only one specific endpoint. This flag is used for that |


## Response output

Slim-bootstrap3 has the possibility to support multiple output formats, which can be requested via header attribute
"Accept". These are the formats that are currently supported:

 - application/json __(default)__


## Authentication

When authentication is enabled, you have to add some more entries to your configuration file depending on the
authentication method you are using.

### Changes for authentication

#### config/acl.json
You have to add a config/acl.json, which defines accessible endpoints for a clientId.
~~~json
{
    "roles": {
        "role_dummy": {
            "dummy": true
        }
    },
    "access": {
        "myDummyClientId": "role_dummy"
    }
}
~~~
This is mapping the clientId "myDummyClientId" to the role "role_dummy" which has access to the "dummy" endpoint.

#### Changes to www/index.php
~~~diff
 <?php
 require(__DIR__ . '/../vendor/autoload.php');

 $applicationConfig = \json_decode(\file_get_contents(__DIR__ . '/../config/application.json'), true);
+$aclConfig         = \json_decode(\file_get_contents(__DIR__ . '/../config/acl.json'), true);

 $loggerFactory = new \MonologCreator\Factory($applicationConfig['monolog']);
 $logger        = $loggerFactory->createLogger('dummyApi');

 \Monolog\ErrorHandler::register($logger);

+$authenticationFactory = new \SlimBootstrap\Authentication\Factory(
+    new \Http\Caller($logger),
+    $logger
+);
 $slimBootstrap = new \SlimBootstrap\Bootstrap(
     $applicationConfig
 );

 $slimApp = $slimBootstrap->init($logger);

 $slimBootstrap->addEndpoint(
     SlimBootstrap\Bootstrap::HTTP_METHOD_GET,
     '/dummy/test/{id:[0-9]+}',
     'dummy',
     new \DummyApi\Endpoint\V1\EndpointA()
 );

 $slimApp->run();
~~~

### OAuth
You have to add the url parameter `access_token` to api calls with an access token given from your oauth server. The
authentication logic validates this access token against the configured oauth server via its /me endpoint. Next the
collected clientId from /me endpoint is going to be validated against requested endpoint and configured ACL. If all is
fine, access is granted to requester. Otherwise request is aborted with an 401 or 403 HTTP status code.

#### Changes in config/application.json
~~~diff
 {
   "displayErrorDetails": false,
+  "oauth": {
+    "authenticationUrl": "https://myserver.com/me?access_token="
+  },
   "cacheDuration": 900,
 }
~~~

#### Changes to www/index.php
~~~diff
 $slimBootstrap = new \SlimBootstrap\Bootstrap(
-    $applicationConfig
+    $applicationConfig,
+    $authenticationFactory->createOauth($applicationConfig),
+    $aclConfig
 );
~~~

### JWT
You have to add the JWT as an authorization bearer header to the request of the API. The framework will verify and
validate the JWT with the public key from the JWT provider. For the validation all fields from the config's `jwt.claims`
block have to match with the JWT. The public key of the JWT provider is currently expacted to be located on the "/"
endpoint in the json field `Pubkey`.

After that the framework will extract the clientId from the claim "name" and the role from the "role" claim. Next the
collected clientId and role are going to be validated against requested endpoint and configured ACL. If all is fine,
access is granted to requester. Otherwise request is aborted with an 401 or 403 HTTP status code.

#### Changes in config/application.json
~~~diff
 {
   "displayErrorDetails": false,
+  "jwt": {
+    "providerUrl": "https://my-sombra-instance.com/",
+    "claims": {
+      "issuer": "sombra_development"
+    }
+  },
   "cacheDuration": 900,
 }
~~~

#### Changes to www/index.php
~~~diff
 $slimBootstrap         = new \SlimBootstrap\Bootstrap(
-    $applicationConfig
+    $applicationConfig,
+    $authenticationFactory->createJwt($applicationConfig),
+    $aclConfig
 );
~~~

### Custom Authentication
If you want, you can define your own authentication class which for example reads from a database. If you want to do
this you have to implement the [Authentication interface](https://github.com/Bigpoint/slim-bootstrap3/blob/master/src/SlimBootstrap/Authenticate.php).


## License & Authors

- Authors:: Peter Ahrens (<pahrens@bigpoint.net>), Andreas Schleifer (<aschleifer@bigpoint.net>), Hendrik Meyer (<hmeyer@bigpoint.net>)

~~~
Copyright:: 2016 Bigpoint GmbH

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
~~~
