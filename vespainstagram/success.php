<?php

/**
 * Instagram PHP API
 * Example for using the getUserMedia() method
 * 
 * @link https://github.com/cosenary/Instagram-PHP-API
 * @author Christian Metz
 * @since 31.01.2012
 */

require 'src/Instagram.php';
use MetzWeb\Instagram\Instagram;

// Initialize class
$instagram = new Instagram(array(
  'apiKey'      => 'ab9ad048ea114512a095a0c3237fc5ca',
  'apiSecret'   => 'aa4c65fbdcd24065b17fee377da89d3a',
  'apiCallback' => 'https://www.misiva.com.ec/vespainstagram/'
));

// Receive OAuth code parameter
$code = $_GET['code'];

// Check whether the user has granted access
if (true === isset($code)) {

  // Receive OAuth token object
  $data = $instagram->getOAuthToken($code);

  // Store user access token
  $instagram->setAccessToken($data);

  // Now you can call all authenticated user methods
  // Get the most recent media published by a user
  $media = $instagram->getUserMedia();

  foreach ($media->data as $entry) {
    echo "<img src=\"{$entry->images->thumbnail->url}\">";
  }

}

?>