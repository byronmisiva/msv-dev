<?php
/**
 * Created by PhpStorm.
 * User: byronherrera
 * Date: 7/9/15
 * Time: 15:33
 */

if (array_key_exists('imageData', $_POST)) {
    $imgData = base64_decode($_REQUEST['imageData']);
    $nombreArchivoSubido = $_REQUEST['nombreArchivoSubido'];

    $nombreOriginal = $nombreArchivoSubido;

    $nombreArchivoSubido = str_replace(".mp4", ".png", $nombreArchivoSubido);
    $nombreArchivoSubido = str_replace(".mpg", ".png", $nombreArchivoSubido);
    $nombreArchivoSubido = str_replace(".mov", ".png", $nombreArchivoSubido);
    $nombreArchivoSubido = str_replace(".MOV", ".png", $nombreArchivoSubido);
    $nombreArchivoSubido = str_replace(".MP4", ".png", $nombreArchivoSubido);
    $nombreArchivoSubido = str_replace(".MPG", ".png", $nombreArchivoSubido);

    $nombreArchivoSubido = str_replace(".3gp", ".png", $nombreArchivoSubido);
    $nombreArchivoSubido = str_replace(".3GP", ".png", $nombreArchivoSubido);


    // Path where the image is going to be saved
    // $filePath =  $_SERVER['DOCUMENT_ROOT'] . '/appss/videos/' . $nombreArchivoSubido;
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/videos/' . $nombreArchivoSubido;

    // Delete previously uploaded image
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Write $imgData into the image file
    $file = fopen($filePath, 'w');
    fwrite($file, $imgData);
    fclose($file);
    echo '{"video":"' . $nombreOriginal . '", "imagen":"' . $nombreArchivoSubido . '"}';
    sleep(2);
    $imageObject = imagecreatefrompng($filePath);
    imagegif($imageObject, str_replace(".png", ".gif", $filePath));

    //validar que la imagen se genero correctamente


    $numeroazar = rand(1, 3);
    if ($this->esImagen($filePath)) {
        if (filesize(str_replace(".png", ".gif", $filePath)) < 500) {
            $fichero = $_SERVER['DOCUMENT_ROOT'] . '/videos/galeria_' . $numeroazar . '.gif';
            $nuevo_fichero = str_replace(".png", ".gif", $filePath);
            copy($fichero, $nuevo_fichero);

            $fichero = $_SERVER['DOCUMENT_ROOT'] . '/videos/galeria_' . $numeroazar . '.png';
            $nuevo_fichero = $filePath;
            copy($fichero, $nuevo_fichero);
        }
        //echo "es imagen ";
    } else {
        $fichero = $_SERVER['DOCUMENT_ROOT'] . '/videos/galeria_' . $numeroazar . '.gif';
        $nuevo_fichero = str_replace(".png", ".gif", $filePath);
        copy($fichero, $nuevo_fichero);

        $fichero = $_SERVER['DOCUMENT_ROOT'] . '/videos/galeria_' . $numeroazar . '.png';
        $nuevo_fichero = $filePath;
        copy($fichero, $nuevo_fichero);
    }
}


function esImagen($path)
{
    $imageSizeArray = getimagesize($path);
    $imageTypeArray = $imageSizeArray[2];
    return (bool)(in_array($imageTypeArray, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP)));
}