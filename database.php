<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array( "{$CFG->dbprefix}topic_build",
        "create table {$CFG->dbprefix}topic_build (
    list_id       INTEGER NOT NULL AUTO_INCREMENT,
    link_id       INTEGER NOT NULL,
    stu_reserve   BOOL,
    allow_stu     BOOL,
    
    PRIMARY KEY(list_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}topic",
        "create table {$CFG->dbprefix}topic (
    topic_id          INTEGER NOT NULL AUTO_INCREMENT,
    list_id           INTEGER NOT NULL,
    topic_num         INTEGER NOT NULL,
    topic_text        TEXT NULL,
    num_allowed       INTEGER NOT NULL,
    num_reserved      INTEGER NOT NULL,

    CONSTRAINT `{$CFG->dbprefix}topic`
        FOREIGN KEY (`list_id`)
        REFERENCES `{$CFG->dbprefix}topic_build` (`list_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(topic_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array( "{$CFG->dbprefix}selection",
        "create table {$CFG->dbprefix}selection (
    select_id         INTEGER NOT NULL AUTO_INCREMENT,
    topic_id          INTEGER NOT NULL,
    user_email        TEXT NULL,
    user_first_name   TEXT NULL,
    user_last_name    TEXT NULL,
    date_selected     datetime NOT NULL,

    CONSTRAINT `{$CFG->dbprefix}selection`
        FOREIGN KEY (`topic_id`)
        REFERENCES `{$CFG->dbprefix}topic` (`topic_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(select_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);