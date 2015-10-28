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
$databaseSamsung = new MySQL();
global $databaseSamsung;


if (isset($_GET["secciones"])) {

    if (isset($_GET["fecha"] )) {
        $fechaentrada = $_GET["fecha"];
        $fecha = " AND `samsung_kiiconnect_mensajes`.`creado`  + INTERVAL 3 HOUR  > '$fechaentrada' ";

    } else {
        $fecha = "";
    }
    $secciones = $_GET["secciones"];
    if ($secciones == "") {
        echo "[]";
        return;
    }

    $totalsecciones = explode(",", $secciones);

    /// Recupera y ordena datos de cada seccion
    $noticiasOrden = array();

    if (count($totalsecciones) != 0)
        $total = count($totalsecciones) * 2;
    else $total = 0
        ;
    $secciones2 = explode("/", $secciones);
    $secciones = $secciones2[0];



    $seccionesNew = '"' . str_replace (',', '","', $secciones)  . '"';

    // todo , DAYOFYEAR(samsung_kiiconnect_mensajes.creado) para el caso de que se quiera hacer que se repita dependiendo del día

    if ($databaseSamsung->Query("SELECT *,
	samsung_kiiconnect_setting.icono,
	samsung_kiiconnect_mensajes.id,
	samsung_kiiconnect_mensajes.body,
	samsung_kiiconnect_mensajes.header,
	samsung_kiiconnect_mensajes.p,
	samsung_kiiconnect_mensajes.l,
	samsung_kiiconnect_mensajes.tag,
	samsung_kiiconnect_mensajes.longuitud,
	samsung_kiiconnect_mensajes.latitud,
	samsung_kiiconnect_mensajes.richpage,
	samsung_kiiconnect_mensajes.activo,
	samsung_kiiconnect_mensajes.creado,
	samsung_kiiconnect_mensajes.tagsetings,
	samsung_kiiconnect_setting.nombre
FROM samsung_kiiconnect_mensajes INNER JOIN samsung_kiiconnect_setting ON samsung_kiiconnect_mensajes.tagsetings = samsung_kiiconnect_setting.tag
WHERE samsung_kiiconnect_mensajes.activo = 1 AND samsung_kiiconnect_mensajes.tag IN ($seccionesNew) $fecha
GROUP BY samsung_kiiconnect_mensajes.body
ORDER BY samsung_kiiconnect_mensajes.creado DESC
  LIMIT $total")
    ) {
        if ($databaseSamsung->GetJSON() != 'null')

        echo $databaseSamsung->GetJSON();
        else
            echo "[]";

    } else {
        echo "<p>Query Failed</p>";
    }

    foreach ($totalsecciones as $index1 => $seccion) {


    }
} else {
    if ($databaseSamsung->Query("SELECT *,
	samsung_kiiconnect_setting.icono,
	samsung_kiiconnect_mensajes.id,
	samsung_kiiconnect_mensajes.body,
	samsung_kiiconnect_mensajes.header,
	samsung_kiiconnect_mensajes.p,
	samsung_kiiconnect_mensajes.l,
	samsung_kiiconnect_mensajes.tag,
	samsung_kiiconnect_mensajes.longuitud,
	samsung_kiiconnect_mensajes.latitud,
	samsung_kiiconnect_mensajes.richpage,
	samsung_kiiconnect_mensajes.activo,
	samsung_kiiconnect_mensajes.creado,
	samsung_kiiconnect_mensajes.tagsetings,
	samsung_kiiconnect_setting.nombre
FROM samsung_kiiconnect_mensajes INNER JOIN samsung_kiiconnect_setting ON samsung_kiiconnect_mensajes.tagsetings = samsung_kiiconnect_setting.tag
WHERE samsung_kiiconnect_mensajes.activo = 1
GROUP BY samsung_kiiconnect_mensajes.body
ORDER BY samsung_kiiconnect_mensajes.creado DESC ")
    ) {
        echo $databaseSamsung->GetJSON();

    } else {
        echo "<p>Query Failed</p>";
    }
}

logMensajes($_GET, $databaseSamsung);

function logMensajes($json, $databaseSamsung)
{
    $file = 'log.txt';
    $json = json_encode($json) . "\n";
    //$json = $json . json_encode($databaseSamsung->GetLastSQL())  . "\n";
    file_put_contents($file, $json, FILE_APPEND | LOCK_EX);
}


