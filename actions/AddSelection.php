<?php
require_once "../../config.php";
require_once('../dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

$t_buildST  = $PDOX->prepare("SELECT * FROM {$p}topic_build WHERE link_id = :linkId");
$t_buildST->execute(array(":linkId" => $LINK->id));
$t_build = $t_buildST->fetch(PDO::FETCH_ASSOC);

$hasRosters = LTIX::populateRoster(false);
$topics = $TS_DAO->getTopics($t_build['list_id']);

$userFirstName = '';
$userLastName = '';
$x = 0;
if ($hasRosters) {
    $rosterData = $GLOBALS['ROSTER']->data;
    foreach ($rosterData as $roster){
        if($rosterData[$x]['person_contact_email_primary'] == $_GET['user_email']){
            $userFirstName = $rosterData[$x]['person_name_given'];
            $userLastName = $rosterData[$x]['person_name_family'];
            break;
        }
        $x++;
    }
} else {
    $_SESSION['error'] = 'Topic not assigned: Unable to load class roster';
    header('Location: ' . addSession('../index.php'));
}

$newSelect = $PDOX->prepare("INSERT INTO {$p}selection (topic_id, user_email, user_first_name, user_last_name, date_selected)
                                                       values (:topicId, :userEmail, :userFirstName, :userLastName, :dateSelected)");
$newSelect->execute(array(
    ":topicId" => $_GET['topic'],
    ":userEmail" => $_GET['user_email'],
    ":userFirstName" => $userFirstName,
    ":userLastName" => $userLastName,
    ":dateSelected" => $currentTime,
));

$numReserved = $topic['num_reserved'];
$numReserved++;

$newTopic = $PDOX->prepare("UPDATE {$p}topic SET num_reserved=:numReserved WHERE topic_id = :topicId");
$newTopic->execute(array(
    ":topicId" => $_GET['topic'],
    ":numReserved" => $numReserved,
));

$_SESSION['success'] = 'Student assigned successfully.';
header('Location: ' . addSession('../index.php'));