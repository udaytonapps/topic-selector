<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$p = $CFG->dbprefix;

$LAUNCH = LTIX::requireData();

include("menu.php");

$OUTPUT->header();

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

$OUTPUT->flashMessages();

if(isset($_GET['confirm'])) {
    if(($_GET['confirm'] == 2)) {
        $topicST = $PDOX->prepare("SELECT * FROM {$p}topic");
        $topicST->execute(array());
        $topic = $topicST->fetchAll(PDO::FETCH_ASSOC);

        $numReserved = 0;

        foreach ($topic as $top) {
            $newTopic = $PDOX->prepare("UPDATE {$p}topic SET num_reserved=:numReserved WHERE topic_id = :topicId");
            $newTopic->execute(array(
                ":topicId" => $top['topic_id'],
                ":numReserved" => $numReserved,
            ));
        }

        $delSelections = $PDOX->prepare("DELETE FROM {$p}selection");
        $delSelections->execute(array());

        header('Location: ' . addSession('index.php'));
        return;
    }
}

header('Location: ' . addSession('index.php?confirm=2'));
return;