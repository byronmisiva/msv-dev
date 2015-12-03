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
$databaseKiiconnect = new MySQL();
global $databaseKiiconnect;

// recuperar codigo de don balos
if (isset($_GET["tags"])) {
    $tags = $_GET["tags"];
    $bodytag = '"' . str_replace(",", '","', $tags) . '"';
    $consultaTag = "AND tag in ($bodytag)";
} else {
    $consultaTag = "";
}

if ($databaseKiiconnect->Query("SELECT *
                                FROM
                                    kiiconnect_mensajes
                                    WHERE activo = 1 $consultaTag
                                    ORDER BY creado DESC  ")
) {
    echo $databaseKiiconnect->GetJSON();
} else {
    echo "<p>Query Failed</p>";
}
