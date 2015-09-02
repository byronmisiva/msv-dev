<?php
$conn = ftp_connect("200.24.202.133") or die("Could not connect");
ftp_login($conn,"web.developer","Compaq123rans1hyoc");

ftp_nb_put($conn,"indexas.php","index.php",FTP_BINARY);

ftp_close($conn);
?>