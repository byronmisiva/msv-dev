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

//{"body":"test 3","header":"0;juan_valdez","p":"3Ff"}

/*if (isset($_POST['body'])) $body = $_POST['body']; else $body = "test 5";
if (isset($_POST['p'])) $p = $_POST['p']; else $p = "3Ff";
if (isset($_POST['l'])) $l = $_POST['l']; else $l = "http://www.kfc.com.ec";
if (isset($_POST['header'])) {
    $header = $_POST['header'];
    $parametros = explode(";", $header);
    if (count($parametros)> 0) {
        ($parametros[0] != "0") ? $richpage = $parametros[0] :$richpage = "" ;
        ($parametros[1] != "0") ? $tag = $parametros[1] :$tag = "" ;
    } else {
        $richpage = "0";
        $tag = "juan_valdez";
    }
} else {
    $header = "0;juan_valdez";
    $richpage = "0";
    $tag = "juan_valdez";
}*/

if (isset($_POST['body'])) $body = $_POST['body']; else $body = "";
if (isset($_POST['p'])) $p = $_POST['p']; else $p = "";
if (isset($_POST['l'])) $l = $_POST['l']; else $l = "";
if (isset($_POST['header'])) {
    $header = $_POST['header'];
    $parametros = explode(";", $header);
    if (count($parametros) > 0) {
        ($parametros[0] != "0") ? $richpage = $parametros[0] : $richpage = "";
        ($parametros[1] != "0") ? $tag = $parametros[1] : $tag = "";
        ($parametros[2] != "0") ? $tagsetings = $parametros[2] : $tagsetings = "";
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
if ($database->Query("SELECT MAX(creado) as creado FROM samsung_kiiconnect_mensajes;")) {
    $data = $database->RecordsArray();
    $fechaCreado = $data[0]['creado'];
    //2 comparamos si existio otro registro similar en el tiempo
    $sql = "SELECT NOW() as fecha";
    if ($database->Query($sql)) {
        //  if ($database->Query("select * from samsung_kiiconnect_mensajes where creado > date_sub('2015-09-23 11:16:30', interval 1 minute) ;")) {
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
            $update["tag"] = MySQL::SQLValue($tagsetings);
            $update["tagsetings"] = MySQL::SQLValue($tagsetings);
            $update["latitud"] = MySQL::SQLValue($fechaCreado);
            $update["longuitud"] = MySQL::SQLValue($fechaServidor);
            $database->InsertRow("samsung_kiiconnect_mensajes", $update);
        }

    } else {
        echo "<p>Query Failed</p>";
    }
} else {
    echo "<p>Query Failed</p>";
}


echo '{"status":"OK"}';