<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Content-type: text/html; charset=utf-8');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}

include("mysql.class.php");

$databaseXmltv = new MySQL();


function googleadwords()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT contenido AS parametros
                                FROM kiiconnect_parametros WHERE id = 1 AND activo = 1 ")) {
         // echo $databaseXmltv->GetJSON();
        $data = $databaseXmltv->RecordsArray(MYSQLI_ASSOC);
    } else {
        echo "<p>Query Failed</p>";
    }

    $data2 =  json_decode ($data[0]['parametros']);
    echo json_encode(array(
            "success" => true,
            "data" => $data2)
    );
}

function googleadwordsYoutube()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT contenido AS parametros
                                FROM kiiconnect_parametros WHERE id = 3 AND activo =1 ")) {
         // echo $databaseXmltv->GetJSON();
        $data = $databaseXmltv->RecordsArray(MYSQLI_ASSOC);
    } else {
        echo "<p>Query Failed</p>";
    }

    $data2 =  json_decode ($data[0]['parametros']);
    echo json_encode(array(
            "success" => true,
            "data" => $data2)
    );
}

function googleadwordsInstalaciones()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT contenido AS parametros
                                FROM kiiconnect_parametros WHERE id = 4 AND activo = 1 ")) {
         // echo $databaseXmltv->GetJSON();
        $data = $databaseXmltv->RecordsArray(MYSQLI_ASSOC);
    } else {
        echo "<p>Query Failed</p>";
    }

    $data2 =  json_decode ($data[0]['parametros']);
    echo json_encode(array(
            "success" => true,
            "data" => $data2)
    );
}

function googleadwordsAndroid()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT contenido AS parametros
                                FROM kiiconnect_parametros WHERE id = 2 AND activo = 1")) {
        // echo $databaseXmltv->GetJSON();
        $data = $databaseXmltv->RecordsArray(MYSQLI_ASSOC);
    } else {
        echo "<p>Query Failed</p>";
    }

    $data2 =  json_decode ($data[0]['parametros']);
    echo json_encode(array(
            "success" => true,
            "data" => $data2)
    );
}


switch ($_GET['operation']) {
    case 'google-adwords' :
        googleadwords();
        break;
    case 'google-adwords-ios' :
        googleadwords();
        break;
    case 'google-adwords-android' :
        googleadwordsAndroid();
        break;

    case 'google-adwords-ios-youtube' :
        googleadwordsYoutube();
        break;

    case 'google-adwords-ios-instalaciones' :
        googleadwordsInstalaciones();
        break;


}