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

function selectKiiconnect()
{
    global $databaseSamsung;

    if ($databaseSamsung->Query("SELECT * FROM samsung_kiiconnect_setting ORDER BY nombre")) {
        // echo $databaseSamsung->GetJSON();
        $data =  $databaseSamsung->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data)
    );
}

function updateKiiconnect()
{
    global $databaseSamsung;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["aprobado"] = MySQL::SQLValue($data->aprobado, MySQL::SQLVALUE_NUMBER);
    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseSamsung->UpdateRows("samsung_kiiconnect_setting", $update, $where);


    echo json_encode(array(
        "success" => $databaseSamsung->ErrorNumber() == 0,
        "msg" => $databaseSamsung->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseSamsung->error()
    ));
}


switch ($_GET['operation']) {
    case 'selectKiiconnect' :
        selectKiiconnect();
        break;
    case 'updateKiiconnect' :
        updateKiiconnect();
        break;
}