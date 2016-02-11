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

$URLBASE = $os->get_url_base();


function canales()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT id,
                                    display_name
                                FROM xmltv_channel WHERE activo = 1
                                ORDER BY `order` ASC
                                ")) {
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

function programas()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT id, title
                                FROM xmltv_programme WHERE activo = 1
                                ORDER BY id ASC")) {
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

function frecuencia()
{
    global $databaseXmltv;

    if ($databaseXmltv->Query("SELECT id,
                                    nombre
                                FROM xmltv_frecuencia WHERE activo = 1
                                ORDER BY nombre ASC
                                ")) {
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

function itemsTienda($path, $url)
{

    $tipos = array("gif", "jpg", "png", "JPG", "GIF", "PNG");
    listar_ficheros($tipos, $path, $url);
}

function listar_ficheros($tipos, $carpeta, $url)
{
    //Comprobamos que la carpeta existe
    global $URLBASE;
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
                        $imagen = ' <div style="overflow: hidden; width: 100%"><img src="' . $URLBASE . $url . $scanarray[$i] . '" width="35px"><span style="padding-top: 10px;position: absolute;font-weight: bold;"> -  ' .$scanarray[$i] .'</span></div>';
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
        selectXmltvJson();
        break;

    case 'canales' :
        canales();
        break;

    case 'programas' :
        programas();
        break;

    case 'frecuencia' :
        frecuencia();
        break;

    case 'itemsTienda' :
        itemsTienda($_GET['path'], $_GET['urlver']);
        break;
}