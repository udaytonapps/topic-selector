<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;

include("menu.php");

$title = $LAUNCH->link->settingsGet("title", false);

if (!$title) {
    $LAUNCH->link->settingsSet("title", $LAUNCH->link->title);
    $title = $LAUNCH->link->title;
}

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

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['top']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

$stuReserve = isset($_POST["reservations"]) ? 1 : 0;
$stuAllow = isset($_POST["allow"]) ? 1 : 0;
$numTopics = isset($_POST["numReservations"]) ? $_POST["numReservations"] : " ";
$topicInput = isset($_POST["topic_list"]) ? $_POST["topic_list"] : " ";

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {
    $stuReserve = isset($_POST["stuReserve"]) ? $_POST["stuReserve"] : " ";
    $url = 'deleteSelection.php?topic=' . $_GET['top'] . '&user=' . $stuReserve;

    header('Location: ' . addSession($url));
}

// Start of the output
$OUTPUT->header();
?>
    <!-- Our main css file that overrides default Tsugi styling -->
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($title, false, false);

echo '</div>';// end container
?>
    <div id="main">
        <?php
        if($USER->instructor) {
            $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
            $selectionST->execute(array(":topicId" => $_GET['top']));
            $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="container mainBody">
                <p class="instructions">Which student would you like to unassign from the topic, "<?=$topic['topic_text']?>?"</p>
                <div class="container">
                    <form method="post">
                        <?php
                        if($selections) {
                            ?>
                            <div class="dropdown unassignDrop">
                                <select class="dropdown" id="stuReserve" name="stuReserve">
                                    <?php
                                    foreach($selections as $select) {
                                        ?>
                                        <option value="<?=$select['user_email']?>"><?=$select['user_first_name']?> <?=$select['user_last_name']?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="container assignButtons">
                                <div class="col-sm-1">
                                    <button class="btn btn-success" type="submit" onclick="return confirmRemoveStuTool()">Save</button>
                                </div>
                                <div class="col-sm-1">
                                    <a class="btn btn-danger" href="index.php">Cancel</a>
                                </div>
                            </div>
                        <?php
                        } else {
                            ?>
                            <div class="container noAssignment">
                                <p>No students are assigned to this topic.</p>
                            </div>
                            <div class="container assignButtons">
                                <div class="col-sm-1">
                                    <button class="btn btn-success" type="submit" disabled>Save</button>
                                </div>
                                <div class="col-sm-1">
                                    <a class="btn btn-danger" href="index.php">Cancel</a>
                                </div>
                            </div>
                        <?php
                        }
                        ?>


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
