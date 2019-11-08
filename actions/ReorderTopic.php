<?php
require_once "../../config.php";
require_once('../dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$TS_DAO = new TS_DAO($PDOX, $p);

$topic_id = isset($_POST["topic_id"]) ? $_POST["topic_id"] : false;

if ( $USER->instructor && $topic_id ) {
    $topics = $TS_DAO->getTopics($LINK->id);
    $prevTopic = false;
    foreach ($topics as $topic) {
        if ($topic["topic_id"] == $topic_id) {
            // Move this one up
            if($topic["topic_num"] == 1) {
                // This was the first so put it at the end
                $TS_DAO->updateTopicNumber($topic_id, count($topics) + 1);
                $TS_DAO->fixUpTopicNumbers($LINK->id);
                break;
            } else {
                // This was one of the other topics so swap with previous
                $TS_DAO->updateTopicNumber($topic_id, $prevTopic["topic_num"]);
                $TS_DAO->updateTopicNumber($prevTopic["topic_id"], $topic["topic_num"]);
                break;
            }
        }
        $prevTopic = $topic;
    }

    $_SESSION["success"] = "Topic Order Saved.";

    $result = array();

    $OUTPUT->buffer=true;
    $result["flashmessage"] = $OUTPUT->flashMessages();

    header('Content-Type: application/json');

    echo json_encode($result, JSON_HEX_QUOT | JSON_HEX_TAG);

    exit;
} else if ($USER->instructor) {
    exit;
} else {
    header( 'Location: '.addSession('../student-home.php') ) ;
}
