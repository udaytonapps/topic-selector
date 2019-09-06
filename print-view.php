<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

include("menu.php");

$topicsST  = $PDOX->prepare("SELECT * FROM {$p}topic_list WHERE link_id = :linkId");
$topicsST->execute(array(":linkId" => $LINK->id));
$topics = $topicsST->fetch(PDO::FETCH_ASSOC);

$topicST  = $PDOX->prepare("SELECT * FROM {$p}topic WHERE list_id = :listId");
$topicST->execute(array(":listId" => $topics['list_id']));
$topic = $topicST->fetchAll(PDO::FETCH_ASSOC);

$OUTPUT->header();
?>
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);


$OUTPUT->flashMessages();

$title = $LAUNCH->link->settingsGet("title", false);

if (!$title) {
    $LAUNCH->link->settingsSet("title", $LAUNCH->link->title);
    $title = $LAUNCH->link->title;
}

$title = $title . " - Print View";

$OUTPUT->pageTitle($title, false, false);

echo '</div>';// end container

?>
<div>
    <div class="col-xs-10 col-sm-10 col-md-10 col-lg-10 col-xl-10"></div>
    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 col-xl-2">
        <a class="print_link" href="#" onclick="printList()"><img class="printer_icon" src="styles/printer_icon.png">Print Results</a>
    </div>
    <div id="printArea" class="printArea">
        <div class="container topicView">
            <?php
            $count1=0;
            foreach($topic as $tops) {
                $selectionST  = $PDOX->prepare("SELECT * FROM {$p}selection WHERE topic_id = :topicId");
                $selectionST->execute(array(":topicId" => $tops['topic_id']));
                $selections = $selectionST->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="container">
                    <span ><b><?=$tops['topic_text']?>: </b></span>
                    <?php
                    foreach($selections as $select) {
                        ?>
                        <p class="printStu"><?=$select['user_first_name']?> <?=$select['user_last_name']?></p>
                        <?php
                    }
                    ?>
                </div>
                <?php
                $count1++;
            }
            ?>
        </div>
    </div>
</div>
<?php
$OUTPUT->footerStart();
?>
    <script type="text/javascript">
        function printList() {
            let printPage = document.getElementById('printArea');
            let printView = window.open('', '', 'width=1100, height=850');
            printView.document.open();
            printView.document.write(printPage.innerHTML);
            printView.document.write('<html><link rel="stylesheet" href="styles/main.css" /></head><body onload="window.print()"></html><style type="text/css" media="print">@page { size: portrait; }</style>');
            printView.document.close();
        }
    </script>
<?php
$OUTPUT->footerEnd();
