<?php
include("mysql.class.php");

$databaseXmltv = new MySQL();
if ($databaseXmltv->Query("SELECT xmltv_canal.id,
                                    xmltv_canal.nombre as title
                                     FROM xmltv_canal WHERE activo = 1 ORDER BY orden")) {
        $canales =  $databaseXmltv->GetJSON();
        $data = $databaseXmltv->RecordsArray();
    } else {
        $canales = "''";
    }
     ;

//////////////////////////

?>
 {
"calendars": <?php echo $canales;?>
}
