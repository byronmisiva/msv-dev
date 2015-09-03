<?php

//http://medoo.in/api/select

require_once '../../../../server/os.php';

$os = new os();
if (!$os->session_exists()) {
    die('No existe sesión!');
}

require_once 'medoo.min.php';
$databaseSamsung = new medoo([
    // required
    'database_type' => 'mysql',
    'database_name' => 'appss',
    'server' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',

]);

function selectSamsungKaraoke()
{
    global $os;
    global $databaseSamsung;

    $os->db->conn->query("SET NAMES 'utf8'");
    $datas = $databaseSamsung->select("samsung_karaoke_galaxya", [
        "id",
        "id_user",
        "fbid",
        "filenameimage",
        "filename",
        "creado",
        "aprobado",
        "nombre"
    ], ["ORDER" => "creado DESC"    ]);

    echo json_encode(array(
            "success" => true,
            "data" => $datas)
    );
}

function updateSamsungKaraoke()
{
    global $os;
    global $databaseSamsung;

    $os->db->conn->query("SET NAMES 'utf8'");
    $datas = $databaseSamsung->select("samsung_karaoke_galaxya", [
        "id",
        "id_user",
        "fbid",
        "filenameimage",
        "filename",
        "creado",
        "aprobado",
        "nombre"
    ]);

    echo json_encode(array(
            "success" => true,
            "data" => $datas)
    );
}


switch ($_GET['operation']) {
    case 'selectSamsungKaraoke' :
        selectSamsungKaraoke();
        break;
    case 'updateSamsungKaraoke' :
        updateSamsungKaraoke();
        break;
}