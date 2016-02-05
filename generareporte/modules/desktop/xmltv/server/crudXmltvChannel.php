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
    if ($databaseXmltv->Query("SELECT xmltv_channel.id,
                                    xmltv_channel.id_code,
                                    xmltv_channel.display_name,
                                    xmltv_channel.description,
                                    xmltv_channel.tag,
                                    xmltv_channel.`order`,
                                    xmltv_channel.icon,
                                    xmltv_channel.activo,
                                    xmltv_channel.creado
                                FROM xmltv_channel
                                ORDER BY xmltv_channel.`order` ASC")) {
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

    $update["id_code"] = MySQL::SQLValue($data->id_code);
    $update["display_name"] = MySQL::SQLValue($data->display_name);
    $update["description"] = MySQL::SQLValue($data->description);
    $update["tag"] = MySQL::SQLValue($data->tag);
    $update["icon"] = MySQL::SQLValue($data->icon);
    $update["order"] = MySQL::SQLValue($data->order);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $file = __DIR__ . '/../../../../' . $data->icon;

    if($fp = fopen($file,"rb", 0))
    {
        $picture = fread($fp,filesize($file));
        fclose($fp);
        // base64 encode the binary data, then break it
        // into chunks according to RFC 2045 semantics
        $base64 = chunk_split(base64_encode($picture));
        $imagen = 'data:image/gif;base64,' . $base64;
    } else {
        $imagen = "";
    }

    $update["file"] = MySQL::SQLValue($imagen);
    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseXmltv->UpdateRows("xmltv_channel", $update, $where);

    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseXmltv->ErrorNumber()
    ));
}

function insertXmltv()
{
    global $databaseXmltv;
    $data = json_decode(stripslashes($_POST["data"]));
    $update["id_code"] = MySQL::SQLValue($data->id_code);
    $update["display_name"] = MySQL::SQLValue($data->display_name);
    $update["tag"] = MySQL::SQLValue($data->tag);
    $update["description"] = MySQL::SQLValue($data->description);
    $update["icon"] = MySQL::SQLValue($data->icon);
    $update["order"] = MySQL::SQLValue($data->order);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $databaseXmltv->InsertRow("xmltv_channel", $update);
    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseXmltv->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseXmltv->GetLastInsertID(),
                "id_code" => $data->id_code,
                "display_name" => $data->display_name,
                "tag" => $data->tag,
                "description" => $data->description,
                "icon" => $data->icon,
                "order" => $data->order,
                "activo" => $data->activo
            )
        )
    ));

    //inserto como blob la imagen
    $file = __DIR__ . '/../../../../' . $data->icon;
    if($fp = fopen($file,"rb", 0))
    {
        $picture = fread($fp,filesize($file));
        fclose($fp);
        // base64 encode the binary data, then break it
        // into chunks according to RFC 2045 semantics
        $base64 = chunk_split(base64_encode($picture));
        $imagen = 'data:image/png;base64,' . $base64;
    } else {
        $imagen = "";
    }
    $lastId =   $databaseXmltv->GetLastInsertID();
    $databaseXmltv->Query("update xmltv_channel set file='$imagen'  where `id`='$lastId'");

}

function deleteXmltv()
{
    global $databaseXmltv;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM xmltv_channel WHERE id=$id";

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