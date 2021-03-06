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
    if ($databaseXmltv->Query("SELECT xmltv_canal.id, xmltv_canal.nombre, xmltv_canal.tag, xmltv_canal.descripcion, xmltv_canal.icono, xmltv_canal.activo, xmltv_canal.creado, xmltv_canal.orden   FROM xmltv_canal ORDER BY orden ASC")) {
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

    $update["nombre"] = MySQL::SQLValue($data->nombre);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["tag"] = MySQL::SQLValue($data->tag);
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["orden"] = MySQL::SQLValue($data->orden);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $file = __DIR__ . '/../../../../' . $data->icono;

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

    $databaseXmltv->UpdateRows("xmltv_canal", $update, $where);

    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? " actualizado exitosamente" : $databaseXmltv->ErrorNumber()
    ));
}

function insertXmltv()
{
    global $databaseXmltv;
    $data = json_decode(stripslashes($_POST["data"]));
    $update["nombre"] = MySQL::SQLValue($data->nombre);
    $update["tag"] = MySQL::SQLValue($data->tag);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["orden"] = MySQL::SQLValue($data->orden);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $databaseXmltv->InsertRow("xmltv_canal", $update);
    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseXmltv->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseXmltv->GetLastInsertID(),
                "nombre" => $data->nombre,
                "tag" => $data->tag,
                "descripcion" => $data->descripcion,
                "icono" => $data->icono,
                "orden" => $data->orden,
                "activo" => $data->activo
            )
        )
    ));

    //inserto como blob la imagen
    $file = __DIR__ . '/../../../../' . $data->icono;
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
    $databaseXmltv->Query("update xmltv_canal set file='$imagen'  where `id`='$lastId'");

}

function deleteXmltv()
{
    global $databaseXmltv;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM xmltv_canal WHERE id=$id";

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