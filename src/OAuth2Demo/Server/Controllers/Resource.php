<?php

namespace OAuth2Demo\Server\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class Resource
{
    /**
     * Connects the routes in Silex
     * @param type $routing
     */
    static public function addRoutes($routing)
    {
        $routing->get('/apiResource', array(new self(), 'apiResource'))->bind('access_api');
        $routing->get('/resource', array(new self(), 'resource'))->bind('access');
        $routing->get('/profile', array(new self(), 'profile'))->bind('access_profile');
        $routing->get('/friends', array(new self(), 'friends'))->bind('access_friends');
    }

    public function apiResource(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        $scopeRequired = 'basic'; // this resource requires "postonwall" scope
        
        /*if (!$server->verifyResourceRequest($app['request'])) {
            return $server->getResponse();
        }*///request before adding scope requirements
        if (!$server->verifyResourceRequest($app['request'], $scopeRequired)) {
            // if the scope required is different from what the token allows, this will send a "401 insufficient_scope" error
            return $server->getResponse();
        } else {
            // return a fake API response - not that exciting
            // @TODO return something more valuable, like the name of the logged in user
            $api_response = array(
                'info' => array (
                    'organization' => 'My Demo Application',
                    'website'  => 'www.mydemoapp.com',
                ),
                'contact' => array(
                    'mailing_address'   => '123 Test Address Street Sunshine, FL 33913',
                    'email'             => 'info@mydemoapp.com'
                )
            );
            return new Response(json_encode($api_response));
        }
    }


    
    /**
     * This is called by the client app once the client has obtained an access
     * token for the current user.  If the token is valid, the resource (in this
     * case, the basic profile of the current user) will be returned to the client.
     * Requires scope of 'basic' to be granted to the access token
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resource(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        $scopeRequired = 'basic'; // this resource requires "postonwall" scope
        
        /*if (!$server->verifyResourceRequest($app['request'])) {
            return $server->getResponse();
        }*///request before adding scope requirements
        if (!$server->verifyResourceRequest($app['request'], $scopeRequired)) {
            // if the scope required is different from what the token allows, this will send a "401 insufficient_scope" error
            return $server->getResponse();
        } else {
            // return a fake API response - not that exciting
            // @TODO return something more valuable, like the name of the logged in user
            $api_response = array(
                'profile' => array (
                    'firstName' => 'Tanya',
                    'lastName'  => 'Brodsky',
                    'location'  => 'Orlando, FL',
                    'astro_sign' => 'Taurus',
                    'quote' => 'Something thoughtful here.',
                ),
                'friends' => array(
                    array(
                        'firstName' => 'johnny'
                    ),
                    array(
                        'firstName' => 'matthew'
                    ),
                    array(
                        'firstName' => 'jane'
                    )
                )
            );
            return new Response(json_encode($api_response));
        }
    }
    
    /**
     * This is called by the client app once the client has obtained an access
     * token for the current user.  If the token is valid, the resource (in this
     * case, the full profile of the current user including contact information)
     * will be returned to the client.
     * Requires scope of 'profile' to be granted to the access token
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile(Application $app)
    {    
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        $scopeRequired = 'profile';             // this resource requires "profile" scope
        
        if (!$server->verifyResourceRequest($app['request'], $scopeRequired)) {
            // if the scope required is different from what the token allows, this will send a "401 insufficient_scope" error
            return $server->getResponse();
        } else {   
            // return some fake API data in the response - not that exciting
            // @TODO return something more valuable, or pull from the database
            $api_response = array(
                'profile' => array (
                    'firstName' => 'Tanya',
                    'lastName'  => 'Brodsky',
                    'location'  => 'Orlando, FL',
                    'astro_sign' => 'Taurus',
                    'quote' => 'Something thoughtful here.',
                    'details'   => array(
                        'email'     => 'tanya@coolcodechick.com',
                        'dob'       => '05/07/1984',
                        'phone'     => '239-244-1234'
                    )
                ),
            );
            return new Response(json_encode($api_response));
        }
    }
    
    /**
     * This is called by the client app once the client has obtained an access
     * token for the current user.  If the token is valid, the resource (in this
     * case, the full list of friends for the current user including contact information)
     * will be returned to the client.
     * Requires scope of 'friends' to be granted to the access token
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function friends(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        $scopeRequired = 'friends'; // this resource requires "friends" scope
        if (!$server->verifyResourceRequest($app['request'], $scopeRequired)) {
            return $server->getResponse();
        } else {
            // return some fake API data in the response - not that exciting
            // @TODO return something more valuable, or pull from database
            $api_response = array(
                'friends' => array(
                    array(
                        'firstName' => 'johnny',
                        'lastName' => 'bravo',
                        'email' => 'jonny@gmail.com',
                        'dob' => '12/04/1982'
                    ),
                    array(
                        'firstName' => 'matthew',
                        'lastName' => 'wehttam',
                        'email' => 'matt@gmail.com',
                        'dob' => '09/21/1984'
                    ),
                    array(
                        'firstName' => 'jane',
                        'lastName' => 'doe',
                        'email' => 'jane@gmail.com',
                        'dob' => '02/14/1983'
                    )
                )
            );
            return new Response(json_encode($api_response));
        }
    }
}