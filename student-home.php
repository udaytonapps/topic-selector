<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");

$topicListST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicListST->execute(array(":linkId" => $LINK->id));
$topicList = $topicListST->fetchAll(PDO::FETCH_ASSOC);

$topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicsST->execute(array(":linkId" => $LINK->id));
$topics = $topicsST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE list_id = :listId");
$topicST->execute(array(":listId" => $topics['list_id']));
$topic = $topicST->fetchAll(PDO::FETCH_ASSOC);

$stuReserve = isset($_POST["reservations"]) ? 1 : 0;
$stuAllow = isset($_POST["allow"]) ? 1 : 0;
$numTopics = isset($_POST["numReservations"]) ? $_POST["numReservations"] : " ";
$topicInput = isset($_POST["topic_list"]) ? $_POST["topic_list"] : " ";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topicId = isset($_POST["topicId"]) ? $_POST["topicId"] : " ";
    $topic2ST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE list_id = :listId AND topic_id = :topicId");
    $topic2ST->execute(array(":listId" => $topics['list_id'], ":topicId" => $topicId));
    $topic2 = $topic2ST->fetch(PDO::FETCH_ASSOC);
    $numReserved = $topic2['num_reserved'];
    $numReserved = $numReserved + 1;

    $newTopic = $PDOX->prepare("UPDATE {$p}topic SET num_reserved=:numReserved WHERE list_id = :listId AND topic_id = :topicId");
    $newTopic->execute(array(
        ":listId" => $topics['list_id'],
        ":topicId" => $topicId,
        ":numReserved" => $numReserved
    ));

    $userEmail = isset($_POST["studentEmail"]) ? $_POST["studentEmail"] : " ";
    $userFirstName = isset($_POST["firstName"]) ? $_POST["firstName"] : "Unknown";
    $userLastName = isset($_POST["lastName"]) ? $_POST["lastName"] : "";

    $newSelect = $PDOX->prepare("INSERT INTO {$p}selection (topic_id, user_email, user_first_name, user_last_name, date_selected) 
                                        values (:topicId, :userEmail, :userFirstName, :userLastName, :dateSelected)");
    $newSelect->execute(array(
        ":topicId" => $topicId,
        ":userEmail" => $userEmail,
        ":userFirstName" => $userFirstName,
        ":userLastName" => $userLastName,
        ":dateSelected" => $currentTime,
    ));
    $_SESSION['success'] = 'Topic saved successfully.';
    header('Location: ' . addSession('index.php'));
}

$title = $LAUNCH->link->settingsGet("title", $LAUNCH->link->title);

include("menu.php");

$OUTPUT->header();

?>
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($title, true, false);

echo '<p class="lead">Select the topic you would like to reserve.</p>';

?>
<div id="main">
<?php

$selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection");
$selectionST->execute(array());
$selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
$numSelected = 0;
foreach($selections as $select) {
    if($select['user_email'] == $USER->email) {
        $numSelected++;
    }
}
if($topicList) {
    ?>
    <div class="container mainBody">
        <p class="instructions"></p>
        <div class="container topicView">
            <?php
            foreach($topic as $tops) {
                $z = 0;
                $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
                $selectionST->execute(array(":topicId" => $tops['topic_id']));
                $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
                $userExists = false;
                foreach($selections as $select) {
                    if($select['user_email'] == $USER->email) {
                        $userExists = true;
                    }
                    if($select['topic_id'] == $tops['topic_id']) {
                        $z++;
                    }
                }
                ?>
                <div class="card">
                    <div class="card-header" role="tab">
                        <form method="post">
                            <span class="topicBox">
                                <input class="topicId" id="topicId" name="topicId" value="<?=$tops['topic_id']?>" type="hidden">
                                <input class="studentEmail" id="studentEmail" name="studentEmail" value="<?=$USER->email?>" type="hidden">
                                <?php
                                $hasRosters = LTIX::populateRoster(false);
                                $x = 0;
                                if ($hasRosters) {
                                    $rosterData = $GLOBALS['ROSTER']->data;
                                    foreach ($rosterData as $roster){
                                        if($rosterData[$x]['person_contact_email_primary'] == $USER->email) {
                                            ?>
                                            <input class="firstName" id="firstName" name="firstName" value="<?=$rosterData[$x]['person_name_given']?>" type="hidden">
                                            <input class="lastName" id="lastName" name="lastName" value="<?=$rosterData[$x]['person_name_family']?>" type="hidden">
                                            <?php
                                        }
                                        $x++;
                                    }
                                }
                                if($userExists == true || $numSelected >= $topics['num_topics'] || $topics['stu_reserve'] == 0 || $z >= $tops['num_allowed']) {
                                    ?>
                                    <button type="submit" class="btn btn-success" disabled>Reserve</button>
                                    <?php
                                } else {
                                    ?>
                                    <button type="submit" class="btn btn-success">Reserve</button>
                                    <?php
                                }
                                ?>
                                <span class="topicName"><?=$tops['topic_text']?></span>
                                <?php
                                if($topics['allow_stu'] == 1) {
                                    $count = 0;
                                    foreach($selections as $select) {
                                        if($count > 0) {
                                            ?>
                                            <span class="registeredStu">, <?=$select['user_first_name']?> <?=$select['user_last_name']?></span>
                                            <?php
                                        } else {
                                            ?>
                                            <span class="registeredStu"><?=$select['user_first_name']?> <?=$select['user_last_name']?></span>
                                            <?php
                                        }
                                        $count++;
                                    }
                                }
                                foreach($selections as $select) {
                                    if($select['user_email'] == $USER->email) {
                                        ?>
                                        <a class="removeSelect" onclick="confirmRemoveSelectTool()" href="deleteSelection.php?topic=<?=$select['topic_id']?>&user=<?=$select['user_email']?>"><i class="fa fa-trash fa-2x"></i></a>
                                        <?php
                                    }
                                }
                                ?>
                            </span>
                        </form>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="container">
        <img src="styles/IsidoreDunce.png" class="noTopics" alt="No topics have been added yet.">
    </div>
    <?php
}

echo '</div>';// end container

if ($USER->instructor) {
    $OUTPUT->helpModal("Instructor Help", '
                            <p>This help will show when an instructor is previewing student view.</p>');
} else {
    $OUTPUT->helpModal("Student Help", '
                            <p>This is the help that students will see.</p>');
}
$OUTPUT->footerStart();

$OUTPUT->footerEnd();