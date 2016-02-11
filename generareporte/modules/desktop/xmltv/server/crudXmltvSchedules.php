<?php

//http://medoo.in/api/select

//http://localhost:10088/msv-dev/generareporte/modules/desktop/xmltv/server/help.html#Error

require_once '../../../../server/os.php';

$os = new os();
if (!$os->session_exists()) {
    die('No existe sesión!');
}

include("mysql.class.php");

$databaseXmltv = new MySQL();


function selectXmltv()
{
    global $databaseXmltv;
    if ($databaseXmltv->Query("SELECT
                                    id,
                                    description,
                                    date_star,
                                    date_end,
                                    time,
                                    duration,
                                    id_channel,
                                    id_programme,
                                    activo,
                                    creado,
                                    id_frecuencia,
                                    id_reemplazo
                                FROM xmltv_schedules")
    ) {
        // echo $databaseXmltv->GetJSON();
        $data = $databaseXmltv->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data)
    );
}

function updateXmltv()
{
    global $databaseXmltv;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["id_programme"] = MySQL::SQLValue($data->id_programme);
    $update["id_channel"] = MySQL::SQLValue($data->id_channel);
    $update["id_frecuencia"] = MySQL::SQLValue($data->id_frecuencia);
    $update["description"] = MySQL::SQLValue($data->description);
    $update["date_star"] = MySQL::SQLValue($data->date_star);
    $update["date_end"] = MySQL::SQLValue($data->date_end);
    $update["duration"] = MySQL::SQLValue($data->duration);
    $update["time"] = MySQL::SQLValue($data->time);
    $update["activo"] = MySQL::SQLValue($data->activo);

    $where["id"] = MySQL::SQLValue($data->id, "integer");
    $databaseXmltv->UpdateRows("xmltv_schedules", $update, $where);

    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseXmltv->ErrorNumber()
    ));
}

function insertXmltv()
{
    global $databaseXmltv;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["id_channel"] = MySQL::SQLValue($data->id_channel);
    $update["id_programme"] = MySQL::SQLValue($data->id_programme);
    $update["id_frecuencia"] = MySQL::SQLValue($data->id_frecuencia);
    $update["description"] = MySQL::SQLValue($data->description);
    $update["date_end"] = MySQL::SQLValue($data->date_end);
    $update["date_star"] = MySQL::SQLValue($data->date_star);
    $update["duration"] = MySQL::SQLValue($data->duration);
    $update["time"] = MySQL::SQLValue($data->time);
    $update["activo"] = MySQL::SQLValue($data->activo);

    $databaseXmltv->InsertRow("xmltv_schedules", $update);
    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseXmltv->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseXmltv->GetLastInsertID(),
                "id_channel" => $data->id_channel,
                "id_programme" => $data->id_programme,
                "id_frecuencia" => $data->id_frecuencia,
                "description" => $data->description,
                "date_end" => $data->date_end,
                "date_star" => $data->date_star,
                "time" => $data->time,
                "duration" => $data->duration,
                "activo" => $data->activo
            )
        )
    ));
}

function deleteXmltv()
{
    global $databaseXmltv;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM xmltv_schedules WHERE id=$id";

    if ($databaseXmltv->Query($sql)) {

    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? "Nota de entrega eliminado exitosamente" : $databaseXmltv->ErrorNumber()
    ));
}

switch ($_GET['operation']) {
    case 'select' :
        selectXmltv();
        break;
    case 'update' :
        updateXmltv();
        break;
    case 'insert' :
        insertXmltv();
        break;
    case 'delete' :
        deleteXmltv();
        break;
}