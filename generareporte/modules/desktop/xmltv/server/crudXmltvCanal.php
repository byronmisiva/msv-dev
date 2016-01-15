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

function categorias()
{
    global $databaseKiiconnect;

    if ($databaseKiiconnect->Query("SELECT kiiconnect_categoria.id,
                                    kiiconnect_categoria.nombre,
                                    kiiconnect_categoria.icono,
                                    kiiconnect_categoria.creado,
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

function selectKiiconnect()
{
    global $databaseKiiconnect;

    if ($databaseKiiconnect->Query("SELECT kiiconnect_setting.id, kiiconnect_setting.nombre, kiiconnect_setting.tag, kiiconnect_setting.descripcion, kiiconnect_setting.slogan, kiiconnect_setting.icono, kiiconnect_setting.link, kiiconnect_setting.activo, kiiconnect_setting.creado, kiiconnect_setting.orden, kiiconnect_setting.id_categoria FROM kiiconnect_setting ORDER BY nombre ASC")) {
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
    $update["tag"] = MySQL::SQLValue($data->tag);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["link"] = MySQL::SQLValue($data->link);
    $update["orden"] = MySQL::SQLValue($data->orden);
    $update["id_categoria"] = MySQL::SQLValue($data->id_categoria);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $file = __DIR__ . '/../../../../' . $data->icono;

    if($fp = fopen($file,"rb", 0))
    {
        $picture = fread($fp,filesize($file));
        fclose($fp);
        // base64 encode the binary data, then break it
        // into chunks according to RFC 2045 semantics
        $base64 = chunk_split(base64_encode($picture));
        $tag = 'data:image/gif;base64,' . $base64;
    }
//    $databaseKiiconnect->Query("update kiiconnect_setting set file= '$tag'   where `id`='$data->id'");

    $update["file"] = MySQL::SQLValue($tag);
    $where["id"] = MySQL::SQLValue($data->id, "integer");

    $databaseKiiconnect->UpdateRows("kiiconnect_setting", $update, $where);

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
    $update["tag"] = MySQL::SQLValue($data->tag);
    $update["descripcion"] = MySQL::SQLValue($data->descripcion);
    $update["icono"] = MySQL::SQLValue($data->icono);
    $update["link"] = MySQL::SQLValue($data->link);
    $update["orden"] = MySQL::SQLValue($data->orden);
    $update["id_categoria"] = MySQL::SQLValue($data->id_categoria);
    $update["activo"] = MySQL::SQLValue($data->activo, MySQL::SQLVALUE_NUMBER);

    $databaseKiiconnect->InsertRow("kiiconnect_setting", $update);
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? "Parametro insertado exitosamente" : $databaseKiiconnect->ErrorNumber(),
        "data" => array(
            array(
                "id" => $databaseKiiconnect->GetLastInsertID(),
                "nombre" => $data->nombre,
                "tag" => $data->tag,
                "descripcion" => $data->descripcion,
                "icono" => $data->icono,
                "link" => $data->link,
                "orden" => $data->orden,
                "id_categoria" => $data->id_categoria,
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
        $tag = 'data:image/png;base64,' . $base64;
    }
    $lastId =   $databaseKiiconnect->GetLastInsertID();
    $databaseKiiconnect->Query("update kiiconnect_setting set file='$tag'  where `id`='$lastId'");

}

function deleteKiiconnect()
{
    global $databaseKiiconnect;
    $id = json_decode(stripslashes($_POST["data"]));
    $sql = "DELETE FROM kiiconnect_setting WHERE id=$id";

    if ($databaseKiiconnect->Query($sql)) {

    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
        "success" => $databaseKiiconnect->ErrorNumber() == 0,
        "msg" => $databaseKiiconnect->ErrorNumber() == 0 ? "Nota de entrega eliminado exitosamente" : $databaseKiiconnect->ErrorNumber()
    ));
}

function itemsTienda($path, $url)
{

    $tipos = array("gif", "jpg", "png", "JPG", "GIF", "PNG");
    listar_ficheros($tipos, $path, $url);
}

function listar_ficheros($tipos, $carpeta, $url)
{
    //Comprobamos que la carpeta existe

    if (is_dir($carpeta)) {
        //Escaneamos la carpeta usando scandir
        $scanarray = scandir($carpeta);

        for ($i = 0; $i < count($scanarray); $i++) {
            //Eliminamos  "." and ".." del listado de ficheros
            if ($scanarray[$i] != "." && $scanarray[$i] != "..") {
                //No mostramos los subdirectorios
                if (is_file($carpeta . "/" . $scanarray[$i])) {
                    //Verificamos que la extension se encuentre en $tipos
                    $thepath = pathinfo($carpeta . "/" . $scanarray[$i]);
                    if (in_array($thepath['extension'], $tipos)) {
                        //$imagen =  $scanarray[$i] . ' <div style="overflow: hidden; width: 120px"><img src="http://wwww.misiva.com.ec/generareporte/' . $url . $scanarray[$i] .  '" width="30px"></div>';
                        $imagen = ' <div style="overflow: hidden; width: 100%"><img src="http://wwww.misiva.com.ec/generareporte/' . $url . $scanarray[$i] . '" width="35px"><span style="padding-top: 10px;position: absolute;font-weight: bold;"> -  ' .$scanarray[$i] .'</span></div>';
                        $data[] = array("id" => $url . $scanarray[$i], "nombre" => $imagen);
                    }
                }
            }
        }
        echo json_encode(array(
                "success" => true,
                "data" => $data)
        );
    } else {
        echo "La carpeta no existe";
    }
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

    case 'itemsTienda' :
        itemsTienda($_GET['path'], $_GET['urlver']);
        break;
}