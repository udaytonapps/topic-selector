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

    $newTopic = $PDOX->prepare("UPDATE {$p} topic SET num_reserved=:numReserved WHERE list_id = :listId AND topic_id = :topicId");
    $newTopic->execute(array(
        ":listId" => $topics['list_id'],
        ":topicId" => $topicId,
        ":numReserved" => $numReserved,
    ));

    $newSelect = $PDOX->prepare("INSERT INTO {$p}selection (topic_id, user_id, date_selected) 
                                        values (:topicId, :userId, :dateSelected)");
    $newSelect->execute(array(
        ":topicId" => $topicId,
        ":userId" => $USER->id,
        ":dateSelected" => $currentTime,
    ));
    $_SESSION['success'] = 'Topic saved successfully.';
    header('Location: ' . addSession('index.php'));
}

// Start of the output
$OUTPUT->header();
?>
    <!-- Our main css file that overrides default Tsugi styling -->
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
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

        function confirmRemoveSelectTool() {
            return confirm("Are you sure that you want to remove yourself from this topic?");
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
        <?php
        if($USER->instructor) {
            if($topicList) {
                ?>
                <div class="container mainBody">
                    <h2 class="title">Topic Selector</h2>
                    <p class="instructions">Students are able to reserve their topics from the list you created.</p>
                    <div class="container topicView">
                        <?php
                        foreach($topic as $tops) {
                            $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
                            $selectionST->execute(array(":topicId" => $tops['topic_id']));
                            $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <script>
                                $(document).ready(function() {
                                    $('[data-toggle="popover"]').popover({
                                        trigger: 'focus',
                                        placement: 'right',
                                        html: true,
                                        title : '<h3 class="popTitle">Settings</h3>',
                                        content : '<a type="button" class="btn btn-default" href="assignStu.php?top=<?=$tops['topic_id']?>">Assign Student(s)</a>' +
                                            '<a type="button" class="btn btn-default" href="unassignStu.php?top=<?=$tops['topic_id']?>">Unassign Student(s)</a>' +
                                            '<a type="button" class="btn btn-default" href="allowStu.php?top=<?=$tops['topic_id']?>">Allow Additional Students</a>' +
                                            '<a type="button" class="btn btn-default" href="emailStu.php?top=<?=$tops['topic_id']?>">Email Student(s)</a>'
                                    });
                                });
                            </script>
                            <div class="card">
                                <div class="card-header" role="tab">
                                    <span class="topicName"><?=$tops['topic_text']?></span>
                                    <?php
                                    if($topics['allow_stu'] == 1) {
                                        $count = 0;
                                        foreach($selections as $select) {
                                            if($count > 0) {
                                                ?>
                                                <span class="registeredStu">, <?=findDisplayName($select['user_id'], $PDOX, $p)?></span>
                                                <?php
                                            } else {
                                                ?>
                                                <span class="registeredStu"><?=findDisplayName($select['user_id'], $PDOX, $p)?></span>
                                                <?php
                                            }
                                            $count++;
                                        }
                                    }
                                    ?>
                                    <a href="#" data-toggle="popover" data-trigger="focus" class="settings" role="button"><i class="fa fa-cog fa-2x"></i></a>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            } else {
                header('Location: ' . addSession('newTopics.php'));
            }
        } else {
            $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection");
            $selectionST->execute(array());
            $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
            $numSelected = 0;
            foreach($selections as $select) {
                if($select['user_id'] == $USER-> id) {
                    $numSelected++;
                }
            }
            if($topicList) {
                ?>
                <div class="container mainBody">
                    <h2 class="title">Topic Selector</h2>
                    <p class="instructions"></p>
                    <div class="container topicView">
                        <?php
                        foreach($topic as $tops) {
                            $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
                            $selectionST->execute(array(":topicId" => $tops['topic_id']));
                            $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
                            $userExists = false;
                            foreach($selections as $select) {
                                if($select['user_id'] == $USER-> id) {
                                    $userExists = true;
                                }
                            }
                            ?>
                            <div class="card">
                                <div class="card-header" role="tab">
                                    <form method="post">
                                        <span class="topicBox">
                                            <input class="topicId" id="topicId" name="topicId" value="<?=$tops['topic_id']?>" hidden>
                                            <input class="studentId" id="studentId" name="studentId" value="<?=$USER->id?>" hidden>
                                            <?php
                                            if($userExists == true || $numSelected >= $topics['num_topics'] || $tops['num_reserved'] >= $tops['num_allowed'] || $topics['stu_reserve'] == 0) {
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
                                                        <span class="registeredStu">, <?=findDisplayName($select['user_id'], $PDOX, $p)?></span>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <span class="registeredStu"><?=findDisplayName($select['user_id'], $PDOX, $p)?></span>
                                                        <?php
                                                    }
                                                    $count++;
                                                }
                                            }
                                            foreach($selections as $select) {
                                                if($select['user_id'] == $USER->id) {
                                                    ?>
                                                    <a class="removeSelect" onclick="confirmRemoveSelectTool()" href="deleteSelection.php?topic=<?=$select['topic_id']?>&user=<?=$select['user_id']?>"><i class="fa fa-trash fa-2x"></i></a>
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
                        <?php
                        //Something about no list available yet
                        ?>
                    </div>
                <?php
            }
        }

        ?>
    </div>
<?php
$OUTPUT->footerStart();
?>
    <script src="scripts/main.js" type="text/javascript"></script>
<?php
$OUTPUT->footerEnd();
