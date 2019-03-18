<?php

require_once "../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LTI = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;

$topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicsST->execute(array(":linkId" => $LINK->id));
$topics = $topicsST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['top']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {
    $numAllowed = isset($_POST["numReservations"]) ? $_POST["numReservations"] : " ";
    $numAllowed = $numAllowed + $topic['num_allowed'];
    $newTopic = $PDOX->prepare("UPDATE {$p} topic SET num_allowed=:numAllowed WHERE topic_id = :topicId");
    $newTopic->execute(array(
        ":topicId" => $_GET['top'],
        ":numAllowed" => $numAllowed,
    ));
    $_SESSION['success'] = 'New allowance saved successfully.';
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
        <?php
        if($USER->instructor) {
            ?>
            <div class="container mainBody">
                <h2 class="title">Topic Selector - Allow Additional Student(s)</h2>
                <p class="instructions">How many additional students would you like to reserve the topic, "<?=$topic['topic_text']?>?"</p>
                <div class="container">
                    <h4 class="curNum"><i>Current number allowed: <?=$topic['num_allowed']?></i></h4>
                    <form method="post">
                        <div class="container numRes">
                            <div class="col-sm-2">
                                <input type="number" class="numReservations" id="numReservations" name="numReservations">
                                <label for="numReservations"></label>
                            </div>
                        </div>
                        <div class="container assignButtons">
                            <div class="col-sm-1">
                                <button class="btn btn-success" type="submit">Save</button>
                            </div>
                            <div class="col-sm-1">
                                <a class="btn btn-danger" href="index.php">Cancel</a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <?php
        }

        ?>
    </div>
<?php
$OUTPUT->footerStart();
?>
    <script src="scripts/main.js" type="text/javascript"></script>
<?php
$OUTPUT->footerEnd();
