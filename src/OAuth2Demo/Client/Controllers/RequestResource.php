<?php

namespace OAuth2Demo\Client\Controllers;

use OAuth2Demo\Shared\Curl;
use Silex\Application;

class RequestResource
{
    /**
     * Connects the routes in Silex 
     * @param type $routing
     */
    static public function addRoutes($routing)
    {
        $routing->get('/client/request_resource', array(new self(), 'requestResource'))->bind('request_resource');
        $routing->get('/client/request_profile', array(new self(), 'requestProfile'))->bind('request_profile');
        $routing->get('/client/request_friends', array(new self(), 'requestFriends'))->bind('request_friends');
    }

    /**
     * Checks if the session has a refresh_token and uses it to request a new access token
     * @param \Silex\Application $app
     * @return string Returns the new access token if issued or "error"
     */
    protected function renewAccessToken(Application $app)
    {
        $session = $app['session'];         // the session (or user) object
        
        // Use the refresh_token to retrive a new Access Token
        if( $session->get('refresh_token')){
            $refreshed_token = RefreshToken::requestRefreshToken($app);
            if(isset($refreshed_token['access_token'])) {
                return $refreshed_token['access_token'];
            } else {
                return "error";
            }                 
        }
    }

    /**
     * Requests the resource from the resource server
     * @param \Silex\Application $app
     * @return type Renders to a Twig template
     */
    public function requestResource(Application $app)
    {
        $session = $app['session'];         // the session (or user) object
        $twig   = $app['twig'];             // used to render twig templates
        $config = $app['parameters'];       // the configuration for the current oauth implementation
        $urlgen = $app['url_generator'];    // generates URLs based on our routing
        $curl  = new Curl();                // simple class used to make curl requests

        // Pull the token from the request
        $token = $app['request']->get('token');

        // Make the resource request with the token in the request body
        $config['resource_params']['access_token'] = $token;

        // Set endpoint for the request
        $endpoint = 0 === strpos($config['resource_route'], 'http') ? $config['resource_route'] : $urlgen->generate($config['resource_route'], array(), true);        

        // Make the resource request via curl and decode the json response
        $response = $curl->request($endpoint, $config['resource_params'], $config['resource_method'], $config['curl_options']);
        $json = json_decode($response['response'], true);

        // Check the state
        if ($app['request']->get('state') !== $session->getId()) {
           return $twig->render('client/failed_authorization.twig', array('response' => array('error_description' => 'Your session has expired.  Please try again.')));
        }
        
        // Check if the access token is expired
        if (isset( $json['error_description']) && $json['error_description'] === 'The access token provided has expired' ){
            // Try to renew the access token with the refresh token
            $refreshed_token = $this->renewAccessToken($app);
            if($refreshed_token != "error") {
                // Redirect to send the request for the resource again
                return $app->redirect($app['url_generator']->generate('request_resource', array( 'token' => $refreshed_token, 'state' => $session->getId() ) ) );
            } else {
                // Render the page with the original error that the access token expired
                return $twig->render('client/show_resource.twig', array('response' => $json ? $json : $response, 'token' => $token, 'endpoint' => $endpoint, 'session_id' => $session->getId()));
            }                 
        } else {
            return $twig->render('client/show_resource.twig', array('response' => $json ? $json : $response, 'token' => $token, 'endpoint' => $endpoint, 'session_id' => $session->getId()));
        }
    }
    
    /**
     * Requests the full profile details from the resource server - requires API scope of profile
     * @param \Silex\Application $app
     * @return type Renders to a Twig template
     */
    public function requestProfile(Application $app)
    {
        $session = $app['session'];         // the session (or user) object
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $curl  = new Curl();             // simple class used to make curl requests

        // Check the state
        if ($app['request']->get('state') !== $session->getId()) {
           return $twig->render('client/failed_authorization.twig', array('response' => array('error_description' => 'Your session has expired.  Please try again.')));
        }
        
        // Pull the token from the request
        $token = $app['request']->get('token');

        // Make the resource request with the token in the request body
        $config['resource_params']['access_token'] = $token;

        // Set endpoint for request
        $endpoint = 0 === strpos($config['resource_profile_route'], 'http') ? $config['resource_profile_route'] : $urlgen->generate($config['resource_profile_route'], array(), true);

        // Make the resource request via curl and decode the json response
        $response = $curl->request($endpoint, $config['resource_params'], $config['resource_method'], $config['curl_options']);
        $json = json_decode($response['response'], true);
        
        // Return the response
        return $twig->render('client/show_resource.twig', array('response' => $json ? $json : $response, 'token' => $token, 'endpoint' => $endpoint, 'session_id' => $session->getId()));
    }
    
    /**
     * Requests the details of the friend list from the resource server - requires the API scope of friends
     * @param \Silex\Application $app
     * @return type Renders to a Twig template
     */
    public function requestFriends(Application $app)
    {
        $session = $app['session'];         // the session (or user) object
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $curl  = new Curl();             // simple class used to make curl requests

        // Pull the token from the request
        $token = $app['request']->get('token');
        
        // Make the resource request with the token in the request body
        $config['resource_params']['access_token'] = $token;

        // Set endpoint for request
        $endpoint = 0 === strpos($config['resource_friends_route'], 'http') ? $config['resource_friends_route'] : $urlgen->generate($config['resource_friends_route'], array(), true);

        // Make the resource request via curl and decode the json response
        $response = $curl->request($endpoint, $config['resource_params'], $config['resource_method'], $config['curl_options']);
        $json = json_decode($response['response'], true);
        
        // Check the state
        if ($app['request']->get('state') !== $session->getId()) {
           return $twig->render('client/failed_authorization.twig', array('response' => array('error_description' => 'Your session has expired.  Please try again.')));
        }
        
        // Return the response
        return $twig->render('client/show_resource.twig', array('response' => $json ? $json : $response, 'token' => $token, 'endpoint' => $endpoint, 'session_id' => $session->getId()));
    }
}