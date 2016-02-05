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
                                    xmltv_programme.id,
                                    xmltv_programme.title,
                                    xmltv_programme.date_end,
                                    xmltv_programme.date_start,
                                    xmltv_programme.duration,
                                    xmltv_programme.description,
                                    xmltv_programme.category,
                                    xmltv_programme.activo,
                                    xmltv_programme.imagen,
                                    xmltv_programme.id_channel,
                                    xmltv_programme.id_frecuencia
                                FROM xmltv_programme" )
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

    $update["title"] = MySQL::SQLValue($data->title);
    $update["date_start"] = MySQL::SQLValue($data->date_start);
    $update["date_end"] = MySQL::SQLValue($data->date_end);
    $update["duration"] = MySQL::SQLValue($data->duration);
    $update["description"] = MySQL::SQLValue($data->description);
    $update["activo"] = MySQL::SQLValue($data->activo);
    $update["imagen"] = MySQL::SQLValue($data->imagen);
    $update["category"] = MySQL::SQLValue($data->category);
    $update["id_channel"] = MySQL::SQLValue($data->id_channel);
    $update["id_frecuencia"] = MySQL::SQLValue($data->id_frecuencia);


    // creamos en variable tag copia de la imagen
    $file = __DIR__ . '/../../../../' . $data->imagen;

    if ($fp = fopen($file, "rb", 0)) {
        $picture = fread($fp, filesize($file));
        fclose($fp);

        $base64 = base64_encode($picture);
        $imagen = 'data:image/png;base64,' . $base64;

    }  else {
        $imagen = "";
    }
    $update["file"] = MySQL::SQLValue($imagen);
    // fin creamos en variable tag copia de la imagen

    // actualizamos la base de datos
    $where["id"] = MySQL::SQLValue($data->id, "integer");
    $databaseXmltv->UpdateRows("xmltv_programme", $update, $where);

    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? " actualizado exitosamente" . $file : $databaseXmltv->ErrorNumber()
    ));
}

function insertXmltv()
{
    global $databaseXmltv;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["title"] = MySQL::SQLValue($data->title);
    $update["date_end"] = MySQL::SQLValue($data->date_end);
    $update["date_start"] = MySQL::SQLValue($data->date_start);
    $update["duration"] = MySQL::SQLValue($data->duration);
    $update["description"] = MySQL::SQLValue($data->description);
    $update["activo"] = MySQL::SQLValue($data->activo);
    $update["imagen"] = MySQL::SQLValue($data->imagen);
    $update["category"] = MySQL::SQLValue($data->category);
    $update["id_channel"] = MySQL::SQLValue($data->id_channel);
    $update["id_frecuencia"] = MySQL::SQLValue($data->id_frecuencia);

    $databaseXmltv->InsertRow("xmltv_programme", $update);
    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseXmltv->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseXmltv->GetLastInsertID(),
                "title" => $data->title,
                "date_end" => $data->date_end,
                "date_start" => $data->date_start,
                "duration" => $data->duration,
                "description" => $data->description,
                "activo" => $data->activo,
                "imagen" => $data->imagen,
                "category" => $data->category,
                "id_channel" => $data->id_channel,
                "id_frecuencia" => $data->id_frecuencia
            )
        )
    ));

    //inserto como blob la imagen
    $file = __DIR__ . '/../../../../' . $data->imagen;
    if ($fp = fopen($file, "rb", 0)) {
        $picture = fread($fp, filesize($file));
        fclose($fp);
        // base64 encode the binary data, then break it
        // into chunks according to RFC 2045 semantics
        //$base64 = chunk_split(base64_encode($picture));
        $base64 = base64_encode($picture);
        $imagen = 'data:image/png;base64,' . $base64;
    }else {
        $imagen = "";
    }
    $lastId = $databaseXmltv->GetLastInsertID();
    $databaseXmltv->Query("update xmltv_programme set file= '$imagen'   where `id`='$lastId'");
}

function deleteXmltv()
{
    global $databaseXmltv;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM xmltv_programme WHERE id=$id";

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