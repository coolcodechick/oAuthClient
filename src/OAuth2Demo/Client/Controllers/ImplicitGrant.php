<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;

/**
 * Uses an Implicit Grant Type to request an Access Token directly
 *
 * REQUEST : HttpRequest Method = GET
 *           REQUIRED : response_type = "token"
 *                      client_id 
 *           OPTIONAL : redirect_uri 
 *                      scope 
 *                      state 
 * RETURNS : Returns response and redirects all items as part of url fragment ( ie. ?#access_token=____&expires_in=____&token_type=____&scope=____ )
 *           REQUIRED : access_token
 *                      token_type = "Bearer"
 *                      expires_in ( default 3600 sec ) 
 *           OPTIONAL : scope ( MUST BE INCLUDED IF part of the REQUEST )
 *                      state ( MUST BE INCLUDED IF part of the REQUEST )
 *
 *           refresh_token SHOULD NOT be issued
 * 
 * @author tanya brodsky
 */
class ImplicitGrant {
    
    /**
     * Connects the routes in Silex
     * @param type $routing
     */
    static public function addRoutes($routing)
    {
        $routing->get('/implicit_grant', array(new self(), 'implicitGrant'))->bind('implicit_grant');  //per documentation should be a get request not a post but this breaks the classes
    }
    
    /**
     * Uses the response_type of token to grant permissions to the client. Must validate against 
     * @param \Silex\Application $app
     * @return redirect back to client 
     */
    public function implicitGrant(Application $app)
    {
        $session = $app['session'];         // the session (or user) object
        $twig   = $app['twig'];             // used to render twig templates
        $config = $app['parameters'];       // the configuration for the current oauth implementation
        $urlgen = $app['url_generator'];    // generates URLs based on our routing
        
        // Check the state
        if ($app['request']->get('state') !== $session->getId()) {
            return $twig->render('client/failed_token_request.twig', array('response' => array( "error_description" => "Session Expired")));
        }
       
        // Set endpoint for request 
        $endpoint = 0 === strpos($config['implicit_grant'], 'http') ? $config['implicit_grant'] : $urlgen->generate($config['implicit_grant'], array(), true);
            
        // Set the grant_type and REQUIRED params
        $query  = 'response_type=token';
        $query .= '&client_id='.$config['client_id'];
            
        // Check for OPTIONAL params and add to query if set
        $app['request']->get('redircet_uri')    ? $query .= '&redirect_uri='.$app['request']->get('redircet_uri')   : '';
        $app['request']->get('scope')           ? $query .= '&scope='.$app['request']->get('scope')                 : '';  
        $app['request']->get('state')           ? $query .= '&state='.$app['request']->get('state')                 : '';  
        
        // Send the request
        return $app->redirect($endpoint.'?'.$query);
    }
}