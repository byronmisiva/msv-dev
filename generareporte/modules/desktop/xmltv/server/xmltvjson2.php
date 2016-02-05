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
if ($databaseXmltv->Query("SELECT xmltv_channel.id,
                                xmltv_channel.display_name,
                                xmltv_channel.file
                            FROM xmltv_channel
                            WHERE activo = 1
                            ORDER BY 'order' ASC")
) {
    $canales = $databaseXmltv->GetJSON();
} else {
    $canales = "''";
};
//CONCAT(UNIX_TIMESTAMP (fecha_inicio), '000') as inicio,
//CONCAT(UNIX_TIMESTAMP (fecha_fin), '000') as fin,
// consulta que me devuelve los programas segun la fecha (ejemplo en fecha actual )

$programas = programacion($fecha);
//DAYOFWEEK(xmltv_schedules.creado),

if ($databaseXmltv->Query("SELECT xmltv_schedules.id,
                                xmltv_schedules.id_channel,
                                xmltv_schedules.id_programme,
                                xmltv_programme.title,
                                IF(length(xmltv_schedules.description) > 0, CONCAT(xmltv_programme.description, ', ' , xmltv_schedules.description) , xmltv_programme.description ) AS description,
                                xmltv_programme.category,
                                CONCAT(CAST(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', time)) AS INT), '000') as start,
                                CONCAT(CAST(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', time)) AS INT) + xmltv_schedules.duration * 60, '000') as stop,
                                xmltv_schedules.time,
                                xmltv_programme.file
                            FROM xmltv_schedules INNER JOIN xmltv_programme ON xmltv_schedules.id_programme = xmltv_programme.id
                            WHERE IF(xmltv_schedules.date_end IS NOT NULL, xmltv_schedules.date_star < CURDATE() AND CURDATE() < xmltv_schedules.date_end , xmltv_schedules.date_star < CURDATE()) AND
							xmltv_programme.activo = 1;
							")
) {
    $programas = $databaseXmltv->GetJSON();
} else {
    $programas = "''";
};

//////////////////////////

$json = '[{
"category": "channel",
"items":' . $canales . '},
{
"category": "programme",
"items":  ' . $programas . '}]';

echo $json;


function programacion($fecha)
{
    return 1;
}