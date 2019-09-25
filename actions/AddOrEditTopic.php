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

if ($USER->instructor) {

    $result = array();

    $topicId = $_POST["topicId"];
    $topicText = $_POST["topicText"];
    $numAllowed = $_POST["num_allowed"];

    if (isset($topicText) && trim($topicText) != '') {
        if ($topicId > -1) {
            // Existing topic
            $TS_DAO->updateTopic($topicId, $topicText, $numAllowed);
        } else {
            // New topic
            $topicId = $TS_DAO->createTopic($t_build["list_id"], $topicText, $numAllowed);

            $topic = $TS_DAO->getTopicById($topicId);

            // Create new topic markup
            ob_start();
            ?>
            <div id="topicRow<?=$topic["topic_id"]?>" class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row" data-topic-number="<?=$topic["topic_num"]?>">
                <div class="topic-number"><?=$topic["topic_num"]?>.</div>
                <div class="flx-grow-all topic-text">
                    <span class="topic-text-span" onclick="editTopicText(<?=$topic["topic_id"]?>)" id="topicText<?=$topic["topic_id"]?>"><?= $topic["topic_text"] ?></span>
                    <form id="topicTextForm<?=$topic["topic_id"]?>" onsubmit="return confirmDeleteTopicBlank(<?=$topic["topic_id"]?>)" action="AddOrEditTopic.php" method="post" style="display:none;">
                        <input type="hidden" name="topicId" value="<?=$topic["topic_id"]?>">
                        <label for="topicTextInput<?=$topic["topic_id"]?>" class="sr-only">Topic Text</label>
                        <textarea class="form-control" id="topicTextInput<?=$topic["topic_id"]?>" name="topicText" rows="2" required><?=$topic["topic_text"]?></textarea>
                    </form>
                </div>
                <a id="topicEditAction<?=$topic["topic_id"]?>" href="javascript:void(0);" onclick="editTopicText(<?=$topic["topic_id"]?>)">
                    <span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
                    <span class="sr-only">Edit Topic Text</span>
                </a>
                <a id="topicReorderAction<?=$topic["topic_id"]?>" href="javascript:void(0);" onclick="moveTopicUp(<?=$topic["topic_id"]?>)">
                    <span class="fa fa-fw fa-chevron-circle-up" aria-hidden="true"></span>
                    <span class="sr-only">Move Topic Up</span>
                </a>
                <a id="topicDeleteAction<?=$topic["topic_id"]?>" href="javascript:void(0);" onclick="deleteTopic(<?=$topic["topic_id"]?>)">
                    <span aria-hidden="true" class="fa fa-fw fa-trash"></span>
                    <span class="sr-only">Delete Topic</span>
                </a>
                <a id="topicSaveAction<?=$topic["topic_id"]?>" href="javascript:void(0);" style="display:none;">
                    <span aria-hidden="true" class="fa fa-fw fa-save"></span>
                    <span class="sr-only">Save Topic</span>
                </a>
                <a id="topicCancelAction<?=$topic["topic_id"]?>" href="javascript:void(0);" style="display: none;">
                    <span aria-hidden="true" class="fa fa-fw fa-times"></span>
                    <span class="sr-only">Cancel Topic</span>
                </a>
            </div>
            <?php
            $result["new_topic"] = ob_get_clean();
        }
        $_SESSION['success'] = 'Topic Saved.';
    } else {
        if ($topicId > -1) {
            // Blank text means delete topic
            $TS_DAO->deleteTopic($topicId);
            // Set topic id to false to remove topic line
            $topicId = false;
            $_SESSION['success'] = 'Topic Deleted.';
        } else {
            $_SESSION['error'] = 'Unable to save blank topic.';
        }
    }

    $OUTPUT->buffer=true;
    $result["flashmessage"] = $OUTPUT->flashMessages();

    header('Content-Type: application/json');

    echo json_encode($result, JSON_HEX_QUOT | JSON_HEX_TAG);

    exit;
} else {
    header( 'Location: '.addSession('../student-home.php') ) ;
}

