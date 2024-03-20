<?php

error_reporting(E_ALL);

date_default_timezone_set('Europe/London');

include "05featuredemo.inc.php";

/** PHPExcel_IOFactory */
require_once '../Classes/PHPExcel/IOFactory.php';



// Export to Excel5 (.xls)
echo date('H:i:s') , " Write to Excel5 format" , PHP_EOL;
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save(str_replace('.php', '.xls', __FILE__));
echo date('H:i:s') , " File written to " , str_replace('.php', '.xls', __FILE__) , PHP_EOL;



