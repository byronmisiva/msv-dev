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
$database = new MySQL();
global $database;

if ($database->Query("SELECT mensaje, url FROM kiiconnect_compartir ")) {
    $mensajes = $database->RecordsArray();
    $tags = array();
    if ($mensajes != false) {
        foreach ($mensajes as $mensaje) {
            $tags['url'] = $mensaje['url'];
            $tags['mensaje'] = $mensaje['mensaje'];
        }
    }
//echo $database->GetJSON();
    echo json_encode($tags);

} else {
    echo "<p>Query Failed</p>";
}
