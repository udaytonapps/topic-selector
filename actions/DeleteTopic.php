<?php
require_once "../../config.php";
require_once "../dao/TS_DAO.php";

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$t_buildST  = $PDOX->prepare("SELECT * FROM {$p}topic_build WHERE link_id = :linkId");
$t_buildST->execute(array(":linkId" => $LINK->id));
$t_build = $t_buildST->fetch(PDO::FETCH_ASSOC);

$TS_DAO = new TS_DAO($PDOX, $p);

$topic_id = isset($_POST["topic_id"]) ? $_POST["topic_id"] : false;

if ( $USER->instructor && $topic_id ) {

    $TS_DAO->deleteTopic($topic_id);

    $TS_DAO->fixUpTopicNumbers($t_build['list_id']);

    $_SESSION['success'] = "Topic Deleted.";

    $OUTPUT->buffer=true;
    $result["flashmessage"] = $OUTPUT->flashMessages();

    header('Content-Type: application/json');

    echo json_encode($result, JSON_HEX_QUOT | JSON_HEX_TAG);

    exit;
} else {
    header( 'Location: '.addSession('../student-home.php') ) ;
}

