<?php

require_once 'library/ZendX/Service/Instagram.php';
session_start();


$instagram = new ZendX_Service_Instagram(
    '5d04b189d8a34be38f755a0f27ff09a3',
    '471ecfd3aaae4d19b6afb11d9d38f133',
    'http://instagram.local/'
);


$do = (isset($_GET['do'])) ? $_GET['do'] : 'none';
$output = 'Nothing to do';


if(!isset($_SESSION['InstagramAccessToken']) && isset($_GET['code'])) {
    $_SESSION['InstagramAccessToken'] = $instagram->getAccessToken($_GET['code']);
    $output = 'Authenticated';
}


if($do == 'login') {
    header('Location: ' . $instagram->getAuthorizeUri());
    die();
}
if($do == 'logout') {
    $_SESSION['InstagramAccessToken'] = null;
    $output = 'Logged out';
}
if($do == 'user') {}
if($do == 'user_search') {}
if($do == 'user_follows') {}
if($do == 'user_followed_by') {}
if($do == 'user_requested_by') {}
if($do == 'user_media_feed') {}
if($do == 'user_recent_media') {}
if($do == 'user_relationship') {}




?><!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Instagram test</title>

    <meta name="description" content="Instagram API Test">
    <meta name="author" content="Christian Kirkegaard">
 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://github.com/nathansmith/960-Grid-System/raw/master/code/css/reset.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="https://github.com/nathansmith/960-Grid-System/raw/master/code/css/text.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="http://grids.heroku.com/fluid_grid.css?column_width=0&column_amount=12&gutter_width=0" type="text/css" media="screen" charset="utf-8">

    <style type="text/css" media="screen">
        #left {
            border-right: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="container_12">
        <div id="left" class="grid_3">
            <ul class="menu">
                <?php if(!isset($_SESSION['InstagramAccessToken'])) : ?>
                    <li>
                        <a href="?do=login">Authenticate</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="?do=logout">Logout</a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="?do=user">User</a>
                </li>
                <li>
                    <a href="?do=user_search">User search</a>
                </li>
                <li>
                    <a href="?do=user_follows">User follows</a>
                </li>
                <li>
                    <a href="?do=user_followed_by">User followed by</a>
                </li>
                <li>
                    <a href="?do=user_requested_by">User requested by</a>
                </li>
                <li>
                    <a href="?do=user_media_feed">User media feed</a>
                </li>
                <li>
                    <a href="?do=user_recent_media">User recent media</a>
                </li>
                <li>
                    <a href="?do=user_relationship">User relationship</a>
                </li>
            </ul>
        </div>

        <div id="right" class="grid_9">
            <?php var_dump($output); ?>
        </div>
    </div>

</body>
</html>


