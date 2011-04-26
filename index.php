<?php

require_once 'library/ZendX/Service/Instagram.php';
session_start();


$instagram = new ZendX_Service_Instagram(
    '5d04b189d8a34be38f755a0f27ff09a3',
    '471ecfd3aaae4d19b6afb11d9d38f133',
    'http://instagram.local/'
);


if(isset($_GET['reset'])) {
    $_SESSION['InstagramAccessToken'] = null;
}

if(isset($_GET['code'])) {
    $_SESSION['InstagramAccessToken'] = $instagram->getAccessToken($_GET['code']);
}

if(!isset($_SESSION['InstagramAccessToken']) || empty($_SESSION['InstagramAccessToken'])) {
    header('Location: ' . $instagram->getAuthorizeUri());
    die();
}


print 'We are all set!';
var_dump($_SESSION);
var_dump(json_decode($_SESSION['InstagramAccessToken']->getBody()));
