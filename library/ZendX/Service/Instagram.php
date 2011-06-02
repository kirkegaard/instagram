<?php

/**
 * ZendX_Service_Instagram
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
 * @category  ZendX
 * @package   ZendX_Service
 * @author    Christian Kirkegaard <hello@christiank.org>
 * @copyright 2011 Christian Kirkegaard <hello@christiank.org>
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/ranza/instagram
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
    private $_token = null;

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
        if(null !== $this->_token) {
            throw new Exception('Access token is already set.');
        }
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
    public function getAccessToken($code = null)
    {
        if(null !== $this->_token) {
            return $this->_token;
        }

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

    public function setAccessToken($token)
    {
        $this->_token = $token;
        return $this;
    }


    public function user($id = 'self')
    {
        return $this->_sendRequest(
            '/users/' . $id
        );
    }

    public function userSearch($q)
    {
        return $this->_sendRequest(
            '/users/search/',
            array('q' => $q)
        );
    }

    public function userFollows($id = 'self')
    {
        return $this->_sendRequest(
            '/users/' . $id . '/follows'
        );
    }

    public function userFollowedBy($id = 'self')
    {
        return $this->_sendRequest(
            '/users/' . $id . '/followed-by'
        );
    }

    public function userRequestedBy()
    {
        return $this->_sendRequest(
            '/users/self/requested-by'
        );
    }

    public function userMediaFeed()
    {
        return $this->_sendRequest(
            '/users/self/feed'
        );
    }

    public function userRecentMedia($id = 'self')
    {
        return $this->_sendRequest(
            '/users/' . $id . '/media/recent'
        );
    }

    public function userRelationship($id)
    {
        return $this->_sendRequest(
            '/users/' . $id . '/relationship'
        );
    }

    public function followUser($id)
    {
        $options = array('action' => 'follow');
        return $this->_sendRequest(
            '/users/' . $id . '/relationship',
            $options,
            'POST'
        );
    }

    public function unfollowUser($id)
    {
        $options = array('action' => 'unfollow');
        return $this->_sendRequest(
            '/users/' . $id . '/relationship',
            $options,
            'POST'
        );
    }

    public function blockUser($id)
    {
        $options = array('action' => 'block');
        return $this->_sendRequest(
            '/users/' . $id . '/relationship',
            $options,
            'POST'
        );
    }

    public function unblockUser($id)
    {
        $options = array('action' => 'unblock');
        return $this->_sendRequest(
            '/users/' . $id . '/relationship',
            $options,
            'POST'
        );
    }

    public function approveUser($id)
    {
        $options = array('action' => 'approve');
        return $this->_sendRequest(
            '/users/' . $id . '/relationship',
            $options,
            'POST'
        );
    }

    public function denyUser($id)
    {
        $options = array('action' => 'deny');
        return $this->_sendRequest(
            '/users/' . $id . '/relationship',
            $options,
            'POST'
        );
    }

    public function mediaItem($id = 'self')
    {
        return $this->_sendRequest(
            '/media/' . $id
        );
    }

    public function mediaPopular()
    {
        return $this->_sendRequest(
            '/media/popular'
        );
    }

    public function mediaSearch($lat, $lng, $options = array())
    {
        $options = array_merge($options, array(
            'lat' => $lat,
            'lng' => $lng,
        ));
        return $this->_sendRequest(
            '/media/search',
            $options
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

        if(!isset($args['grant_type'])) {
            $client->setParameterGet(array(
                'access_token' => $this->getAccessToken()
            ));
        }

        if($method == 'GET') {
            $client->setParameterGet($args);
        }

        if($method == 'POST') {
            $client->setParameterPost($args);
        }

        return $client->request($method)->getBody();
    }


}
