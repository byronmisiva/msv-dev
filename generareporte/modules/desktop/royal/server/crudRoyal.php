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
function select()
{
    global $databaseSamsung;
    if ($databaseSamsung->Query("SELECT royal_ruleta_asigna_premios.id,
                                    royal_usuarios.nombre,
                                    royal_usuarios.apellido,
                                    royal_usuarios.mail,
                                    royal_usuarios.ciudad,
                                    royal_usuarios.telefono,
                                    royal_usuarios.cedula,
                                    royal_ruleta_premios.nombre AS premio,
                                   royal_ruleta_asigna_premios.fecha_ganador,
	                                royal_ruleta_asigna_premios.codigo
                                FROM royal_ruleta_asigna_premios INNER JOIN royal_usuarios ON royal_ruleta_asigna_premios.id_usuario = royal_usuarios.id
                                     INNER JOIN royal_ruleta_premios ON royal_ruleta_asigna_premios.id_premio = royal_ruleta_premios.id
                                WHERE royal_ruleta_asigna_premios.asignado = 1
                                ORDER BY royal_ruleta_asigna_premios.fecha_ganador")
    ) {
        $data = $databaseSamsung->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data)
    );
}

function selectIntentos()
{
    global $databaseSamsung;
    if ($databaseSamsung->Query("SELECT royal_usuario_serial.id,
                                royal_usuario_serial.codigopremio,
                                royal_usuario_serial.cedula,
                                (SELECT completo FROM royal_usuarios WHERE royal_usuarios.cedula = royal_usuario_serial.cedula limit 1 ) as completo,
                                (SELECT ciudad FROM royal_usuarios WHERE royal_usuarios.cedula = royal_usuario_serial.cedula limit 1 ) as ciudad,
                                royal_usuario_serial.cedula,
                                royal_usuario_serial.creado,
                                royal_usuario_serial.resultado,
                                royal_usuario_serial.ip
                            FROM royal_usuario_serial ORDER BY creado DESC")
    ) {
        $data = $databaseSamsung->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data)
    );
}

function selectParticipantes()
{
    global $databaseSamsung;
    if ($databaseSamsung->Query("SELECT royal_usuarios.id,
                                    royal_usuarios.nombre,
                                    royal_usuarios.apellido,
                                    royal_usuarios.mail,
                                    royal_usuarios.creado,
                                    royal_usuarios.ciudad,
                                    royal_usuarios.cedula,
                                    royal_usuarios.telefono
                                FROM royal_usuarios ORDER BY cedula")
    ) {
        $data = $databaseSamsung->RecordsArray();
    } else {
        echo "<p>Query Failed</p>";
    }
    echo json_encode(array(
            "success" => true,
            "data" => $data)
    );
}

switch ($_GET['operation']) {
    case 'select' :
        select();
        break;
    case 'selectIntentos' :
        selectIntentos();
        break;
    case 'selectParticipantes' :
        selectParticipantes();
        break;
}