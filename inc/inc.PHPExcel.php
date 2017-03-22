<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/10/2017
 * Time: 1:51 PM
 */
include("lib/php/phpExcel-1.8/classes/phpexcel.php");
include("lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");

function loadPHPEXCELFile($path2xlsfile)
{
    $objPHPExcel = PHPExcel_IOFactory::load($path2xlsfile);
    return $objPHPExcel;
}

function PHPExcelcheckifSheetNameExists($excelOBJ, $sheet_name){
    $sheet_exists = false;
    $sheetNames = $excelOBJ->getSheetNames();
    foreach ($sheetNames as $value) {
        if($value==$sheet_name){
            $sheet_exists = true;
        }
    }
    return $sheet_exists;
}

function savePHPEXCELCSV($file_name,$path2xlsfile,$path2_destination)
{
    //print $path2xlsfile."<br>";
    if(file_exists($path2xlsfile)==false){
        return null;
    }
    $objPHPExcel  = loadPHPEXCELFile($path2xlsfile);
    $objWriter    = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $sheet_exists = PHPExcelcheckifSheetNameExists($objPHPExcel, "Locked Data");
    if($sheet_exists==true){
        PHPExcelRemoveSheetByName($objPHPExcel,"Locked Data");
    }
    $index = 0;
    foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

        $objPHPExcel->setActiveSheetIndex($index);

        // write out each worksheet to it's name with CSV extension
        $outFile = str_replace(array("-"," "), "_", $worksheet->getTitle()) ."$file_name.csv";
        $objWriter->setDelimiter(',');
        $objWriter->setSheetIndex($index);
        $objWriter->save($path2_destination."/".$outFile);

        $index++;
    }
    $objPHPExcel = null;
    return $outFile;
}
function savePHPEXCELCSV1WorkSheetByIndex($file_name,$path2xlsfile,$path2_destination, $sheet_index)
{
    //print $path2_destination."<br>";

    if(file_exists($path2xlsfile)==false){
        return null;
    }
    $objPHPExcel  = loadPHPEXCELFile($path2xlsfile);
    $objWriter    = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objPHPExcel->setActiveSheetIndex($sheet_index);

    // write out each worksheet to it's name with CSV extension
    $objWriter->setDelimiter(',');
    $objWriter->setSheetIndex($sheet_index);

    // write out each worksheet to it's name with CSV extension
    $outFile = "$file_name.csv";
    $objWriter->save($path2_destination."/".$outFile);
    $objPHPExcel = null;
    return $outFile;
}

function PHPExcelRemoveSheetByName($objWorkSheet,$sheet_name){
    $objWorkSheet->setActiveSheetIndexByName($sheet_name);
    $sheetIndex = $objWorkSheet->getActiveSheetIndex();
    $objWorkSheet->removeSheetByIndex($sheetIndex);
}
