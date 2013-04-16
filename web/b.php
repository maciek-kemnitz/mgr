<?php


$b = $app['controllers_factory'];
$key = '12f6692e591f34d122802a92e23cd807483c21e4';
$secret = '113b17c5f51faba8a78022937b175e6b36d5c516';

$oauth = new OAuth($key, $secret, OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
$oauth->disableSSLChecks();
$oauth->enableDebug();




$b->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
   return "awesome";
});

return $b;



