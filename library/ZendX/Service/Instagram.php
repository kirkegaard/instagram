<?php

/**
 * Services_Instagram
 *
 * Implementation of the Instagram API
 *
 * PHP version 5.2.0+
 *
 * LICENSE: This source file is subject to the New BSD license that is
 * available through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/bsd-license.php. If you did not receive
 * a copy of the New BSD License and are unable to obtain it through the web,
 * please send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Services
 * @package   Services_Instagram
 * @author    Christian Kirkegaard <hello@christiank.org>
 * @copyright 2011 Christian Kirkegaard <hello@christiank.org>
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/ranza/Services_Instagram
 */

require_once 'Zend/Http/Client.php';

class ZendX_Service_Instagram {


    private $_api;

    private $_oauth = null;

    private $_client;

    private $_secret;

    private $_redirect;

    private $_version;


    public function __construct($client, $secret, $redirect, $api = 'https://api.instagram.com', $version = 'v1')
    {
        $this->_oauth    = null;
        $this->_client   = $client;
        $this->_secret   = $secret;
        $this->_redirect = $redirect;
        $this->_api      = $api;
        $this->_version  = $version;
    }

    public function getAuthorizeUri()
    {
        $params = array(
            'client_id'     => $this->_client,
            'redirect_uri'  => $this->_redirect,
            'response_type' => 'code'
        );

        $args = http_build_query($params);

        return $this->_api . '/oauth/authorize/?' . $args;
    }

    public function getAccessToken($code)
    {
        $params = array(
            'client_id'     => $this->_client,
            'client_secret' => $this->_secret,
            'redirect_uri'  => $this->_redirect,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        );

        return $this->_sendRequest(
            '/oauth/access_token',
            $params,
            'POST',
            false
        );
    }



    public function getUser($id)
    {
        return $this->_sendRequest(
            '/users/'
        );
    }

    public function getUserFeed()
    {
        return $this->_sendRequest(
            '/users/self/feed'
        );
    }

    public function getUserMedia($id)
    {
        return $this->_sendRequest(
            '/users/' . $id . '/media/recent'
        );
    }

    public function getUserSearch($query)
    {
        return $this->_sendRequest(
            '/users/search/',
            array('q' => $query)
        );
    }

    private function _sendRequest($endpoint, $args = array(), $method = 'GET', $use_version = true) 
    {
        $version = ($use_version) ? '/' . $this->_version : null;

        $client = new Zend_Http_Client();
        $client->setUri($this->_api . $version . $endpoint);

        if($method == 'GET') {
            $client->setParameterGet($args);
        }

        if($method == 'POST') {
            $client->setParameterPost($args);
        }

        return $client->request($method);
    }


}
