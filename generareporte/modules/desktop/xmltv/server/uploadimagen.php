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
    echo '{"imagen":"' . $nombreArchivoSubido . '"}';

}


function esImagen($path)
{
    $imageSizeArray = getimagesize($path);
    $imageTypeArray = $imageSizeArray[2];
    return (bool)(in_array($imageTypeArray, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP)));
}