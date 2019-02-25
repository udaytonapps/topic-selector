<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

$OUTPUT->bodyStart();

$topicListST  = $PDOX->prepare("SELECT * FROM {$p}topic_list");
$topicListST->execute(array());
$topicList = $topicListST->fetch(PDO::FETCH_ASSOC);

$delTopics = $PDOX->prepare("DELETE FROM {$p}topic_list");
$delTopics->execute(array());

header('Location: ' . addSession('index.php'));
return;