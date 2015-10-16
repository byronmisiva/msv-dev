<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

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
    'apiKey' => 'ab9ad048ea114512a095a0c3237fc5ca',
    'apiSecret' => 'aa4c65fbdcd24065b17fee377da89d3a',
    'apiCallback' => 'https://www.misiva.com.ec/vespainstagram/'
));

// Receive OAuth code parameter
$code = $_GET['code'];

if (!isset($_GET['code']))
    $code = "40a5095bb5484217b0f324798c7ad865";


// Check whether the user has granted access
if (true === isset($code)) {

    // Receive OAuth token object
    $data = $instagram->getOAuthToken($code);

    // Store user access token
    $instagram->setAccessToken($data);

    // Now you can call all authenticated user methods
    // Get the most recent media published by a user

    // 1133961085 id vespa_ecuador

    $likes = $instagram->getMediaLikes('1090835460940398909_1640259127');

   print_r($item->id);
       echo "xxxx";


    $media = $instagram->getTagMedia('vespaecuador');

    //echo json_encode($media);

    $dataTotal = array();

    foreach ($media->data as $item) {
        //  print_r($item);
// echo "xxx";
        $data = array();
        $data ['image_link'] = $item->images->standard_resolution->url;
        if (!empty($item->caption)) {
            $data ['caption'] = $item->caption->text;
        } else {
            $data ['caption'] = '';
        }

        if (!empty($item->user)) {
            $data ['user'] = $item->user->username;
            $data ['userPicture'] = $item->user->profile_picture;
        } else {
            $data ['user'] = '';
            $data ['userPicture'] = '';
        }
        $data ['id'] = $item->id;

        //$likes = $instagram->getMediaLikes('1090835460940398909_1640259127');

//  print_r($item->id);
        //    echo "xxxx";



        $dataLinkArray = array ();
        $dataLink = "";
        foreach ($item->likes->data as $itemLink) {
            //$dataLink .=  '<a href="https://instagram.com/'.  $itemLink->username. '" class="usuarios">'  . $itemLink->username . "</a> ";
            $dataLink .=   $itemLink->username . ", ";
            //$dataLinkArray[] = $itemLink->username;
            $a = array();
            $a['nombre'] = $itemLink->username;
            $dataLinkArray[] = $a;
        }
        $data ['dataLink'] =  $dataLink;
        $data ['dataUser'] =  $dataLinkArray;

        $data ['likes'] = $item->likes->count;
        $data ['created_time'] = $item->created_time;


        $dataTotal[] = $data;
    }

    $mediaUser = $instagram->getUserMedia('1133961085');

    foreach ($mediaUser->data as $item) {
        //print_r($item);
        $data = array();
        $data ['image_link'] = $item->images->standard_resolution->url;
        if (!empty($item->caption)) {
            $data ['caption'] = $item->caption->text;
        } else {
            $data ['caption'] = '';
        }

        if (!empty($item->user)) {
            $data ['user'] = $item->user->username;
            $data ['userPicture'] = $item->user->profile_picture;
        } else {
            $data ['user'] = '';
            $data ['userPicture'] = '';
        }
        $data ['id'] = $item->id;

        $dataLinkArray = array ();
        $dataLink = "";
        foreach ($item->likes->data as $itemLink) {
            //$dataLink .=  '<a href="https://instagram.com/'.  $itemLink->username. '" class="usuarios">'  . $itemLink->username . "</a> ";
            $dataLink .=   $itemLink->username . ", ";
            //$dataLinkArray[] = $itemLink->username;
            $a = array();
            $a['nombre'] = $itemLink->username;
            $dataLinkArray[] = $a;
        }
        $data ['dataLink'] =  $dataLink;
        $data ['dataUser'] =  $dataLinkArray;


        $data ['likes'] = $item->likes->count;

        $data ['created_time'] = $item->created_time;



        $dataTotal[] = $data;
    }


    usort($dataTotal, function ($a, $b) {
        return strcasecmp($b['created_time'], $a['created_time']);
        //return $a['created_time'] - $b['created_time'];
    });
    // borrar repetidos

    $newDataTotal = array();
    for ($i = 0; $i < count($dataTotal); $i++) {
        if (isset($dataTotal[$i + 1])) {
            if ($dataTotal[$i]['image_link'] != $dataTotal[$i + 1]['image_link']) {
                $newDataTotal[] = $dataTotal[$i];
            }
        }
    }
    $dataTotal = $newDataTotal;

    echo json_encode($dataTotal);
}
//https://www.misiva.com.ec/vespainstagram/json2.php?code=40a5095bb5484217b0f324798c7ad865

foreach ($dataTotal as $item) {
    // echo "<img src=\"{$item['image_link']}\">";
}
?>