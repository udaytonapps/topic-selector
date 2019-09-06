<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;
use Tsugi\UI\SettingsForm;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");

function findDisplayName($user_id, $PDOX, $p) {
    $nameST = $PDOX->prepare("SELECT displayname FROM {$p}lti_user WHERE user_id = :user_id");
    $nameST->execute(array(":user_id" => $user_id));
    $name = $nameST->fetch(PDO::FETCH_ASSOC);
    return $name["displayname"];
}

function deleteTopic($topicId, $PDOX, $p) {
    $delTopic = $PDOX->prepare("DELETE FROM {$p}topic WHERE topic_id = :topicId");
    $delTopic->execute(array(":topicId" => $topicId));

    $delSelections = $PDOX->prepare("DELETE FROM {$p}selection WHERE topic_id = :topicId");
    $delSelections->execute(array(":topicId" => $topicId));
}

$topicListST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicListST->execute(array(":linkId" => $LINK->id));
$topicList = $topicListST->fetch(PDO::FETCH_ASSOC);

$topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE list_id = :listId");
$topicsST->execute(array(":listId" => $topicList['list_id']));
$topics = $topicsST->fetch(PDO::FETCH_ASSOC);

$stuReserve = isset($_POST["reservations"]) ? 1 : 0;
$stuAllow = isset($_POST["allow"]) ? 1 : 0;
$numTopics = isset($_POST["numReservations"]) ? $_POST["numReservations"] : 1;
$topicInput = isset($_POST["topic_list"]) ? $_POST["topic_list"] : " ";
$dateSelected = "1";
$numReserved = 0;

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {

    if(isset($topics['list_id'])) {
        $updateList = $PDOX->prepare("UPDATE {$p}topic_list SET topic_list=:topicList, num_topics=:numTopics, stu_reserve=:stuReserve, allow_stu=:allowStu WHERE list_id = :listId");
        $updateList->execute(array(
            ":topicList" => $topicInput,
            ":numTopics" => $numTopics,
            ":stuReserve" => $stuReserve,
            ":allowStu" => $stuAllow,
            ":listId" => $topics['list_id']
        ));

        $topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
        $topicsST->execute(array(":linkId" => $LINK->id));
        $topics = $topicsST->fetch(PDO::FETCH_ASSOC);

        $topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE list_id = :listId");
        $topicST->execute(array(":listId" => $topics['list_id']));
        $topic = $topicST->fetchAll(PDO::FETCH_ASSOC);

        foreach($topic as $oldTops) {
            $exists = false;
            foreach(preg_split("/((\r?\n)|(\r\n?))/", $topics['topic_list']) as $tops) {
                $searchString = ",";
                if(strpos($tops, $searchString) !== false) {
                    $strings = array_map('trim', explode(',', $tops));
                    $topicText = $strings[0];

                    if($oldTops['topic_text'] == $topicText) {
                        $exists = true;
                    }
                } else {
                    if($oldTops['topic_text'] == $tops) {
                        $exists = true;
                    }
                }
            }
            if($exists == false) {
                deleteTopic($oldTops['topic_id'], $PDOX, $p);
            }
        }
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $topics['topic_list']) as $tops) {
            $exists = false;
            $searchString = ",";
            if(strpos($tops, $searchString) !== false) {
                $strings = array_map('trim', explode(',', $tops));
                $num = $strings[1];
                $topicText = $strings[0];

                foreach($topic as $oldTops) {
                    if($oldTops['topic_text'] == $topicText) {
                        $exists = true;
                    }
                }

                if($exists == false) {
                    $newTopic = $PDOX->prepare("INSERT INTO {$p}topic (list_id, topic_text, num_allowed, num_reserved) 
                                        values (:listId, :topicText, :numAllowed, :numReserved)");
                    $newTopic->execute(array(
                        ":listId" => $topics['list_id'],
                        ":topicText" => $topicText,
                        ":numAllowed" => $num,
                        ":numReserved" => $numReserved,
                    ));
                } else {
                    $updateTopic = $PDOX->prepare("UPDATE {$p}topic SET num_allowed=:numAllowed WHERE topic_text = :topicText");
                    $updateTopic->execute(array(
                        ":numAllowed" => $num,
                        ":topicText" => $topicText
                    ));
                }
            } else {
                $num = 1;
                foreach($topic as $oldTops) {
                    if($oldTops['topic_text'] == $tops) {
                        $exists = true;
                    }
                }

                if($exists == false) {
                    $newTopic = $PDOX->prepare("INSERT INTO {$p}topic (list_id, topic_text, num_allowed, num_reserved) 
                                        values (:listId, :topicText, :numAllowed, :numReserved)");
                    $newTopic->execute(array(
                        ":listId" => $topics['list_id'],
                        ":topicText" => $tops,
                        ":numAllowed" => $num,
                        ":numReserved" => $numReserved,
                    ));
                } else {
                    $updateTopic = $PDOX->prepare("UPDATE {$p}topic SET num_allowed=:numAllowed WHERE topic_text = :topicText");
                    $updateTopic->execute(array(
                        ":numAllowed" => $num,
                        ":topicText" => $tops
                    ));
                }
            }
        }

    } else {
        $newList = $PDOX->prepare("INSERT INTO {$p}topic_list (link_id, num_topics, topic_list, stu_reserve, allow_stu) 
                                        values (:linkId, :numTopics, :topicList, :stuReserve, :allowStu)");
        $newList->execute(array(
            ":linkId" => $LINK->id,
            ":numTopics" => $numTopics,
            ":topicList" => $topicInput,
            ":stuReserve" => $stuReserve,
            ":allowStu" => $stuAllow,
        ));

        $topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
        $topicsST->execute(array(":linkId" => $LINK->id));
        $topics = $topicsST->fetch(PDO::FETCH_ASSOC);

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $topics['topic_list']) as $tops) {
            $searchString = ",";
            if(strpos($tops, $searchString) !== false) {
                $strings = array_map('trim', explode(',', $tops));
                $num = $strings[1];
                $topicText = $strings[0];

                $newTopic = $PDOX->prepare("INSERT INTO {$p}topic (list_id, topic_text, num_allowed, num_reserved) 
                                        values (:listId, :topicText, :numAllowed, :numReserved)");
                $newTopic->execute(array(
                    ":listId" => $topics['list_id'],
                    ":topicText" => $topicText,
                    ":numAllowed" => $num,
                    ":numReserved" => $numReserved,
                ));
            } else {
                $num = 1;
                $newTopic = $PDOX->prepare("INSERT INTO {$p}topic (list_id, topic_text, num_allowed, num_reserved) 
                                        values (:listId, :topicText, :numAllowed, :numReserved)");
                $newTopic->execute(array(
                    ":listId" => $topics['list_id'],
                    ":topicText" => $tops,
                    ":numAllowed" => $num,
                    ":numReserved" => $numReserved,
                ));
            }
        }
    }


    $_SESSION['success'] = 'Topics saved successfully.';
    header('Location: ' . addSession('index.php'));
}

if (SettingsForm::isSettingsPost()) {
    if (!isset($_POST["title"]) || trim($_POST["title"]) === '') {
        $_SESSION["error"] = __('Title cannot be blank.');
    } else {
        SettingsForm::handleSettingsPost();
        $_SESSION["success"] = __('All settings saved.');
    }
    header('Location: '.addSession('index.php'));
    return;
}

$title = $LAUNCH->link->settingsGet("title", false);

if (!$title) {
    $LAUNCH->link->settingsSet("title", $LAUNCH->link->title);
    $title = $LAUNCH->link->title;
}

SettingsForm::start();
SettingsForm::text('title',__('Tool Title'));
SettingsForm::end();

$title = $title . " - Edit Topics";

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
if(isset($topics['list_id'])) {
    ?>
    <div id="main">
        <div class="container mainBody">
            <p class="instructions">Edit the topics that students can sign up for</p>
            <p class="instructions">Put each topic on a new line. Each topic will be open to one student by default.
                You can enter the number of slots with a comma and a space after the topic if you want more than one.</p>
            <br>
            <form method="post">
                <div class="container">
                    <div class="col-sm-7">
                        <textarea class="topicInput" id="topic_list" name="topic_list" ><?=$topics['topic_list']?></textarea>
                    </div>
                    <div class="col-sm-5">
                        <p class="example"><u>Example:</u></p>
                        <p class="example">Business Intelligence <br/>
                            Marketing <br/>
                            Accounting,2 <br/>
                            Decision Making <br/>
                            MIS</p>
                    </div>
                </div>
                <br>
                <div class="container">
                    <div class="container">
                        <?php
                        if($topics['stu_reserve'] == 1) {
                            ?>
                            <input type="checkbox" class="custom-control-input" id="reservations" name="reservations" checked>
                            <?php
                        } else {
                            ?>
                            <input type="checkbox" class="custom-control-input" id="reservations" name="reservations">
                            <?php
                        }
                        ?>

                        <label class="custom-control-label" for="reservations"> Students can reserve
                            <input type="number" class="numReservations" id="numReservations" name="numReservations" value="<?=$topics['num_topics']?>">
                            <label class="custom-control-label" for="numReservations">topic(s).</label>
                        </label>
                    </div>
                    <div class="container">
                        <?php
                        if($topics['allow_stu'] == 1) {
                            ?>
                            <input type="checkbox" class="custom-control-input" id="allow" name="allow" checked>
                            <?php
                        } else {
                            ?>
                            <input type="checkbox" class="custom-control-input" id="allow" name="allow">
                            <?php
                        }
                        ?>
                        <label class="custom-control-label" for="allow">Allow students to see who has reserved each topic</label>
                    </div>
                    <div class="container">
                        <button type="submit" class="btn btn-success" onclick="confirmUpdateTopicsTool()">Save</button>
                        <a type="button" class="btn btn-danger" href="index.php">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    header('Location: ' . addSession('index.php'));
}

$OUTPUT->helpModal("Instructions", __('
                        <h4>Adding Topics:</h4>
                        <p>Add a topic to the text box and hit \'Enter\' to put the next topic on a new line. Each topic defaults to allowing 
                            1 student to reserve it, but if you would like to increase the number of students who can reserve each topic,
                            put a comma at the end of the topic name, followed by the number of students you would like to allow for that topic.</p>
                        <p>After you are finished adding topics, you can choose to allow students to select multiple topics by checking the
                           \'Students can reserve\' box and selecting how many topics you would like to allow each student to reserve. You can also
                           check the box underneath to allow students to view who has signed up for each topic.</p>'));

$OUTPUT->footerStart();

$OUTPUT->footerEnd();