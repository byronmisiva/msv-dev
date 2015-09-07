<?php
if(isset($_FILES)){
    $temp_file_name = $_FILES['photo-path']['tmp_name'];
    $original_file_name = $_FILES['photo-path']['name'];

    $uploaddir =   __DIR__  . "/../../../../imagenes/kiiconnect/";
    $uploadfile = $uploaddir . basename($_FILES['photo-path']['name']);
    if (move_uploaded_file($temp_file_name, $uploadfile)) {
        echo '{success:true, file:'.json_encode($_FILES['photo-path']['name']).'}';
    } else {
        echo '{success:false, file:'.json_encode($_FILES['photo-path']['name']).'}';
    }
}


 