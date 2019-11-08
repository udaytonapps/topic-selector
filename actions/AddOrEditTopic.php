<?php
require_once "../../config.php";
require_once('../dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;
$TS_DAO = new TS_DAO($PDOX, $p);

if ($USER->instructor) {

    $result = array();

    $topicId = $_POST["topicId"];
    $topicText = $_POST["topicText"];
    $numAllowed = $_POST["num_allowed"];
    $topicDescription = isset($_POST["topicDescription"]) ? $_POST["topicDescription"] : '';

    if (isset($topicText) && trim($topicText) != '') {
        if ($topicId > -1) {
            // Existing topic
            $TS_DAO->updateTopic($topicId, $topicText, $numAllowed, $topicDescription);
        } else {
            // New topic
            $topicId = $TS_DAO->createTopic($LINK->id, $topicText, $numAllowed, $topicDescription);

            $topic = $TS_DAO->getTopicById($topicId);

            // Create new topic markup
            ob_start();
            ?>
            <div id="topicRow<?= $topic["topic_id"] ?>"
                 class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row"
                 data-topic-number="<?= $topic['topic_num'] ?>">
                <div class="topic-text flx-basis-0" style="flex:5">
                    <div class="flx-cntnr topic-text-span" style="align-items: center;"
                         onclick="editTopicText(<?= $topic["topic_id"] ?>)"
                         id="topicText<?= $topic["topic_id"] ?>" tabindex="0">
                        <div class="flx-basis-0" style="flex:2">
                            <p class="topic-title"><?= $topic["topic_text"] ?></p>
                        </div>
                        <div class="flx-basis-0" style="flex:1">
                            <p class="topic-slots"><?= $topic["num_allowed"] ?></p>
                        </div>
                        <div class="flx-basis-0" style="flex:2">
                            <p class="topic-description h5 inline" style="margin-bottom:0.5rem;"><?= $topic["description"] ?></p>
                        </div>
                    </div>
                    <form id="topicTextForm<?= $topic["topic_id"] ?>"
                          onsubmit="return confirmDeleteTopicBlank(<?= $topic["topic_id"] ?>)"
                          action="actions/AddOrEditTopic.php?PHPSESSID=<?=$_POST["PHPSESSID"]?>" method="post" style="display:none;">
                        <input type="hidden" name="topicId" value="<?= $topic["topic_id"] ?>">
                        <div class="flx-cntnr">
                            <div class="flx-basis-0" style="flex:2">
                                <label for="topicTextInput<?= $topic["topic_id"] ?>" class="sr-only">Topic
                                    Text</label>
                                <input class="form-control" style="width: 90%;"
                                       id="topicTextInput<?= $topic["topic_id"] ?>"
                                       name="topicText" value="<?= $topic["topic_text"] ?>" required>
                            </div>
                            <div class="flx-basis-0" style="flex:1">
                                <label for="topicStuAllowed<?= $topic["topic_id"] ?>" class="sr-only">Slots
                                    Available</label>
                                <input class="form-control" type="number" style="width: 80%;min-width:84px;"
                                       id="topicStuAllowed<?= $topic["topic_id"] ?>"
                                       name="num_allowed" value="<?= $topic["num_allowed"] ?>">
                            </div>
                            <div class="flx-basis-0" style="flex:2">
                                <label for="topicDescription<?= $topic["topic_id"] ?>" class="sr-only">Topic
                                    Description</label>
                                <textarea class="form-control" id="topicDescription<?= $topic["topic_id"] ?>"
                                          name="topicDescription" rows="2"><?= $topic["description"] ?></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="actions flx-basis-0 flx-grow-1 text-right">
                    <a id="topicEditAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                       onclick="editTopicText(<?= $topic["topic_id"] ?>)">
                        <span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
                        <span class="sr-only">Edit Topic Text</span>
                    </a>
                    <a id="topicReorderAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                       onclick="moveTopicUp(<?= $topic["topic_id"] ?>)">
                        <span class="fa fa-fw fa-chevron-circle-up" aria-hidden="true"></span>
                        <span class="sr-only">Move Topic Up</span>
                    </a>
                    <a id="topicDeleteAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                       onclick="deleteTopic(<?= $topic["topic_id"] ?>)">
                        <span aria-hidden="true" class="fa fa-fw fa-trash"></span>
                        <span class="sr-only">Delete Topic</span>
                    </a>
                    <a id="topicSaveAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                       style="display:none;">
                        <span aria-hidden="true" class="fa fa-fw fa-save"></span>
                        <span class="sr-only">Save Topic</span>
                    </a>
                    <a id="topicCancelAction<?= $topic["topic_id"] ?>" href="javascript:void(0);"
                       style="display: none;">
                        <span aria-hidden="true" class="fa fa-fw fa-times"></span>
                        <span class="sr-only">Cancel Topic</span>
                    </a>
                </div>
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

