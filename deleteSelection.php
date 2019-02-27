<?php
require_once "../config.php";

use Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

$OUTPUT->bodyStart();

$selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection");
$selectionST->execute(array());
$selection = $selectionST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['topic']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

$numReserved = $topic['num_reserved'];
$numReserved--;

$newTopic = $PDOX->prepare("UPDATE {$p} topic SET num_reserved=:numReserved WHERE topic_id = :topicId");
$newTopic->execute(array(
    ":topicId" => $_GET['topic'],
    ":numReserved" => $numReserved,
));

$delSelections = $PDOX->prepare("DELETE FROM {$p}selection WHERE topic_id = :topicId AND user_id = :userId");
$delSelections->execute(array(":topicId" => $_GET['topic'], ":userId" => $_GET['user']));


header('Location: ' . addSession('index.php'));
return;