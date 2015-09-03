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
    'server' => '69.64.85.197',
    'username' => 'externo',
    'password' => 'feadmin06',
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
    global $databaseSamsung;

    $data = json_decode(stripslashes($_POST["data"]));
    $databaseSamsung->update("samsung_karaoke_galaxya", [
        "aprobado" => $data->aprobado
    ], [
        "id" => $data->id
    ]);


    echo json_encode(array(
        "success" => $databaseSamsung->error() == 0,
        "msg" => $databaseSamsung->error() == 0 ? " actualizado exitosamente" : $databaseSamsung->error()
    ));
}


switch ($_GET['operation']) {
    case 'selectSamsungKaraoke' :
        selectSamsungKaraoke();
        break;
    case 'updateSamsungKaraoke' :
        updateSamsungKaraoke();
        break;
}