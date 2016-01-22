<?php
//////////////////////////

include("mysql.class.php");

$databaseXmltv = new MySQL();
if ($databaseXmltv->Query("SELECT id,
                                    titulo as title,
                                    id_canal
                                    FROM xmltv_programa WHERE activo = 1 ")
) {
    $canales = $databaseXmltv->GetJSON();
    $data = $databaseXmltv->RecordsArray();
} else {
    $canales = "''";
};

?>
{
"programas":<?php echo $canales; ?>
}