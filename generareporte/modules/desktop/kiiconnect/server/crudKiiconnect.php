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

function categorias()
{
    global $databaseSamsung;

    if ($databaseSamsung->Query("SELECT * FROM samsung_kiiconnect_categoria ORDER BY nombre")) {
        // echo $databaseSamsung->GetJSON();
        $data = $databaseSamsung->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data)
    );
}
function selectKiiconnect()
{
    global $databaseSamsung;

    if ($databaseSamsung->Query("SELECT * FROM samsung_kiiconnect_setting ORDER BY nombre")) {
        // echo $databaseSamsung->GetJSON();
        $data = $databaseSamsung->RecordsArray();
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

    $update["nombre"] = MySQL::SQLValue($data->nombre);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["link"] = MySQL::SQLValue($data->link);
    $update["orden"] = MySQL::SQLValue($data->orden);
    $update["id_categoria"] = MySQL::SQLValue($data->id_categoria);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseSamsung->UpdateRows("samsung_kiiconnect_setting", $update, $where);


    echo json_encode(array(
        "success" => $databaseSamsung->ErrorNumber() == 0,
        "msg" => $databaseSamsung->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseSamsung->ErrorNumber()
    ));
}
function insertKiiconnect()
{
    global $databaseSamsung;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["nombre"] = MySQL::SQLValue($data->nombre);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["link"] = MySQL::SQLValue($data->link);
    $update["orden"] = MySQL::SQLValue($data->orden);
    $update["id_categoria"] = MySQL::SQLValue($data->id_categoria);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $databaseSamsung->InsertRow("samsung_kiiconnect_setting", $update );
    echo json_encode(array(
        "success" => $databaseSamsung->ErrorNumber() == 0,
        "msg" => $databaseSamsung->ErrorNumber() == 0?"Parametro insertado exitosamente":$databaseSamsung->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseSamsung->GetLastInsertID(),
                "nombre"	=> $data->nombre,
                "descripcion"	=> $data->descripcion,
                "icono"	=> $data->icono,
                "link"	=> $data->link,
                "orden"	=> $data->orden,
                "id_categoria"	=> $data->id_categoria,
                "activo"	=> $data->activo
            )
        )
    ));


}

function deleteKiiconnect()
{
    global $databaseSamsung;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM samsung_kiiconnect_setting WHERE id=$id";

    if ($databaseSamsung->Query( $sql)) {

    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
        "success" => $databaseSamsung->ErrorNumber() == 0,
        "msg"	=> $databaseSamsung->ErrorNumber() == 0?"Nota de entrega eliminado exitosamente":$databaseSamsung->ErrorNumber()
    ));
}


switch ($_GET['operation']) {
    case 'selectjson' :
        selectKiiconnectJson();
        break;
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
    case 'categorias' :
        categorias();
        break;
}