<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

$OUTPUT->bodyStart();

$topicListST  = $PDOX->prepare("SELECT * FROM {$p}topic_list");
$topicListST->execute(array());
$topicList = $topicListST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic");
$topicST->execute(array());
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

$selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection");
$selectionST->execute(array());
$selection = $selectionST->fetch(PDO::FETCH_ASSOC);

$delTopicList = $PDOX->prepare("DELETE FROM {$p}topic_list");
$delTopicList->execute(array());

$delTopics = $PDOX->prepare("DELETE FROM {$p}topic");
$delTopicList->execute(array());

$delSelections = $PDOX->prepare("DELETE FROM {$p}selection");
$delSelections->execute(array());


header('Location: ' . addSession('index.php'));
return;