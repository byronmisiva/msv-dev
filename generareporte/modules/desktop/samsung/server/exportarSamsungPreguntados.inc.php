<?php
/**
 * MISIVA
 *
 * Copyright (C)  2015
 *
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.0.0, 2015-07-08
 */

if ($databaseSamsung->Query("SELECT samsung_usuarios.nombre,
	samsung_usuarios.apellido,
	samsung_usuarios.mail,
	samsung_usuarios.fbid,
	samsung_usuarios.cedula,
	samsung_usuarios.telefono,
	samsung_usuarios.ciudad,
	samsung_registro_preguntados.actividad,
	samsung_registro_preguntados.invitartw,
	samsung_registro_preguntados.invitarfb,
	samsung_registro_preguntados.nivel,
	samsung_registro_preguntados.tiempo,
	samsung_registro_preguntados.id
FROM samsung_registro_preguntados INNER JOIN samsung_usuarios ON samsung_registro_preguntados.id_user = samsung_usuarios.id
ORDER BY samsung_usuarios.apellido ASC, samsung_usuarios.nombre ASC")) {
    $result =  $databaseSamsung->RecordsArray();
} else {
    echo "<p>Query Failed</p>";
}

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

$filaInicio = 2;

$objPHPExcel->getActiveSheet()->setCellValue('A1', 'id');
$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Nombre');
$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Apellido');
$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Mail');
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Cedula');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Telefono');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Ciudad');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Intentos');
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Twiter');
$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Facebook');
$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Nivel');
$objPHPExcel->getActiveSheet()->setCellValue('L1', 'Tiempo');
foreach($result as &$rowdetalle ) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $filaInicio, $rowdetalle['id']);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $filaInicio, $rowdetalle['nombre']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $filaInicio, $rowdetalle['apellido']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $filaInicio, $rowdetalle['mail']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $filaInicio, $rowdetalle['cedula']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $filaInicio, $rowdetalle['telefono']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $filaInicio, $rowdetalle['ciudad']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $filaInicio, $rowdetalle['actividad']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $filaInicio, $rowdetalle['invitartw']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $filaInicio, $rowdetalle['invitarfb']);
    $objPHPExcel->getActiveSheet()->setCellValue('K' . $filaInicio, $rowdetalle['nivel']);
    $objPHPExcel->getActiveSheet()->setCellValue('L' . $filaInicio, $rowdetalle['tiempo']);
    $filaInicio++;
}


// Create new PHPExcel object

// Set document properties
//echo date('H:i:s') , " Set document properties" , PHP_EOL;
$objPHPExcel->getProperties()->setCreator("Byron Herrera")
    ->setLastModifiedBy("Byron Herrera")
    ->setTitle("Samsung Preguntados 2015")
    ->setSubject("")
    ->setDescription("Samsung Preguntados 2015")
    ->setKeywords("Samsung Preguntados 2015 openxml php")
    ->setCategory("Archivo");


//$objPHPExcel->getActiveSheet()->setCellValue('A7', 'Cantidad');
//$objPHPExcel->getActiveSheet()->setCellValue('B7', 'Detalle');
//$objPHPExcel->getActiveSheet()->setCellValue('C7', 'Valor Unitario');
//$objPHPExcel->getActiveSheet()->setCellValue('D7', 'Valor Total');


$styleThinBlackBorderOutline = array(
    'borders' => array(
        'outline' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('argb' => 'FF000000'),
        ),
    ),
);


$objPHPExcel->getActiveSheet()->getStyle('A1:L300')->applyFromArray(
    array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        )
    )
);

//$objPHPExcel->getActiveSheet()->getStyle('A8:D36')->applyFromArray($styleThinBlackBorderOutline);

/*

$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('A8:D8')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('A7:D7')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('A37:D37')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

$objPHPExcel->getActiveSheet()->getStyle('A7:A36')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('B7:B36')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('C7:C36')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
*/
// Set page orientation and size
//echo date('H:i:s') , " Set page orientation and size" , PHP_EOL;
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);


$objPHPExcel->getActiveSheet()->getStyle('A1:L3000')->getFont()->setSize(12);
//$objPHPExcel->getActiveSheet()->getStyle('A3:D40')->getFont()->setSize(10);


$pageMargins = $objPHPExcel->getActiveSheet()->getPageMargins();


// margin is set in inches (0.5cm)
$margin = 0.5 / 2.54;

$pageMargins->setTop($margin);
$pageMargins->setBottom($margin);
$pageMargins->setLeft($margin);
$pageMargins->setRight($margin);


$objPHPExcel->getActiveSheet()->setShowGridLines(false);

//echo date('H:i:s') , " Set orientation to landscape" , PHP_EOL;
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
