<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

$OUTPUT->bodyStart();

$selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
$selectionST->execute(array(":topicId" => $_GET['topic']));
$selection = $selectionST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['topic']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

$numReserved = $topic['num_reserved'];
$numReserved--;

$newTopic = $PDOX->prepare("UPDATE {$p}topic SET num_reserved=:numReserved WHERE topic_id = :topicId");
$newTopic->execute(array(
    ":topicId" => $_GET['topic'],
    ":numReserved" => $numReserved,
));

$delSelections = $PDOX->prepare("DELETE FROM {$p}selection WHERE user_email = :userEmail AND topic_id = :topicId");
$delSelections->execute(array(":userEmail" => $_GET['user'], ":topicId" => $_GET['topic']));


header('Location: ' . addSession('index.php'));
return;