<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

$displayname = $USER->displayname;

include("menu.php");

$stuTops = $LAUNCH->link->settingsGet("stu_topics", "1");

$currentTime = new DateTime('now', new DateTimeZone($CFG->timezone));
$currentTime = $currentTime->format("Y-m-d H:i:s");

$topicST = $PDOX->prepare("SELECT * FROM {$p}ts_topic WHERE topic_id = :topicId");
$topicST->execute(array(":topicId" => $_GET['top']));
$topic = $topicST->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $USER->instructor) {
    $userEmail = isset($_POST["stuReserve"]) ? $_POST["stuReserve"] : "";

    if (trim($userEmail) === '') {
        $_SESSION['error'] = 'You are unable to select this user because their email address is blank.';
        header('Location: ' . addSession('index.php?top=true'));
    } else {
        $userFirstName = " ";
        $userLastName = " ";

        $hasRosters = LTIX::populateRoster(false);
        $x = 0;
        if ($hasRosters) {
            $rosterData = $GLOBALS['ROSTER']->data;
            foreach ($rosterData as $roster) {
                if ($rosterData[$x]['person_contact_email_primary'] == $userEmail) {
                    $userFirstName = $rosterData[$x]['person_name_given'];
                    $userLastName = $rosterData[$x]['person_name_family'];
                    break;
                }
                $x++;
            }
        }
        $newSelect = $PDOX->prepare("INSERT INTO {$p}ts_selection (topic_id, user_email, user_first_name, user_last_name, date_selected) 
                                        values (:topicId, :userEmail, :userFirstName, :userLastName, :dateSelected)");
        $newSelect->execute(array(
            ":topicId" => $_GET['top'],
            ":userEmail" => $userEmail,
            ":userFirstName" => $userFirstName,
            ":userLastName" => $userLastName,
            ":dateSelected" => $currentTime,
        ));

        $_SESSION['success'] = 'Student assigned successfully.';
        header('Location: ' . addSession('index.php?top=true'));
    }
} else {
    $OUTPUT->header();

    $OUTPUT->bodyStart();

    $OUTPUT->topNav($menu);

    echo '<div class="container-fluid">';

    $OUTPUT->flashMessages();

    if ($USER->instructor) {
        $name = '';
        ?>
        <a href="results-assignments.php"><span class="fas fa-chevron-left" aria-hidden="true"></span> Back to Topic
            Selections</a>
        <h3 class="small-hdr"><small>Topic Title</small></h3>
        <h3 class="sub-hdr"><?= $topic["topic_text"] ?></h3>
        <form method="post">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="stuReserve">Assign Student</label>
                        <select class="form-control" id="stuReserve" name="stuReserve">
                            <?php
                            $selectionST = $PDOX->prepare("SELECT * FROM {$p}ts_selection");
                            $selectionST->execute(array());
                            $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);

                            $top_num = 0;
                            foreach ($topic as $top) {
                                $top_num++;
                            }
                            $hasRosters = LTIX::populateRoster(false);
                            $x = 0;
                            $z = 0;
                            if ($hasRosters) {
                                $rosterData = $GLOBALS['ROSTER']->data;
                                usort($rosterData, "sortByName");
                                foreach ($rosterData as $roster) {
                                    $y = 0;
                                    $z = 0;
                                    $w = 0;
                                    foreach ($selections as $select) {
                                        if ($rosterData[$x]['person_contact_email_primary'] == $select['user_email']) {
                                            $y++;
                                            if ($select['topic_id'] == $_GET['top']) {
                                                $w++;
                                            }
                                        }
                                        if ($select['topic_id'] == $_GET['top']) {
                                            $z++;
                                        }
                                    }
                                    if ($roster["roles"] == "Learner" && $y < $top_num && $w == 0 && $y < $stuTops) {
                                        $name1 = $rosterData[$x]["person_name_given"];
                                        $name2 = $rosterData[$x]["person_name_family"];
                                        ?>
                                        <option value="<?= $rosterData[$x]['person_contact_email_primary'] ?>"><?= $name1 ?> <?= $name2 ?></option>
                                        <?php
                                    }
                                    $y = 0;
                                    $x++;
                                }
                            } else {
                                $name = "No roster found";
                                ?>
                                <option><?= $name ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <input class="topicInput" id="topicInput" type="hidden" value="<?= $_GET['top'] ?>">
            <?php
            if ($z >= $topic['num_allowed'] || $name == "No roster found") {
                ?>
                <button class="btn btn-primary btn-disabled" type="submit" disabled>Save</button>
                <?php
            } else {
                ?>
                <button class="btn btn-primary" type="submit">Save</button>
                <?php
            }
            if ($z >= $topic['num_allowed']) {
                ?>
                <p class="alert alert-warning">The maximum number of students have been assigned to this topic.</p>
                <?php
            }
            ?>
        </form>
        <?php
    }

    echo '</div>';// end container

    $OUTPUT->footerStart();
    $OUTPUT->footerEnd();
}
function sortByName($a, $b)
{
    return strcmp($a["person_name_family"], $b["person_name_family"]);
}
