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
     * The scope of permissions you want
     * Supports: likes, comments, relationships
     *
     * @var string $_scope The scope of permissions
     */
    private $_scope = array('basic');

    /**
     * The format you wish to get returned.
     * Right now only json and array is supported
     *
     * @var string $_format Returned format
     */
    private $_format = 'json';

    /**
     * Constructor
     *
     * @param string $client   The OAuth client id
     * @param string $secret   The OAuth client secret
     * @param string $redirect The redirect url
     * @param string $format   The format you want returned
     * @param array  $scope    The permission scope
     * @param string $api      The API url (optional)
     * @param string $version  The API version (optional)
     */
    public function __construct($client, $secret, $redirect, $format = 'json', $scope = array('basic'), $api = 'https://api.instagram.com', $version = 'v1')
    {
        $this->_oauth    = null;
        $this->_client   = $client;
        $this->_secret   = $secret;
        $this->_redirect = $redirect;
        $this->_scope    = $scope;
        $this->_format   = $format;
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
            'response_type' => 'code',
            'scope'         => implode(' ', $this->_scope),
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

        $format = $this->_format;
        $this->_format = 'array';

        $response = $this->_sendRequest(
            '/oauth/access_token',
            $params,
            'POST',
            false
        );

        $this->_format = $format;
        $this->_oauth  = $response['user'];
        $this->_token  = $response['access_token'];

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

    public function userLikedMedia($options = array())
    {
        return $this->_sendRequest(
            '/users/self/media/liked',
            $options
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

    public function mediaComments($id)
    {
        return $this->_sendRequest(
            '/media/' . $id . '/comments'
        );
    }

    public function createMediaComment($id, $text)
    {
        return $this->_sendRequest(
            '/media/' . $id . '/comments',
            array('text' => $text),
            'POST'
        );
    }

    public function deleteMediaComment($media_id, $comment_id)
    {
        return $this->_sendRequest(
            '/media/' . $media_id . '/comments/' . $comment_id,
            array(),
            'DELETE'
        );
    }

    public function mediaLikes($id)
    {
        return $this->_sendRequest(
            '/media/' . $id . '/likes'
        );
    }

    public function likeMedia($id)
    {
        return $this->_sendRequest(
            '/media/' . $id . '/likes',
            array(),
            'POST'
        );
    }

    public function unlikeMedia($id)
    {
        return $this->_sendRequest(
            '/media/' . $id . '/likes',
            array(),
            'DELETE'
        );
    }

    public function tag($tag)
    {
        return $this->_sendRequest(
            '/tags/' . $tag
        );
    }

    public function tagRecentMedia($tag, $options = array())
    {
        return $this->_sendRequest(
            '/tags/' . $tag . '/media/recent',
            $options
        );
    }

    public function tagSearch($q, $options = array())
    {
        $options = array_merge($options, array('q' => $q));
        return $this->_sendRequest(
            '/tags/search',
            $options
        );
    }

    public function location($id)
    {
        return $this->_sendRequest(
            '/locations/' . $id
        );
    }

    public function locationRecentMedia($id, $options = array())
    {
        return $this->_sendRequest(
            '/locations/' . $id . '/media/recent',
            $options
        );
    }

    public function locationSearch($lat, $lng, $options = array())
    {
        $options = array_merge($options, array(
            'lat' => $lat,
            'lng' => $lng,
        ));
        return $this->_sendRequest(
            '/locations/search',
            $options
        );
    }

    public function geographyRecentMedia($id, $options = array())
    {
        return $this->_sendRequest(
            '/geographies/' . $id . '/media/recent',
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

        $body = $client->request($method)->getBody();

        switch(strtolower($this->_format)) {
            case 'array':
                return Zend_Json::decode($body);
                break;
            default:
            case 'json':
                return $body;
                break;
        }
    }


}
