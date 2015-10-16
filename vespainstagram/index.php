<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="content-language" content="es"/>
    <meta name="robots" content="follow,index,nocache"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="description" content=""/>
    <meta name="author" content="Misiva Corp"/>
    <title>Vespa</title>
    <!-- Bootstrap core CSS -->

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <style>

        body {
            margin: 0;
            padding: 0;
            width: 100%;

        }

        .ca {
            background-color: #ffffff;
            overflow: hidden;
            margin-bottom: 10px;
        }

        a, a:visited {
            color: #125688;
            text-decoration: none;
        }

        .contenimage {
            width: 100%
        }

        .contenimage img {
            width: 100%
        }

        .userPicture {
            width: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .header_ca {
            padding: 14px 20px;
            border-top: 1px solid #e1e1e1;
            color: #125688;
            font-size: 13px;
            line-height: 17px;
            font-weight: 600;
            text-overflow: ellipsis;
            font-family: 'proxima-nova', 'Helvetica Neue', Arial, Helvetica, sans-serif;
            float: left;
            width: 100%;
        }

        .caption_ca {
            font-size: 11px;
            margin: 14px 20px;
            color: #125688;
            font-size: 13px;
            line-height: 16px;
            font-weight: 400;
            text-overflow: ellipsis;
            font-family: 'proxima-nova', 'Helvetica Neue', Arial, Helvetica, sans-serif;

        }

        .user_ca {
            margin: 8px 0;
            float: left;
        }

        .header_ca img {
            float: left;
        }
    </style>

</head>
<body>

<?php
function processURL($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 2
    ));

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

$tag = 'vespaecuador';
$client_id = "ab9ad048ea114512a095a0c3237fc5ca";
$url = 'https://api.instagram.com/v1/tags/' . $tag . '/media/recent?client_id=' . $client_id;

$all_result = processURL($url);

//echo $all_result;
$decoded_results = json_decode($all_result, true);

//Now parse through the $results array to display your results...
foreach ($decoded_results['data'] as $item) {
    //print_r($item);
    $image_link = $item['images']['standard_resolution']['url'];
    if (!empty($item['caption'])) {
        $caption = $item['caption'][text];
    } else {
        $caption = '';
    }

    if (!empty($item['user'])) {
        $user = $item['user']['username'];
        $userPicture = $item['user']['profile_picture'];
    } else {
        $user = '';
        $userPicture = '';
    }

    echo "<div class=\"ca\"><div class=\"header_ca\"><img  src=\"{$userPicture}\" class=\"userPicture\"><div class=\"user_ca\">{$user}</div></div>
            <div class=\"contenimage\"><a href='#'onclick=\"window.open('instagram://media?id={$item['id']}', '_system', 'location=yes'); return false;\"> <img  src=\"{$image_link}\"></a></div>
            <div class=\"caption_ca\">{$item['likes']['count']} ♥ </br>{$caption}</div></div>";
}


?>
</body>
</html>