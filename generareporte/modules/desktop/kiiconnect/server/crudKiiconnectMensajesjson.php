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

if (isset($_GET["secciones"])) {
    $fechaentrada = date('Y-m-d H:i:s');
    $fecha = " AND `kiiconnect_mensajes`.`creado`  + INTERVAL 7 DAY  > '$fechaentrada' ";

    $secciones = $_GET["secciones"];

    //$secciones = eliminartag ('blue_card', $secciones);

    if ($secciones == "") {
        echo "[]";
        logMensajes($_GET);
        return;
    }

    // if version
    if (isset($_GET["version"])) {
        $version = $_GET["version"];
        $version = str_replace('.', '', $version);

        $plataforma = $_GET["plataforma"];

        if ($plataforma == 'iOS') {
            if ($version >= 1111)
                $secciones = "," . $secciones;
        }

        if ($plataforma == 'Android') {
            if ($version >= 118)
                $secciones = "," . $secciones;
        }
    }

    $totalsecciones = explode(",", $secciones);
    /// Recupera y ordena datos de cada seccion
    $noticiasOrden = array();

    if (count($totalsecciones) != 0)
        $total = count($totalsecciones) * 2;
    else $total = 0;
    $secciones2 = explode("/", $secciones);
    $secciones = $secciones2[0];
    $seccionesNew = '"' . str_replace(',', '","', $secciones) . '"';

    // todo , DAYOFYEAR(kiiconnect_mensajes.creado) para el caso de que se quiera hacer que se repita dependiendo del día
    $seccion = str_replace('interno', '', $seccionesNew);


    $resultados = array();

    foreach ($totalsecciones as $index1 => $seccion) {


        $sql = "SELECT kiiconnect_setting.icono,
                       kiiconnect_mensajes.id,
                       kiiconnect_mensajes.body,
                       kiiconnect_mensajes.title,
                       kiiconnect_mensajes.header,
                       kiiconnect_mensajes.p,
                       kiiconnect_mensajes.l,
                       kiiconnect_mensajes.tag,
                       kiiconnect_mensajes.longuitud,
                       kiiconnect_mensajes.latitud,
                       kiiconnect_mensajes.richpage,
                       kiiconnect_mensajes.activo,
                       kiiconnect_mensajes.creado,
                       kiiconnect_mensajes.tagsetings,
                       kiiconnect_setting.nombre,
                       kiiconnect_setting.file
                                FROM kiiconnect_mensajes INNER JOIN kiiconnect_setting ON kiiconnect_mensajes.tag = kiiconnect_setting.tag
                                WHERE kiiconnect_mensajes.activo = 1 AND kiiconnect_mensajes.tag IN ('$seccion') $fecha

                                ORDER BY kiiconnect_mensajes.creado DESC";

        if ($databaseKiiconnect->Query($sql)
        ) {
            if ($databaseKiiconnect->RecordsArray(MYSQL_ASSOC) != 'null') {
                $data = $databaseKiiconnect->RecordsArray(MYSQL_ASSOC);
                if ($data) {
                    $resultados = array_merge($resultados, $data);
                }
            } else
                echo "[]";
        } else {
            echo "<p>Query Failed</p>";
        }
    }
    foreach ($resultados as $key => $arr):
        $resultados[$key]['body'] = $arr['body'];
        $pun[$key] = $arr['creado'];
        if (isset ($arr['title'])) {
            if (strlen($arr['title']) > 0) {
                $resultados[$key]['nombre'] = $arr['title'];
            }
        }


    endforeach;
    array_multisort($pun, SORT_DESC, $resultados);
    $jsonNulo = '{
                "id": "",
                "body": "",
                "header": "",
                "p": "",
                "l": "",
                "tag": "",
                "tagsetings": "",
                "longuitud": "",
                "latitud": "",
                "richpage": "",
                "activo": "",
                "creado": "",
                "nombre": "",
                "descripcion": "",
                "slogan": "",
                "icono": "imagenes/kiiconnect/blanco.png",
                "link": "",
                "orden": "",
                "file": "data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAADcAAAA3AQMAAACSFUAFAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAA1JREFUGNNjGAVDEgAAAbgAAWOsGIkAAAAASUVORK5CYII=",
                "id_categoria": ""
            }';
    $jsonNulo = json_decode($jsonNulo);
    if (count($resultados) > 6) {
        $resultados[] = $jsonNulo;
    }
    echo json_encode($resultados);

} else {
    if ($databaseKiiconnect->Query("SELECT *,
	kiiconnect_setting.icono,
	kiiconnect_mensajes.id,
	kiiconnect_mensajes.body,
	kiiconnect_mensajes.header,
	kiiconnect_mensajes.p,
	kiiconnect_mensajes.l,
	kiiconnect_mensajes.tag,
	kiiconnect_mensajes.longuitud,
	kiiconnect_mensajes.latitud,
	kiiconnect_mensajes.richpage,
	kiiconnect_mensajes.activo,
	kiiconnect_mensajes.creado,
	kiiconnect_mensajes.tagsetings,
	kiiconnect_setting.nombre
FROM kiiconnect_mensajes INNER JOIN kiiconnect_setting ON kiiconnect_mensajes.tagsetings = kiiconnect_setting.tag
WHERE kiiconnect_mensajes.activo = 1
GROUP BY kiiconnect_mensajes.body
ORDER BY kiiconnect_mensajes.creado DESC ")
    ) {
        echo $databaseKiiconnect->GetJSON();
    } else {
        echo "<p>Query Failed</p>";
    }
}

logMensajes($_GET);

function logMensajes($json)
{
    $file = 'log.txt';
    $json = json_encode($json) . "\n\n";
    //$json = $json . json_encode($databaseKiiconnect->GetLastSQL())  . "\n";
    file_put_contents($file, $json, FILE_APPEND | LOCK_EX);
}

function cortarTexto($texto, $numMaxCaract)
{
    if (strlen($texto) < $numMaxCaract) {
        $textoCortado = $texto;
    } else {
        $textoCortado = substr($texto, 0, $numMaxCaract);
        $ultimoEspacio = strripos($textoCortado, " ");

        if ($ultimoEspacio !== false) {
            $textoCortadoTmp = substr($textoCortado, 0, $ultimoEspacio);
            if (substr($textoCortado, $ultimoEspacio)) {
                $textoCortadoTmp .= '...';
            }
            $textoCortado = $textoCortadoTmp;
        } elseif (substr($texto, $numMaxCaract)) {
            $textoCortado .= '...';
        }
    }

    return $textoCortado;
}

function eliminartag ($tag, $secciones ){
    //eliminar tag blue_card
    $secciones = str_replace($tag, '', $secciones);
    $secciones = str_replace(',,', ',', $secciones);

    if (substr($secciones, -1) == ",") {
        $secciones = substr($secciones, 0, -1);
    }

    if (substr($secciones, 0, 1) == ",") {
        $secciones = substr($secciones, 1);
    }
    return $secciones;
}