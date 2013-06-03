<?php

namespace OAuth2Demo\Client\Controllers;

use OAuth2Demo\Shared\Curl;
use Silex\Application;


class ReceiveAuthorizationCode
{
    static public function addRoutes($routing)
    {
        $routing->get('/client/receive_authcode', array(new self(), 'receiveAuthorizationCode'))->bind('authorize_redirect');
    }

    public function receiveAuthorizationCode(Application $app)
    {
        $request = $app['request']; // the request object
        $session = $app['session']; // the session (or user) object
        $twig    = $app['twig'];    // used to render twig templates

        // the user denied the authorization request
        if (!$code = $request->get('code')) {
            return $twig->render('client/failed_authorization.twig', array('response' => $request->getAllQueryParameters()));
        }

        // verify the "state" parameter matches this user's session (this is like CSRF - very important!!)
        if ($request->get('state') !== $session->getId()) {
            return $twig->render('client/failed_authorization.twig', array('response' => array('error_description' => 'Your session has expired.  Please try again.')));
        }

        // Redirect directly to the request_token without user interaction
        //return $twig->render('client/show_authorization_code.twig', array('code' => $code));
        
        //return $this->forward('OAuth2Demo:RequestToken:requestToken', array('name' => $name));  //forward should be better so the browser does not have to redirect but seems to be part of the Symfony\Bundle\FrameworkBundle\Controller\Controller and I can't get this class to extend it
        return $app->redirect($app['url_generator']->generate('request_token', array("code" => $code)));
    }
}