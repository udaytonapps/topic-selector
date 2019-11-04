<?php
namespace TS\DAO;

class TS_DAO {

    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    function getTopics($link_id) {
        $query = "SELECT * FROM {$this->p}ts_topic WHERE link_id = :linkId order by topic_num;";
        $arr = array(':linkId' => $link_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function getTopicById($topic_id) {
        $query = "SELECT * FROM {$this->p}ts_topic WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id);
        return $this->PDOX->rowDie($query, $arr);
    }

    function createTopic($link_id, $topic_text, $num_allowed, $description) {
        $nextNumber = $this->getNexttopicNumber($link_id);
        $query = "INSERT INTO {$this->p}ts_topic (link_id, topic_num, topic_text, num_allowed, description) VALUES (:linkId, :topicNum, :topicText, :numAllowed, :description);";
        $arr = array(':linkId' => $link_id, ':topicNum' => $nextNumber, ':topicText' => $topic_text, ':numAllowed'=>$num_allowed, ":description"=>$description);
        $this->PDOX->queryDie($query, $arr);
        return $this->PDOX->lastInsertId();
    }

    function updateTopic($topic_id, $topic_text, $num_allowed, $description) {
        $query = "UPDATE {$this->p}ts_topic set topic_text = :topicText, num_allowed = :numAllowed, description = :description WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id, ':topicText' => $topic_text, ':numAllowed'=>$num_allowed, ":description"=>$description);
        $this->PDOX->queryDie($query, $arr);
    }

    function getNextTopicNumber($link_id) {
        $query = "SELECT MAX(topic_num) as lastNum FROM {$this->p}ts_topic WHERE link_id = :linkId";
        $arr = array(':linkId' => $link_id);
        $lastNum = $this->PDOX->rowDie($query, $arr)["lastNum"];
        return $lastNum + 1;
    }

    function deleteTopic($topic_id) {
        $query = "DELETE FROM {$this->p}ts_topic WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function fixUpTopicNumbers($link_id) {
        $query = "SET @topic_num = 0; UPDATE {$this->p}ts_topic set topic_num = (@topic_num:=@topic_num+1) WHERE link_id = :linkId ORDER BY topic_num";
        $arr = array(':linkId' => $link_id);
        $this->PDOX->queryDie($query, $arr);
    }

    function updateTopicNumber($topic_id, $new_number) {
        $query = "UPDATE {$this->p}ts_topic set topic_num = :topicNumber WHERE topic_id = :topicId;";
        $arr = array(':topicId' => $topic_id, ':topicNumber' => $new_number);
        $this->PDOX->queryDie($query, $arr);
    }

    function getNumberReservedForTopic($topic_id) {
        $query = "SELECT COUNT(*) as num_reserved FROM {$this->p}ts_selection WHERE topic_id = :topicId";
        $arr = array(':topicId' => $topic_id);
        return $this->PDOX->rowDie($query, $arr)["num_reserved"];
    }

    function getTotalReserved($link_id) {
        $query = "SELECT COUNT(*) as num_reserved FROM {$this->p}ts_selection WHERE topic_id in 
                    (SELECT topic_id FROM {$this->p}ts_topic where link_id = :linkId)";
        $arr = array(':linkId' => $link_id);
        return $this->PDOX->rowDie($query, $arr)["num_reserved"];
    }

    function findInstructors($context_id) {
        $query = "SELECT user_id FROM {$this->p}lti_membership WHERE context_id = :context_id AND (role = '1000' OR role = '5000');";
        $arr = array(':context_id' => $context_id);
        return $this->PDOX->allRowsDie($query, $arr);
    }

    function findEmail($user_id) {
        $query = "SELECT email FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["email"];
    }

    function findDisplayName($user_id) {
        $query = "SELECT displayname FROM {$this->p}lti_user WHERE user_id = :user_id;";
        $arr = array(':user_id' => $user_id);
        $context = $this->PDOX->rowDie($query, $arr);
        return $context["displayname"];
    }
}
