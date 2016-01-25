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
    if ($databaseXmltv->Query("SELECT xmltv_programa.id,
                                    xmltv_programa.titulo,
                                    xmltv_programa.fecha_fin,
                                    xmltv_programa.fecha_inicio,
                                    xmltv_programa.duracion,
                                    xmltv_programa.descripcion,
                                    xmltv_programa.activo,
                                    xmltv_programa.imagen,
                                    xmltv_programa.tipo,
                                    xmltv_programa.id_canal
                                FROM xmltv_programa ORDER BY titulo")
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


    $update["titulo"] = MySQL::SQLValue($data->titulo);
    $update["fecha_fin"] = MySQL::SQLValue($data->fecha_fin);
    $update["fecha_inicio"] = MySQL::SQLValue($data->fecha_inicio);
    $update["duracion"] = MySQL::SQLValue($data->duracion);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["activo"] = MySQL::SQLValue($data->activo);
    $update["imagen"] = MySQL::SQLValue($data->imagen);
    $update["tipo"] = MySQL::SQLValue($data->tipo);
    $update["id_canal"] = MySQL::SQLValue($data->id_canal);


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
    $databaseXmltv->UpdateRows("xmltv_programa", $update, $where);

    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? " actualizado exitosamente" . $file : $databaseXmltv->ErrorNumber()
    ));
}

function insertXmltv()
{
    global $databaseXmltv;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["titulo"] = MySQL::SQLValue($data->titulo);
    $update["fecha_fin"] = MySQL::SQLValue($data->fecha_fin);
    $update["fecha_inicio"] = MySQL::SQLValue($data->fecha_inicio);
    $update["duracion"] = MySQL::SQLValue($data->duracion);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["activo"] = MySQL::SQLValue($data->activo);
    $update["imagen"] = MySQL::SQLValue($data->imagen);
    $update["tipo"] = MySQL::SQLValue($data->tipo);
    $update["id_canal"] = MySQL::SQLValue($data->id_canal);

    $databaseXmltv->InsertRow("xmltv_programa", $update);
    echo json_encode(array(
        "success" => $databaseXmltv->ErrorNumber() == 0,
        "msg" => $databaseXmltv->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseXmltv->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseXmltv->GetLastInsertID(),
                "titulo" => $data->titulo,
                "fecha_fin" => $data->fecha_fin,
                "fecha_inicio" => $data->fecha_inicio,
                "duracion" => $data->duracion,
                "descripcion" => $data->descripcion,
                "activo" => $data->activo,
                "imagen" => $data->imagen,
                "tipo" => $data->tipo,
                "id_canal" => $data->id_canal
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
    $databaseXmltv->Query("update xmltv_programa set file= '$imagen'   where `id`='$lastId'");
}

function deleteXmltv()
{
    global $databaseXmltv;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM xmltv_programa WHERE id=$id";

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