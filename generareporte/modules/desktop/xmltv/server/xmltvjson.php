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

//////////////////////////

include("mysql.class.php");

$databaseXmltv = new MySQL();
if ($databaseXmltv->Query("SELECT xmltv_canal.id,
                                    xmltv_canal.nombre,
                                    xmltv_canal.file
                                     FROM xmltv_canal WHERE activo = 1 ORDER BY orden")
) {
    $canales = $databaseXmltv->GetJSON();
} else {
    $canales = "''";
};
//CONCAT(UNIX_TIMESTAMP (fecha_inicio), '000') as inicio,
//CONCAT(UNIX_TIMESTAMP (fecha_fin), '000') as fin,
// consulta que me devuelve los programas segun la fecha (ejemplo en fecha actual )

$programas = programacion ($fecha);

if ($databaseXmltv->Query("SELECT xmltv_programacion.id,
                                xmltv_programacion.id_canal,
                                xmltv_programacion.id_programa,
                                xmltv_programa.titulo,
                                 IF(length(xmltv_programacion.descripcion) > 0, CONCAT(xmltv_programa.descripcion, ', ' , xmltv_programacion.descripcion) , xmltv_programa.descripcion ) AS descripcion,
                                xmltv_programa.tipo,
DAYOFWEEK(xmltv_programacion.creado),
                                CONCAT(CAST(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', horario)) AS INT), '000') as inicio,
                                CONCAT(CAST(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', horario)) AS INT) + xmltv_programacion.duracion * 60, '000') as fin,
                                xmltv_programacion.duracion,
                                xmltv_programa.file
                            FROM xmltv_programacion INNER JOIN xmltv_programa ON xmltv_programacion.id_programa = xmltv_programa.id
							WHERE IF(xmltv_programacion.fecha_fin IS NOT NULL, xmltv_programacion.fecha_inicio < CURDATE() AND CURDATE() < xmltv_programacion.fecha_fin , xmltv_programacion.fecha_inicio < CURDATE()) AND
							xmltv_programa.activo = 1;
							")
) {
    $programas = $databaseXmltv->GetJSON();
} else {
    $programas = "''";
};

//////////////////////////

$json = '[{
"categoria": "canal",
"items":' . $canales . '},
{
"categoria": "programa",
"items":  ' .  $programas . '}]';

echo $json;


function programacion ($fecha) {
    return 1;
}