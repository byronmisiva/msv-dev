<?php

//http://medoo.in/api/select

//http://localhost:10088/msv-dev/generareporte/modules/desktop/kiiconnect/server/help.html#Error

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

    // borramos los registros duplicados
    /*if ($databaseKiiconnect->Query("SELECT * FROM kiiconnect_mensajes GROUP BY creado HAVING (COUNT(*) > 1);")) {
        $mensajes = $databaseKiiconnect->RecordsArray();
        if ($mensajes != false) {
            foreach ($mensajes as $mensaje) {
                $databaseKiiconnect->Query("SELECT * FROM kiiconnect_mensajes where creado = '{$mensaje['creado']}';");
                $mensajesrepetidos = $databaseKiiconnect->RecordsArray();
                $none = 1;
                foreach ($mensajesrepetidos as $index => $mensajesrepetido) {
                    if ($none != 1) {
                        $databaseKiiconnect->Query("DELETE FROM kiiconnect_mensajes where id  = '{$mensajesrepetido['id']}';");
                    }
                    $none++;
                }
            }

        }

    } else {
        echo "<p>Query Failed</p>";
    }*/

    if ($databaseKiiconnect->Query("SELECT * FROM kiiconnect_mensajes ORDER BY creado DESC")) {
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

    $update["body"] = MySQL::SQLValue($data->body);
    $update["title"] = MySQL::SQLValue($data->title);

    $update["l"] = MySQL::SQLValue($data->l);
    if ($data->tag == "") {
        $update["tag"] =  "''";
    } else {
        $update["tag"] = MySQL::SQLValue($data->tag, MySQL::SQLVALUE_TEXT);
    }
    $update["richpage"] = MySQL::SQLValue($data->richpage);
    $update["activo"] = MySQL::SQLValue($data->activo);

    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseKiiconnect->UpdateRows("kiiconnect_mensajes", $update, $where);

    $lastSql =  $databaseKiiconnect->GetLastSQL();

    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseKiiconnect->ErrorNumber()
    ));
}

function insertKiiconnect()
{
    global $databaseKiiconnect;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["body"] = MySQL::SQLValue($data->body);
    $update["title"] = MySQL::SQLValue($data->title);
//    $update["header"] = MySQL::SQLValue($data->header);
//    $update["p"] = MySQL::SQLValue($data->p);
    $update["l"] = MySQL::SQLValue($data->l);
    $update["tag"] = MySQL::SQLValue($data->tag);
//    $update["longuitud"] = MySQL::SQLValue($data->longuitud);
//    $update["latitud"] = MySQL::SQLValue($data->longuitud);
    $update["richpage"] = MySQL::SQLValue($data->richpage);
    $update["activo"] = MySQL::SQLValue($data->activo);

    $databaseKiiconnect->InsertRow("kiiconnect_mensajes", $update);
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseKiiconnect->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseKiiconnect->GetLastInsertID(),
                "body" => $data->body,
//                "header" => $data->header,
//                "p" => $data->p,
                "l" => $data->l,
                "tag" => $data->tag,
//                "longuitud" => $data->longuitud,
//                "latitud" => $data->latitud,
                "richpage" => $data->richpage,
                "activo" => $data->activo
            )
        )
    ));
}

function deleteKiiconnect()
{
    global $databaseKiiconnect;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM kiiconnect_mensajes WHERE id=$id";

    if ($databaseKiiconnect->Query($sql)) {

    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? "Nota de entrega eliminado exitosamente" : $databaseKiiconnect->ErrorNumber()
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