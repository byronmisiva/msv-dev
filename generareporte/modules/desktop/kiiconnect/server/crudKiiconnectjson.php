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
if ($databaseSamsung->Query("SELECT
                                    samsung_kiiconnect_setting.nombre,
                                    samsung_kiiconnect_setting.tag,
                                    samsung_kiiconnect_setting.descripcion,
                                    samsung_kiiconnect_setting.icono,
                                    samsung_kiiconnect_setting.link,
                                    samsung_kiiconnect_categoria.nombre AS categoria,
                                    samsung_kiiconnect_categoria.id AS id_categoria,
                                    samsung_kiiconnect_categoria.icono AS categoria_icono
                                FROM
                                    samsung_kiiconnect_setting
                                INNER JOIN samsung_kiiconnect_categoria ON samsung_kiiconnect_setting.id_categoria = samsung_kiiconnect_categoria.id
                                WHERE
                                    activo = 1
                                ORDER BY
                                    orden ASC")
) {
    echo $databaseSamsung->GetJSON();

} else {
    echo "<p>Query Failed</p>";
}