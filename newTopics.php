<?php

require_once "../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LTI = LTIX::requireData();
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

$stuReserve = isset($_POST["reservations"]) ? 1 : 0;
$stuAllow = isset($_POST["allow"]) ? 1 : 0;
$numTopics = isset($_POST["numReservations"]) ? $_POST["numReservations"] : 1;
$topicInput = isset($_POST["topic_list"]) ? $_POST["topic_list"] : " ";
$dateSelected = "1";
$numReserved = 0;

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {

    if(isset($_GET['topList'])) {
        $updateList = $PDOX->prepare("UPDATE {$p}topic_list SET topic_list=:topicList, num_topics=:numTopics, stu_reserve=:stuReserve, allow_stu=:allowStu WHERE list_id = :listId");
        $updateList->execute(array(
            ":topicList" => $topicInput,
            ":numTopics" => $numTopics,
            ":stuReserve" => $stuReserve,
            ":allowStu" => $stuAllow
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
                if(preg_match_all('!\d+!', $tops, $tempNum)) {
                    $topicText = str_replace(array('0','1','2','3','4','5','6','7','8','9',','), '',$tops);

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
            if(preg_match_all('!\d+!', $tops, $tempNum)) {
                $num=implode('',$tempNum[0]);
                $topicText = str_replace(array('0','1','2','3','4','5','6','7','8','9',','), '',$tops);

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
            if(preg_match_all('!\d+!', $tops, $tempNum)) {
                $num=implode('',$tempNum[0]);
                $topicText = str_replace(array('0','1','2','3','4','5','6','7','8','9',','), '',$tops);

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

// Start of the output
$OUTPUT->header();
?>
    <!-- Our main css file that overrides default Tsugi styling -->
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
            document.getElementById("main").style.marginLeft = "250px";
        }

        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("main").style.marginLeft= "0";
        }
    </script>

    <script>
        function confirmResetTool() {
            return confirm("Are you sure that you want to clear all topics? This cannot be undone.");
        }
    </script>
<?php
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
?>

    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <a href="index.php"><span class="fa fa-home" aria-hidden="true"></span> Home</a>
        <?php
        if($USER->instructor){
            ?>
            <a href="#"><span class="fa fa-edit" aria-hidden="true"></span> Edit Topics</a>
            <a href="#"><span class="fa fa-print" aria-hidden="true"></span> Print View</a>
            <a href="clearTopics.php" onclick="return confirmResetTool();"><span class="fa fa-trash" aria-hidden="true"></span> Clear All</a>
            <?php
        }
        ?>
    </div>

    <div id="main">
        <button class="openbtn" onclick="openNav()">☰ Menu</button>
        <div class="container mainBody">
            <h2 class="title">Topic Selector</h2>
            <p class="instructions">Enter the topics that students can sign up for</p>
            <p class="instructions">Put each topic on a new line. Each topic will be open to one student by default.
                You can enter the number of slots with a comma and a space after the topic if you want more than one.</p>
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
                            <input type="number" class="numReservations" id="numReservations" name="numReservations">
                            <label class="custom-control-label" for="numReservations">topic(s).</label>
                        </label>
                    </div>
                    <div class="container">
                        <input type="checkbox" class="custom-control-input" id="allow" name="allow">
                        <label class="custom-control-label" for="allow">Allow students to see who has reserved each topic</label>
                    </div>
                    <div class="container">
                        <button type="submit" class="btn btn-success">Save</button>
                        <?php
                        if(isset($_GET['topList'])) {
                            ?>
                            <a type="button" class="btn btn-danger" href="index.php">Cancel</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
$OUTPUT->footerStart();
?>
    <script src="scripts/main.js" type="text/javascript"></script>
<?php
$OUTPUT->footerEnd();
