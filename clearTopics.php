<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

$OUTPUT->bodyStart();

$topicListST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicListST->execute(array(":linkId" => $LINK->id));
$topicList = $topicListST->fetch(PDO::FETCH_ASSOC);

$delTopics = $PDOX->prepare("DELETE FROM {$p}topic_list WHERE link_id = :linkId");
$delTopics->execute(array(":linkId" => $LINK->id));

header('Location: ' . addSession('index.php'));
return;