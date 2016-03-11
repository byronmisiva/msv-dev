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
    if ($databaseKiiconnect->Query("SELECT kiiconnect_categoria.id,
                                    kiiconnect_categoria.nombre,
                                    kiiconnect_categoria.icono,
                                    kiiconnect_categoria.iconodev,
                                    kiiconnect_categoria.creado,
                                    kiiconnect_categoria.activo,
                                    kiiconnect_categoria.orden2 FROM kiiconnect_categoria ORDER BY nombre")) {
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
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["iconodev"] = MySQL::SQLValue($data->iconodev);
    $update["orden2"] = MySQL::SQLValue($data->orden2);
    $update["activo"] = MySQL::SQLValue($data->activo);


    // creamos en variable tag copia de la imagen
    $file = __DIR__ . '/../../../../' . $data->icono;

    if($fp = fopen($file,"rb", 0))
    {
        $picture = fread($fp,filesize($file));
        fclose($fp);
        // base64 encode the binary data, then break it
        // into chunks according to RFC 2045 semantics
        //$base64 =  $picture;
        //$base64 = chunk_split(base64_encode($picture));
        $base64 = base64_encode($picture);
        $tag = 'data:image/png;base64,' . $base64;
        //$tag = $base64;
        //$tag = '' . $base64;
    }
    $update["filecategoria2"] = MySQL::SQLValue($tag);
    // fin creamos en variable tag copia de la imagen

    // creamos en variable tag copia de la imagen
    $filedev = __DIR__ . '/../../../../' . $data->iconodev;

    if($fpdev = fopen($filedev,"rb", 0))
    {
        $picturedev = fread($fpdev,filesize($filedev));
        fclose($fpdev);
        $base64dev = base64_encode($picturedev);
        $tagdev = 'data:image/png;base64,' . $base64dev;
    }
    $update["filecategoriadev"] = MySQL::SQLValue($tagdev);
    // fin creamos en variable tag copia de la imagen

    // actualizamos la base de datos
    $where["id"] = MySQL::SQLValue($data->id, "integer");
    $databaseKiiconnect->UpdateRows("kiiconnect_categoria", $update, $where);

    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? " actualizado exitosamente" .$file : $databaseKiiconnect->ErrorNumber()
    ));


}

function insertKiiconnect()
{
    global $databaseKiiconnect;

    $data = json_decode(stripslashes($_POST["data"]));

    $update["nombre"] = MySQL::SQLValue($data->nombre);

    $databaseKiiconnect->InsertRow("kiiconnect_categoria", $update);
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseKiiconnect->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseKiiconnect->GetLastInsertID(),
                "nombre" => $data->nombre,
                "icono" => $data->icono,
                "iconodev" => $data->iconodev,
                "activo" => $data->iconodev,
                "orden2" => $data->orden2
            )
        )
    ));

    $lastId =   $databaseKiiconnect->GetLastInsertID();
    //inserto como blob la imagen
    $file = __DIR__ . '/../../../../' . $data->icono;
    if($fp = fopen($file,"rb", 0))
    {
        $picture = fread($fp,filesize($file));
        fclose($fp);
        // base64 encode the binary data, then break it
        // into chunks according to RFC 2045 semantics
        //$base64 = chunk_split(base64_encode($picture));
        $base64 = base64_encode($picture);
        $tag = 'data:image/png;base64,' . $base64;
    }

    //$databaseKiiconnect->Query("update kiiconnect_categoria set filecategoria= '$tag'   where `id`='$lastId'");
    $databaseKiiconnect->Query("update kiiconnect_categoria set filecategoria2= '$tag'   where `id`='$lastId'");

    //inserto como blob la imagen dev
    $filedev = __DIR__ . '/../../../../' . $data->iconodev;
    if($fpdev = fopen($filedev,"rb", 0))
    {
        $picturedev = fread($fpdev,filesize($filedev));
        fclose($fpdev);
        $base64dev = base64_encode($picturedev);
        $tagdev = 'data:image/png;base64,' . $base64dev;
    }
    $databaseKiiconnect->Query("update kiiconnect_categoria set filecategoriadev= '$tagdev'   where `id`='$lastId'");

}

function deleteKiiconnect()
{
    global $databaseKiiconnect;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM kiiconnect_categoria WHERE id=$id";

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