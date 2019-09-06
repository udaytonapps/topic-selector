<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;
use \Tsugi\UI\SettingsForm;

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

$topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicsST->execute(array(":linkId" => $LINK->id));
$topics = $topicsST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE list_id = :listId");
$topicST->execute(array(":listId" => $topics['list_id']));
$topic = $topicST->fetchAll(PDO::FETCH_ASSOC);

$stuReserve = isset($_POST["reservations"]) ? 1 : 0;
$stuAllow = isset($_POST["allow"]) ? 1 : 0;
$numTopics = isset($_POST["numReservations"]) ? $_POST["numReservations"] : 1;
$topicInput = isset($_POST["topic_list"]) ? $_POST["topic_list"] : " ";
$dateSelected = "1";
$numReserved = 0;

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {
    if(!$topics) {
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

$OUTPUT->pageTitle($title, true, true);


if(isset($_GET['confirm'])) {
    if($_GET['confirm'] == "1") {
        ?>
        <script>
            if(confirm('Are you sure you want to clear all topics? This cannot be undone.')) {
                <?php
                header('Location: ' . addSession('clearTopics.php?confirm=1'));
                ?>
            }
        </script>
        <?php
    }
    if($_GET['confirm'] == 2) {
        ?>
        <script>
            if(confirm('Are you sure you want to clear all topics? This cannot be undone.')) {
                <?php
                header('Location: ' . addSession('clearSelections.php?confirm=2'))
                ?>
            }
        </script>
        <?php
    }
}

if($topics) {
    ?>
    <div class="container mainBody">
        <p class="instructions">Students are able to reserve their topics from the list you created. The number of
            students allowed per topic is
            indicated in parenthesis next to the topic name. These can be changed in by selecting 'Edit Topics' in
            the 'Options' drop-down menu.</p>
        <div class="container topicView">
            <?php
            $count1 = 0;
            foreach ($topic as $tops) {
                $selectionST = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
                $selectionST->execute(array(":topicId" => $tops['topic_id']));
                $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="card">
                    <div class="card-header" role="tab">
                        <span class="topicName"><?= $tops['topic_text'] ?> (<?= $tops['num_allowed'] ?>)</span>
                        <?php
                        $count = 0;
                        foreach ($selections as $select) {
                            if ($count > 0) {
                                ?>
                                <span
                                    class="registeredStu">, <?= $select['user_first_name'] ?> <?= $select['user_last_name'] ?></span>
                                <?php
                            } else {
                                ?>
                                <span
                                    class="registeredStu"><?= $select['user_first_name'] ?> <?= $select['user_last_name'] ?></span>
                                <?php
                            }
                            $count++;
                        }
                        ?>
                        <div class="dropdown settings">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i
                                    class="fa fa-cog fa-2x"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="assignStu.php?top=<?= $tops['topic_id'] ?>">Assign Student(s)</a></li>
                                <li><a href="unassignStu.php?top=<?= $tops['topic_id'] ?>">Unassign Student(s)</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
                $count1++;
            }
            ?>
        </div>
    </div>
    <?php
} else {
    ?>
    <div id="main">
        <div class="container mainBody">
            <p class="instructions">Enter the topics that students can sign up for</p>
            <p class="instructions">Put each topic on a new line. Each topic will be open to one student by default.
                You can enter the number of slots with a comma after the topic if you want more than one.</p>
            <br>
            <form method="post">
                <div class="container">
                    <div class="col-sm-7">
                        <textarea class="topicInput" id="topic_list" name="topic_list"></textarea>
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
                        <input type="checkbox" class="custom-control-input" id="reservations" name="reservations">
                        <label class="custom-control-label" for="reservations"> Students can reserve
                            <input type="number" class="numReservations" id="numReservations" name="numReservations" value="1">
                            <label class="custom-control-label" for="numReservations">topic(s).</label>
                        </label>
                    </div>
                    <div class="container">
                        <input type="checkbox" class="custom-control-input" id="allow" name="allow">
                        <label class="custom-control-label" for="allow">Allow students to see who has reserved each topic</label>
                    </div>
                    <div class="container">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}
echo '</div>';// end container

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