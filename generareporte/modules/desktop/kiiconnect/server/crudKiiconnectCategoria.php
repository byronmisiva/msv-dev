<?php

//http://medoo.in/api/select

//http://localhost:10088/msv-dev/generareporte/modules/desktop/samsung/server/help.html#Error

require_once '../../../../server/os.php';

$os = new os();
if (!$os->session_exists()) {
    die('No existe sesión!');
}

include("mysql.class.php");

$databaseKiiconnect = new MySQL();

 
function selectKiiconnect()
{
    global $databaseKiiconnect;
    if ($databaseKiiconnect->Query("SELECT * FROM samsung_kiiconnect_categoria ORDER BY nombre")) {
        // echo $databaseKiiconnect->GetJSON();
        $data = $databaseKiiconnect->RecordsArray();
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
    global $databaseKiiconnect;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["nombre"] = MySQL::SQLValue($data->nombre);


    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseKiiconnect->UpdateRows("samsung_kiiconnect_categoria", $update, $where);


    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseKiiconnect->ErrorNumber()
    ));
}
function insertKiiconnect()
{
    global $databaseKiiconnect;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["nombre"] = MySQL::SQLValue($data->nombre);


    $databaseKiiconnect->InsertRow("samsung_kiiconnect_categoria", $update );
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0?"Parametro insertado exitosamente":$databaseKiiconnect->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseKiiconnect->GetLastInsertID(),
                "nombre"	=> $data->nombre
            )
        )
    ));


}

function deleteKiiconnect()
{
    global $databaseKiiconnect;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM samsung_kiiconnect_categoria WHERE id=$id";

    if ($databaseKiiconnect->Query( $sql)) {

    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg"	=> $databaseKiiconnect->ErrorNumber() == 0?"Nota de entrega eliminado exitosamente":$databaseKiiconnect->ErrorNumber()
    ));
}
 

 
switch ($_GET['operation']) {
     
    case 'select' :
        selectKiiconnect();
        break;
    case 'update' :
        updateKiiconnect();
        break;
    case 'insert' :
        insertKiiconnect();
        break;
    case 'delete' :
        deleteKiiconnect();
        break;
   
}