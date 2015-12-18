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
                                ORDER BY royal_ruleta_asigna_premios.fecha_ganador")) {
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
$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Cédula');
$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Telefono');
$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Ciudad');
$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Premio');
$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Fecha');
$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Lote Producto');
foreach($result as &$rowdetalle ) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $filaInicio, $filaInicio-1);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $filaInicio, $rowdetalle['nombre']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $filaInicio, $rowdetalle['apellido']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $filaInicio, $rowdetalle['mail']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $filaInicio, '"' .$rowdetalle['cedula']. '" ');
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $filaInicio, '"' .$rowdetalle['telefono']. '" ');
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $filaInicio, $rowdetalle['ciudad']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $filaInicio, $rowdetalle['premio']);
    $objPHPExcel->getActiveSheet()->setCellValue('I' . $filaInicio, $rowdetalle['fecha_ganador']);
    $objPHPExcel->getActiveSheet()->setCellValue('J' . $filaInicio, '"' .$rowdetalle['codigo']. '" ');
    $filaInicio++;
}


// Create new PHPExcel object

// Set document properties
//echo date('H:i:s') , " Set document properties" , PHP_EOL;
$objPHPExcel->getProperties()->setCreator("Byron Herrera")
    ->setLastModifiedBy("Byron Herrera")
    ->setTitle("Royal - GanaConRoyal 2015")
    ->setSubject("")
    ->setDescription("Royal - GanaConRoyal 2015")
    ->setKeywords("Royal - GanaConRoyal 2015 openxml php")
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
