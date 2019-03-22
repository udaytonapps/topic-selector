<?php

require_once "../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LTI = LTIX::requireData();
$p = $CFG->dbprefix;
$displayname = $USER->displayname;

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");

$topicListST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicListST->execute(array(":linkId" => $LINK->id));
$topicList = $topicListST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['top']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {
    $userEmail = isset($_POST["stuReserve"]) ? $_POST["stuReserve"] : "johndoe@aol.com";
    $userFirstName = " ";
    $userLastName = " ";

    $hasRosters = LTIX::populateRoster(false);
    $x = 0;
    if ($hasRosters) {
        $rosterData = $GLOBALS['ROSTER']->data;
        foreach ($rosterData as $roster){
            if($rosterData[$x]['person_contact_email_primary'] == $userEmail){
                $userFirstName = $rosterData[$x]['person_name_given'];
                $userLastName = $rosterData[$x]['person_name_family'];
                break;
            }
            $x++;
        }
    }

    $newSelect = $PDOX->prepare("INSERT INTO {$p}selection (topic_id, user_email, user_first_name, user_last_name, date_selected) 
                                        values (:topicId, :userEmail, :userFirstName, :userLastName, :dateSelected)");
    $newSelect->execute(array(
        ":topicId" => $_GET['top'],
        ":userEmail" => $userEmail,
        ":userFirstName" => $userFirstName,
        ":userLastName" => $userLastName,
        ":dateSelected" => $currentTime,
    ));

    $_SESSION['success'] = 'Student assigned successfully.';
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
            document.getElementById("mySidebar").style.width = "270px";
            document.getElementById("main").style.marginLeft = "270px";
            var v = document.getElementById("home");
            var w = document.getElementById("edit");
            var x = document.getElementById("print");
            var y = document.getElementById("top");
            var z = document.getElementById("select");
            setTimeout(function() {
                v.style.display = "block";
                w.style.display = "block";
                x.style.display = "block";
                y.style.display = "block";
                z.style.display = "block";
            }, 350);
        }

        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
            document.getElementById("main").style.marginLeft= "0";
            var v = document.getElementById("home");
            var w = document.getElementById("edit");
            var x = document.getElementById("print");
            var y = document.getElementById("top");
            var z = document.getElementById("select");
            v.style.display = "none";
            w.style.display = "none";
            x.style.display = "none";
            y.style.display = "none";
            z.style.display = "none";
        }
    </script>

    <script>
        function confirmResetTopicTool() {
            return confirm("Are you sure that you want to clear all topics? This cannot be undone.");
        }

        function confirmResetSelectTool() {
            return confirm("Are you sure that you want to clear all selections? This cannot be undone.")
        }
    </script>
<?php
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
?>

    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <a href="index.php" id="home" style="display: none"><span class="fa fa-home" aria-hidden="true"></span> Home</a>
        <a href="newTopics.php?topList=<?=$topics['list_id']?>" id="edit" style="display: none"><span class="fa fa-edit" aria-hidden="true"></span> Edit Topics</a>
        <a href="#" onclick="printList()" id="print" style="display: none"><span class="fa fa-print" aria-hidden="true"></span> Print View</a>
        <a href="clearTopics.php" onclick="return confirmResetTopicTool();" id="top" style="display: none"><span class="fa fa-trash" aria-hidden="true"></span> Clear Topics</a>
        <a href="clearSelections.php" onclick="return confirmResetSelectTool();" id="select" style="display: none"><span class="fa fa-trash" aria-hidden="true"></span> Clear Selections</a>
    </div>

    <div id="main">
        <button class="openbtn" onclick="openNav()">☰ Menu</button>
        <?php
        if($USER->instructor) {
            ?>
            <div class="container mainBody">
                <h2 class="title">Topic Selector - Assign Student</h2>
                <p class="instructions">Which student would you like to assign to the topic, "<?=$topic['topic_text']?>?"</p>
                <p class="instructions2"><i>Students assigned to the maximum number of topics are not available to be selected.
                    You must first unassign students from already selected topics.</i></p>
                <div class="container">
                    <form method="post">
                        <div class="dropdown assignDrop">
                            <select class="dropdown assignStu" id="stuReserve" name="stuReserve">
                                <?php
                                $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection");
                                $selectionST->execute(array());
                                $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);

                                $hasRosters = LTIX::populateRoster(false);
                                $x = 0;
                                $z = 0;
                                if ($hasRosters) {
                                    $rosterData = $GLOBALS['ROSTER']->data;
                                    sort($rosterData['person_name_family']);
                                    foreach ($rosterData as $roster) {
                                        $y = 0;
                                        $z = 0;
                                        $w = 0;
                                        foreach($selections as $select) {
                                            if($rosterData[$x]['person_contact_email_primary'] == $select['user_email']) {
                                                $y++;
                                                if($select['topic_id'] == $_GET['top']) {
                                                    $w++;
                                                }
                                            }
                                            if($select['topic_id'] == $_GET['top']) {
                                                $z++;
                                            }
                                        }
                                        if($roster["roles"] == "Learner" && $y < $topicList['num_topics'] && $w == 0){
                                            $name1 = $rosterData[$x]["person_name_given"];
                                            $name2 = $rosterData[$x]["person_name_family"];
                                            ?>
                                            <option value="<?=$rosterData[$x]['person_contact_email_primary']?>"><?=$name1?> <?=$name2?></option>
                                            <?php

                                        }
                                        $y=0;
                                        $x++;
                                    }

                                } else {
                                    $name = "No roster found";
                                    ?>
                                    <option><?=$name?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <input class="topicInput" id="topicInput" type="hidden" value="<?=$_GET['top']?>">
                        <div class="container assignButtons">
                            <div class="col-sm-1">
                                <?php
                                if($z >= $topic['num_allowed']) {
                                    ?>
                                    <button class="btn btn-success" type="submit" disabled>Save</button>
                                <?php
                                } else {
                                    ?>
                                    <button class="btn btn-success" type="submit">Save</button>
                                <?php
                                }
                                ?>

                            </div>
                            <div class="col-sm-1">
                                <a class="btn btn-danger" href="index.php">Cancel</a>
                            </div>
                        </div>
                        <?php
                        if($z >= $topic['num_allowed']) {
                            ?>
                            <div class="container assignButtons">
                            <p><i>The maximum number of students have been assigned to this topic.</i></p>
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
