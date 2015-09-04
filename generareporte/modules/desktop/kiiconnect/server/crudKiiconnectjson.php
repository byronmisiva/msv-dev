<?php
include("mysql.class.php");
$databaseSamsung = new MySQL();
global $databaseSamsung;
if ($databaseSamsung->Query("SELECT
                                    samsung_kiiconnect_setting.nombre,
                                    samsung_kiiconnect_setting.descripcion,
                                    samsung_kiiconnect_setting.icono,
                                    samsung_kiiconnect_setting.link,
                                    samsung_kiiconnect_categoria.nombre AS categoria,
                                    samsung_kiiconnect_categoria.id AS id_categoria
                                FROM
                                    samsung_kiiconnect_setting
                                INNER JOIN samsung_kiiconnect_categoria ON samsung_kiiconnect_setting.id_categoria = samsung_kiiconnect_categoria.id
                                WHERE
                                    activo = 1
                                ORDER BY
                                    orden ASC")
) {
    echo $databaseSamsung->GetJSON();

} else {
    echo "<p>Query Failed</p>";
}