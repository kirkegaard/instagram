<?php

require_once 'library/ZendX/Service/Instagram.php';
session_start();


$instagram = new ZendX_Service_Instagram(
    '5d04b189d8a34be38f755a0f27ff09a3',
    '471ecfd3aaae4d19b6afb11d9d38f133',
    'http://instagram.local/',
    array('likes', 'comments', 'relationships')
);


$do = (isset($_GET['do'])) ? $_GET['do'] : 'none';
$output = array('message' => 'Nothing to do');


if(isset($_SESSION['InstagramAccessToken'])) {
    $instagram->setAccessToken($_SESSION['InstagramAccessToken']);
}
if(!isset($_SESSION['InstagramAccessToken']) && isset($_GET['code'])) {
    $_SESSION['InstagramAccessToken'] = $instagram->getAccessToken($_GET['code']);
    $output = array('message' => 'Authenticated');
}


if($do == 'login') {
    header('Location: ' . $instagram->getAuthorizeUri());
    die();
}
if($do == 'logout') {
    $_SESSION['InstagramAccessToken'] = null;
    $output = array('message' => 'Logged out');
}

switch ($do) {
    // user
    case 'user':
        $output = $instagram->user();
        break;
    case 'user_search':
        $output = $instagram->userSearch('christiank');
        break;
    case 'user_follows':
        $output = $instagram->userFollows();
        break;
    case 'user_followed_by':
        $output = $instagram->userFollowedBy();
        break;
    case 'user_requested_by':
        $output = $instagram->userRequestedBy();
        break;
    case 'user_media_feed':
        $output = $instagram->userMediaFeed();
        break;
    case 'user_recent_media':
        $output = $instagram->userRecentMedia();
        break;
    case 'user_liked_media':
        $output = $instagram->userLikedMedia();
        break;
    case 'user_relationship':
        $output = $instagram->userRelationship(2743472);
        break;

    // media
    case 'media_item':
        $output = $instagram->mediaItem(85090024);
        break;
    case 'media_popular':
        $output = $instagram->mediaPopular();
        break;
    case 'media_search':
        $output = $instagram->mediaSearch(55.676356, 12.569153);
        break;

    // comments
    case 'media_comments':
        $output = $instagram->mediaComments(85090024);
        break;
    case 'create_media_comment':
        $output = $instagram->createMediaComment(85090024, 'Hello from php instagram wrapper example page');
        break;
    // we cant really test this since the id changes all the time
    // case 'delete_media_comment':
    //     break;

    // likes
    case 'media_likes':
        $output = $instagram->mediaLikes(85090024);
        break;
    case 'like_media':
        $output = $instagram->likeMedia(85090024);
        break;
    case 'unlike_media':
        $output = $instagram->unlikeMedia(85090024);
        break;

    // tags
    case 'tag':
        $output = $instagram->tag('cats');
        break;
    case 'tag_recent_media':
        $output = $instagram->tagRecentMedia('cats');
        break;
    case 'tag_search':
        $output = $instagram->tagSearch('cats');
        break;

    // location
    case 'location':
        $output = $instagram->location(120774);
        break;
    case 'location_recent_media':
        $output = $instagram->locationRecentMedia(120774);
        break;
    case 'location_search':
        $output = $instagram->locationSearch(55.676356, 12.569153);
        break;

    // geography
    // This only works for oauth clients own geographics
    // case 'geography_recent_media':
    //     $output = $instagram->geographyRecentMedia(1);
    //     break;

    default:
        $output = array('Nothing to do');
        break;
}

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
                    <li>
                        <strong>User</strong>
                        <ul>
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
                                <a href="?do=user_liked_media">User Liked Media</a>
                            </li>
                            <li>
                                <a href="?do=user_relationship">User relationship</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong>Media</strong>
                        <ul>
                            <li>
                                <a href="?do=media_item">Media item</a>
                            </li>
                            <li>
                                <a href="?do=media_popular">Media popular</a>
                            </li>
                            <li>
                                <a href="?do=media_search">Media search</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong>Comments</strong>
                        <ul>
                            <li>
                                <a href="?do=media_comments">Media comments</a>
                            </li>
                            <li>
                                <a href="?do=create_media_comment">Create media comment</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong>Likes</strong>
                        <ul>
                            <li>
                                <a href="?do=media_likes">Media likes</a>
                            </li>
                            <li>
                                <a href="?do=like_media">Like media</a>
                            </li>
                            <li>
                                <a href="?do=unlike_media">Unlike media</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong>Tags</strong>
                        <ul>
                            <li>
                                <a href="?do=tag">Tag</a>
                            </li>
                            <li>
                                <a href="?do=tag_recent_media">Tag recent media</a>
                            </li>
                            <li>
                                <a href="?do=tag_search">Tag search</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong>Location</strong>
                        <ul>
                            <li>
                                <a href="?do=location">Location</a>
                            </li>
                            <li>
                                <a href="?do=location_recent_media">Location recent media</a>
                            </li>
                            <li>
                                <a href="?do=location_search">Location search</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div id="right" class="grid_9">
            <?php var_dump($output); ?>
            <?php if(!is_array($output)) : ?>
                <?php var_dump(Zend_Json::decode($output)); ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>


