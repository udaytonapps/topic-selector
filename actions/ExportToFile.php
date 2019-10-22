<?php
require_once "../../config.php";
require_once "../util/PHPExcel.php";
require_once "../dao/TS_DAO.php";

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$TS_DAO = new TS_DAO($PDOX, $p);

if ( $USER->instructor ) {

    $topics = $TS_DAO->getTopics($LINK->id);


    $headCount1 = 0;
    $headCount2 = -1;
    $isDouble = false;
    $exportFile = new PHPExcel();
    $letters = range('A', 'Z');

    //Loop to merge header cells and set values for all cells
    foreach($topics as $top) {
        $rowCount = 2;
        if($headCount1 >= 26) {
            $isDouble = true;
            $headCount1 = 0;
            $headCount2 += 1;
        }
        if($isDouble == false) {
            //Merge header cells that contain topic name (for first 26 cells) and set topic name
            $exportFile->setActiveSheetIndex(0)->mergeCells($letters[$headCount1] . '1' . ':' . $letters[$headCount1 + 1] . '1');
            $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount1] . '1', $top['topic_text']);

            $selectionST  = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
            $selectionST->execute(array(":topicId" => $top['topic_id']));
            $selection = $selectionST->fetchAll(PDO::FETCH_ASSOC);

            //Set Student and Date headers
            $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount1] . $rowCount, 'Student');
            $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount1 + 1] . $rowCount, 'Date Assigned');

            foreach($selection as $sel) {
                $rowCount ++;
                $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount1] . $rowCount, $sel['user_first_name'] . ' ' . $sel['user_last_name']);
                $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount1 + 1] . $rowCount, $sel['date_selected']);
            }

        }
        else if($headCount2 < 26) {
            //Merge header cells that contain topic name (for every cell after 26) and set topic name
            //This will handle up to 351 topics, so if a professor creates more than that, then they have other issues
            $exportFile->setActiveSheetIndex(0)->mergeCells($letters[$headCount2] . $letters[$headCount1] . '1' . ':' . $letters[$headCount2] . $letters[$headCount1 + 1] . '1');
            $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount2] . $letters[$headCount1] . '1', $top['topic_text']);

            $selectionST  = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
            $selectionST->execute(array(":topicId" => $top['topic_id']));
            $selection = $selectionST->fetchAll(PDO::FETCH_ASSOC);

            //Set Student and Date headers
            $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount2] . $letters[$headCount1] . $rowCount, 'Student');
            $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount2] . $letters[$headCount1 + 1] . $rowCount, 'Date Selected');

            foreach($selection as $sel) {
                $rowCount ++;
                $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount2] . $letters[$headCount1] . $rowCount, $sel['user_first_name'] . ' ' . $sel['user_last_name']);
                $exportFile->setActiveSheetIndex(0)->setCellValue($letters[$headCount2] . $letters[$headCount1 + 1] . $rowCount, $sel['date_selected']);
            }
        }
        $headCount1+=2;
    }

    $exportFile->getActiveSheet()->setTitle('Topic_Selector');

    foreach($exportFile->getActiveSheet()->getColumnDimension() as $col) {
        $col->setAutoSize(true);
    }

    $exportFile->getActiveSheet()->calculateColumnWidths();

    $filename = 'TopicSelector-'.$CONTEXT->title.'-Results.xls';

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$filename);
    header('Cache-Control: max-age=0');
    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objWriter = PHPExcel_IOFactory::createWriter($exportFile, 'Excel5');
    $objWriter->save('php://output');
} else {
    header( 'Location: '.addSession('../student-home.php') ) ;
}


