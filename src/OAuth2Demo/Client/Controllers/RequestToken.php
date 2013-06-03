<?php

namespace OAuth2Demo\Client\Controllers;

use OAuth2Demo\Shared\Curl;
use Silex\Application;

class RequestToken
{
    static public function addRoutes($routing)
    {
        $routing->get('/client/request_token', array(new self(), 'requestToken'))->bind('request_token');
    }

    public function requestToken(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $curl   = new Curl();            // simple class used to make curl requests

        $code = $app['request']->get('code');

        // exchange authorization code for access token
        $query = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $urlgen->generate('authorize_redirect', array(), true),
        );

//var_dump($query);
//
//  GOOGLE TASKS API
//array(5) { 
//  ["grant_type"]=> string(18) "authorization_code" 
//  ["code"]=> string(62) "4/SHOcNpoD8lePQfW5RUbt1EAuLSSN.AueP0zpSMlgVshQV0ieZDArsA-UUfgI" 
//  ["client_id"]=> string(39) "341894499868.apps.googleusercontent.com" 
//  ["client_secret"]=> string(24) "oKn5rpMEaMsI37IRjLr75iCi" 
//  ["redirect_uri"]=> string(68) "http://localhost:8888/oauth2-server-demo/web/client/receive_authcode" }
//  
//  LOCKDIN
//array(5) { 
//  ["grant_type"]=> string(18) "authorization_code" 
//  ["code"]=> string(40) "23da5016262a6533c8ce90dbbdeb5959df19a4ca" 
//  ["client_id"]=> string(7) "demoapp" 
//  ["client_secret"]=> string(8) "demopass" 
//  ["redirect_uri"]=> string(68) "http://localhost:8888/oauth2-server-demo/web/client/receive_authcode" }
//  
//  MYAPP
//array(5) { 
//  ["grant_type"]=> string(18) "authorization_code" 
//  ["code"]=> string(40) "29520d29828b1f637ea55fbda974e85919e0e69d" 
//  ["client_id"]=> string(22) "OAuth Demo Application" 
//  ["client_secret"]=> string(20) "a3b4b74330724a927bec" 
//  ["redirect_uri"]=> string(68) "http://localhost:8888/oauth2-server-demo/web/client/receive_authcode" }  
//  
        // determine the token endpoint to call based on our config (do this somewhere else?)
        $grantRoute = $config['token_route'];
        
//var_dump($grantRoute);
//  string(42) "https://accounts.google.com/o/oauth2/token"

        $endpoint = 0 === strpos($grantRoute, 'http') ? $grantRoute : $urlgen->generate($grantRoute, array(), true);
//var_dump($endpoint);
//   string(42) "https://accounts.google.com/o/oauth2/token"
//   
        // make the token request via curl and decode the json response
        $response = $curl->request($endpoint, $query, 'POST', $config['curl_options']);
        $json = json_decode($response['response'], true);

        // if it is succesful, display the token in our app
        if (isset($json['access_token'])) {
            //return $twig->render('client/show_access_token.twig', array('token' => $json['access_token']));
            // Instead of displaying token, use it to get the requested resource and add the  lib to this page
            return $app->redirect($app['url_generator']->generate('request_resource', array("token" => $json["access_token"])));
        }

        return $twig->render('client/failed_token_request.twig', array('response' => $json ? $json : $response));
    }  
}
