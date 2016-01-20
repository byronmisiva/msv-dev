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

if (isset($_POST['body'])) $body = $_POST['body']; else $body = "";
if (isset($_POST['p'])) $p = $_POST['p']; else $p = "";
if (isset($_POST['l'])) $l = $_POST['l']; else $l = "";
if (isset($_POST['header'])) {
    $header = $_POST['header'];
    $parametros = explode(";", $header);
    if (count($parametros) > 0) {
        ($parametros[1] != "0") ? $tag = $parametros[1] : $tag = "";
        ($parametros[2] != "0") ? $l = $parametros[2] : $l = "";

        ($parametros[0] != "0") ? $richpage = $parametros[0] : $richpage = "";
        ($parametros[0] != "0") ? $l = $parametros[0] : $richpage = "";
    } else {
        $richpage = "";
        $tag = "";
    }
} else {
    $header = "";
    $richpage = "";
    $tag = "";
}



// validar que el mismo registro no exista

//1 recuperamos el ultimo registro
if ($database->Query("SELECT MAX(creado) as creado FROM kiiconnect_mensajes;")) {
    $data = $database->RecordsArray();
    $fechaCreado = $data[0]['creado'];
    //2 comparamos si existio otro registro similar en el tiempo
    $sql = "SELECT NOW() as fecha";
    if ($database->Query($sql)) {


        $data = $database->RecordsArray();
        $fechaServidor = $data[0]['fecha'];

        $datetime1 = new DateTime($fechaCreado);
        $datetime2 = new DateTime($fechaServidor);

        $diferencia = $datetime1->diff($datetime2);

        if (($diferencia->y == 0) && ($diferencia->m == 0) && ($diferencia->d == 0) && ($diferencia->h == 0) && ($diferencia->i == 0) && ($diferencia->s == 0)) {
            // registro en el mismo minuto se omite
        } else {
            // registro se inserta

            $update["body"] = MySQL::SQLValue($body);
            $update["header"] = MySQL::SQLValue($header);
            $update["p"] = MySQL::SQLValue($p);
            $update["l"] = MySQL::SQLValue($l);
            $update["richpage"] = MySQL::SQLValue($richpage);
            $update["tag"] = MySQL::SQLValue($tag);
            $update["tagsetings"] = MySQL::SQLValue($tag);
            $update["latitud"] = MySQL::SQLValue($fechaCreado);
            $update["longuitud"] = MySQL::SQLValue($fechaServidor);
            $database->InsertRow("kiiconnect_mensajes", $update);
        }

    } else {
        echo "<p>Query Failed</p>";
    }
} else {
    echo "<p>Query Failed</p>";
}


echo '{"status":"OK"}';