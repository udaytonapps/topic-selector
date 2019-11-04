<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array( "{$CFG->dbprefix}ts_topic",
        "create table {$CFG->dbprefix}ts_topic (
    topic_id          INTEGER NOT NULL AUTO_INCREMENT,
    link_id           INTEGER NOT NULL,
    topic_num         INTEGER NOT NULL,
    topic_text        TEXT NULL,
    description       TEXT NULL,
    num_allowed       INTEGER NOT NULL,

    PRIMARY KEY(topic_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}ts_selection",
        "create table {$CFG->dbprefix}ts_selection (
    select_id         INTEGER NOT NULL AUTO_INCREMENT,
    topic_id          INTEGER NOT NULL,
    user_email        TEXT NULL,
    user_first_name   TEXT NULL,
    user_last_name    TEXT NULL,
    date_selected     datetime NOT NULL,

    CONSTRAINT `{$CFG->dbprefix}ts_selection`
        FOREIGN KEY (`topic_id`)
        REFERENCES `{$CFG->dbprefix}ts_topic` (`topic_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(select_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);