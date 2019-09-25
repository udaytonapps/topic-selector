<?php
require_once "../config.php";
require_once('dao/TS_DAO.php');

use \Tsugi\Core\LTIX;
use \Tsugi\UI\SettingsForm;
use \TS\DAO\TS_DAO;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

$TS_DAO = new TS_DAO($PDOX, $p);

$topics = $TS_DAO->getTopics($LINK->id);

if (SettingsForm::isSettingsPost()) {
    if (!isset($_POST["title"]) || trim($_POST["title"]) === '') {
        $_SESSION["error"] = __('Title cannot be blank.');
    } else {
        SettingsForm::handleSettingsPost();
        $_SESSION["success"] = __('All settings saved.');
    }
    header('Location: '.addSession('index.php'));
    return;
}

$title = $LAUNCH->link->settingsGet("title", false);

if (!$title) {
    $LAUNCH->link->settingsSet("title", $LAUNCH->link->title);
    $title = $LAUNCH->link->title;
}

$stu_topics = $LAUNCH->link->settingsGet("stu_topics", false);

if(!$stu_topics) {
    $LAUNCH->link->settingsSet("stu_topics", $LAUNCH->link->stu_topics);
    $stuTops = $LAUNCH->link->stu_topics;
}

SettingsForm::start();
SettingsForm::text('title',__('Tool Title'));
SettingsForm::text('stu_topics',__('Number of Topics Each Student Can be Assigned To'));
SettingsForm::checkbox('stu_allowed',__('Students Can Select Their Own Topics'));
SettingsForm::end();

include("menu.php");

$OUTPUT->header();
?>
    <link rel="stylesheet" type="text/css" href="style/topicselector.css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($title, true, true);

echo '<p class="lead">Create the topics that students can sign up for. You can choose the number of students that may sign up for a topic. 
<br> The number of topics that a student my sign up for defaults to 1, but can be changed in Settings</p>';

?>
    <section id="theTopics">
        <?php
        foreach ($topics as $topic) {
            ?>
            <div id="topicRow<?=$topic["topic_id"]?>" class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row" data-topic-number="<?=$topic['topic_num']?>">
                <div class="topic-number"><?=$topic["topic_num"]?>.</div>
                <div class="flx-grow-all topic-text">
                    <span class="topic-text-span" onclick="editTopicText(<?=$topic["topic_id"]?>)" id="topicText<?=$topic["topic_id"]?>" tabindex="0"> <?=$topic["topic_text"]?> - <?=$topic["num_allowed"]?></span>
                    <form id="topicTextForm<?=$topic["topic_id"]?>" onsubmit="return confirmDeleteTopicBlank(<?=$topic["topic_id"]?>)" action="actions/AddOrEditTopic.php" method="post" style="display:none;">
                        <input type="hidden" name="topicId" value="<?=$topic["topic_id"]?>">
                        <label for="topicTextInput<?=$topic["topic_id"]?>" class="sr-only">Topic Text</label>
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <input class="form-control" id="topicTextInput<?=$topic["topic_id"]?>" name="topicText" placeholder="<?=$topic["topic_text"]?>" required>
                        </div>
                        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                            <input class="form-control" type="number" id="topicStuAllowed<?=$topic["topic_id"]?>" name="num_allowed" value="<?=$topic["num_allowed"]?>">
                        </div>
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
        }
        ?>
        <div id="newTopicRow" class="h3 inline flx-cntnr flx-row flx-nowrap flx-start topic-row" style="display:none;" data-topic-number="<?=$topics ? count($topics)+1 : 1?>">
            <div id="newTopicNumber"><?=$topics ? count($topics)+1 : 1?>.</div>
            <div class="flx-grow-all topic-text">
                <form id="topicTextForm-1" action="actions/AddOrEditTopic.php" method="post">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <input type="hidden" name="topicId" value="-1">
                        <label for="topicTextInput-1" class="sr-only">Topic Text</label>
                        <textarea class="form-control" id="topicTextInput-1" name="topicText" placeholder="Topic Title" required></textarea>
                    </div>
                    <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                        <label class="form-label" for="topicStuAllowed" >Number of Students Allowed:</label>
                    </div>
                    <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
                        <input class="form-control" type="number" id="topicStuAllowed" name="num_allowed" value="1">
                    </div>
                </form>
            </div>
            <a id="topicSaveAction-1" href="javascript:void(0);">
                <span aria-hidden="true" class="fa fa-fw fa-save"></span>
                <span class="sr-only">Save Topic</span>
            </a>
            <a id="topicCancelAction-1" href="javascript:void(0);">
                <span aria-hidden="true" class="fa fa-fw fa-times"></span>
                <span class="sr-only">Cancel Topic</span>
            </a>
        </div>
    </section>
    <section id="addTopics">
        <span class="h3"><a href="javascript:void(0);" id="addTopicLink" onclick="showNewTopicRow();" class="btn btn-success"><span class="fa fa-plus" aria-hidden="true"></span> Add Topic</a></span>
    </section>
    </div>

    <input type="hidden" id="sess" value="<?php echo($_GET["PHPSESSID"]) ?>">
<?php

$OUTPUT->helpModal("Example Help Modal", __('
                        <h4>Help Content</h4>
                        <p>Use this modal to add help to the current page.</p>'));

$OUTPUT->footerStart();
?>
<script src="scripts/topicselector.js" type="text/javascript"></script><br>
<?php

$OUTPUT->footerEnd();