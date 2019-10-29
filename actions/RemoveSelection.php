<?php
require_once "../../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

$OUTPUT->bodyStart();

$selectionST  = $PDOX->prepare("SELECT * FROM {$p}ts_selection WHERE topic_id = :topicId");
$selectionST->execute(array(":topicId" => $_GET['topic']));
$selection = $selectionST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}ts_topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['topic']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

$delSelections = $PDOX->prepare("DELETE FROM {$p}ts_selection WHERE user_email = :userEmail AND topic_id = :topicId");
$delSelections->execute(array(":userEmail" => $_GET['user_email'], ":topicId" => $_GET['topic']));

$_SESSION['success'] = 'Topic selection removed successfully.';
if (isset($_GET["assign"]) && $_GET["assign"] == true) {
    header('Location: ' . addSession('../index.php?top=true'));
} else {
    header('Location: ' . addSession('../student-home.php'));
}
return;