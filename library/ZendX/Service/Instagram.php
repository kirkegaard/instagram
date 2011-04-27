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
require_once 'Zend/Json.php';

class ZendX_Service_Instagram {

    /**
     * API URL
     *
     * @var string $_api The API URL to use
     */
    private $_api;

    /**
     * The oauth response from the api
     *
     * @var array $_oauth The response from the api
     */
    private $_oauth = null;

    /**
     * API Client id
     *
     * @var string $_client The client id
     * @see http://instagr.am/developer/manage/
     */
    private $_client;

    /**
     * API Client secret
     *
     * @var string $_secret The client secret
     * @see http://instagr.am/developer/manage/
     */
    private $_secret;

    /**
     * The url we want the user redirected to after a success authentication
     *
     * @var string $_redirect The redirect url
     */
    private $_redirect;

    /**
     * The access token we need to make requests
     *
     * @var string $_token The access token
     */
    private $_token;

    /**
     * The API version
     *
     * @var string $_version The API version
     */
    private $_version;

    /**
     * Constructor
     *
     * @param string $client   The OAuth client id
     * @param string $secret   The OAuth client secret
     * @param string $redirect The redirect url
     * @param string $api      The API url (optional)
     * @param string $version  The API version (optional)
     */
    public function __construct($client, $secret, $redirect, $api = 'https://api.instagram.com', $version = 'v1')
    {
        $this->_oauth    = null;
        $this->_client   = $client;
        $this->_secret   = $secret;
        $this->_redirect = $redirect;
        $this->_api      = $api;
        $this->_version  = $version;
    }

    /**
     * Get the authorize uri for the user
     *
     * @return string
     */
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

    /**
     * Get an access token from the API
     *
     * @param  string $code The code returned from auth
     * @return string
     */
    public function getAccessToken($code)
    {
        $params = array(
            'client_id'     => $this->_client,
            'client_secret' => $this->_secret,
            'redirect_uri'  => $this->_redirect,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        );

        $response = Zend_Json::decode($this->_sendRequest(
            '/oauth/access_token',
            $params,
            'POST',
            false
        ));

        $this->_oauth = $response['user'];
        $this->_token = $response['access_token'];

        return $this->_token;
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

    /**
     * Send a request to the API
     *
     * @todo Add error handling
     *
     * @param string  $endpoint    API endpoint
     * @param array   $args        Request arguments
     * @param string  $method      Request method
     * @param boolean $use_version Do a api request with version (optional)
     *
     * @return object Instance Of {@link Zend_Http_Response}
     */
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

        return $client->request($method)->getBody();
    }


}
