<?php

//http://medoo.in/api/select

//http://localhost:10088/msv-dev/generareporte/modules/desktop/samsung/server/help.html#Error

require_once '../../../../server/os.php';

$os = new os();
if (!$os->session_exists()) {
    die('No existe sesión!');
}

include("mysql.class.php");

$databaseSamsung = new MySQL();

function selectSamsungPreguntados()
{
    global $databaseSamsung;

    if ($databaseSamsung->Query("SELECT samsung_usuarios.nombre,
	samsung_usuarios.apellido,
	samsung_usuarios.mail,
	samsung_usuarios.fbid,
	samsung_usuarios.cedula,
	samsung_usuarios.telefono,
	samsung_usuarios.ciudad,
	samsung_registro_preguntados.actividad,
	samsung_registro_preguntados.invitartw,
	samsung_registro_preguntados.invitarfb,
	samsung_registro_preguntados.nivel,
	samsung_registro_preguntados.tiempo,
	samsung_registro_preguntados.id
FROM samsung_registro_preguntados INNER JOIN samsung_usuarios ON samsung_registro_preguntados.id_user = samsung_usuarios.id
ORDER BY samsung_usuarios.apellido ASC, samsung_usuarios.nombre ASC")) {
       // echo $databaseSamsung->GetJSON();
        $data3 =  $databaseSamsung->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data3)
    );
}

function updateSamsungKaraoke()
{
    global $databaseSamsung;

    $data = json_decode(stripslashes($_POST["data"]));



    $update["aprobado"] = MySQL::SQLValue($data->aprobado, MySQL::SQLVALUE_NUMBER);
    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseSamsung->UpdateRows("samsung_karaoke_galaxya", $update, $where);


    echo json_encode(array(
        "success" => $databaseSamsung->ErrorNumber() == 0,
        "msg" => $databaseSamsung->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseSamsung->error()
    ));
}


switch ($_GET['operation']) {
    case 'select' :
        selectSamsungPreguntados();
        break;

}