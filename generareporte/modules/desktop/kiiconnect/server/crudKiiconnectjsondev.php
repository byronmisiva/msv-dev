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

if (!isset($_GET["parametro"])){
    if ($database->Query("SELECT
                                    kiiconnect_setting.nombre,
                                    kiiconnect_setting.tag,
                                    kiiconnect_setting.descripcion,
                                    kiiconnect_setting.icono,
                                    kiiconnect_setting.link,
                                    kiiconnect_categoria.nombre AS categoria,
                                    kiiconnect_categoria.id AS id_categoria,
                                    kiiconnect_categoria.filecategoria,
                                    kiiconnect_categoria.filecategoria2,
                                    kiiconnect_categoria.icono AS categoria_icono
                                FROM
                                    kiiconnect_setting
                                INNER JOIN kiiconnect_categoria ON kiiconnect_setting.id_categoria = kiiconnect_categoria.id
                                WHERE
                                    activo = 1
                                ORDER BY
                                    orden ASC")
    ) {
        echo $database->GetJSON();

    } else {
        echo "<p>Query Failed</p>";
    }
} else {
    $temp = $database->QueryArray("SELECT
                                    kiiconnect_categoria.nombre AS categoria,
                                    kiiconnect_categoria.id AS id_categoria,
                                    kiiconnect_categoria.iconodev AS categoria_icono,
                                    kiiconnect_categoria.filecategoriadev AS filecategoria
                                FROM
                                    kiiconnect_categoria
                                WHERE
                                  activo = 1
                                ORDER BY orden2", MYSQL_ASSOC);
    $temp2 = array();
    foreach ($temp as $index=>$categoria){
        $categoria_id = $categoria['id_categoria'];
//            CONCAT('" . '<span style="font-weight:bold">' ." ', kiiconnect_setting.nombre, '</span>') AS nombre,

        $itemsCategoria = $database->QueryArray("SELECT
                                    kiiconnect_setting.nombre,
                                    kiiconnect_setting.tag,
                                    kiiconnect_setting.descripcion,
                                    kiiconnect_setting.icono,
                                    kiiconnect_setting.file,
                                    kiiconnect_setting.link
                                FROM
                                    kiiconnect_setting
                                 WHERE
                                    activo = 1 AND id_categoria = $categoria_id
                                ORDER BY
                                    orden ASC", MYSQL_ASSOC);
        if ($itemsCategoria!= false) {
            $categoria['items'] = $itemsCategoria;
            $temp2[] = $categoria;
        }
    }
    $temp3 = json_encode($temp2);
    echo $temp3;
}