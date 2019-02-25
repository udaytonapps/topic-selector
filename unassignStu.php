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

$stuReserve = isset($_POST["reservations"]) ? 1 : 0;
$stuAllow = isset($_POST["allow"]) ? 1 : 0;
$numTopics = isset($_POST["numReservations"]) ? $_POST["numReservations"] : " ";
$topicInput = isset($_POST["topic_list"]) ? $_POST["topic_list"] : " ";

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {

    $newAssign = $PDOX->prepare("INSERT INTO {$p}topic_list (link_id, num_topics, topic_list, stu_reserve, allow_stu) 
                                        values (:linkId, :numTopics, :topicList, :stuReserve, :allowStu)");
    $newAssign->execute(array(
        ":linkId" => $LINK->id,
        ":numTopics" => $numTopics,
        ":topicList" => $topicInput,
        ":stuReserve" => $stuReserve,
        ":allowStu" => $stuAllow,
    ));
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
        <a href="#"><span class="fa fa-edit" aria-hidden="true"></span> Edit Topics</a>
        <a href="#"><span class="fa fa-print" aria-hidden="true"></span> Print View</a>
        <a href="index.php"><span class="fa fa-home" aria-hidden="true"></span> Home</a>
        <?php
        if($USER->instructor){
            ?>
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
                <h2 class="title">Topic Selector - Unassign Student</h2>
                <p class="instructions">Which student would you like to unassign from the topic, "<?=$_GET['top']?>?"</p>
                <div class="container">
                    <form method="post">
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Unassign Student
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu" id="stuReserve">
                                <?php
                                foreach($USER->id as $user) {
                                    ?>
                                    <li value="<?=$user?>"><a href="#"><?=findDisplayName($user,$PDOX,$p)?></a></li>
                                    <?php
                                }
                                ?>
                            </ul>
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
