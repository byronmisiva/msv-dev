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

if ($databaseXmltv->Query("SELECT id,
                                  id_canal,
                                     titulo,
                                     descripcion,
                                      tipo,
                                      CONCAT(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', '12:00:00')), '000') as inicio,
                                      CONCAT(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', '13:00:00')), '000') as fin,
                                     duracion,
                                     file
                                     FROM xmltv_programa WHERE activo = 1 ORDER BY id_canal;")
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
"items":  ' . $programas . '}]';

echo jxgcompress($json);

function jxgcompress($json)
{
    return base64_encode(gzcompress($json, 9));
}