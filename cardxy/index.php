<?php
require_once '../../../config.php';

$data = required_param('data', PARAM_TEXT);
$data = json_decode($data);

require_login();

if ($CFG->dbtype == "mysqli") {
    $dbtype = "mysql";
} else {
    $dbtype = $CFG->dbtype;
}

$db = new PDO($dbtype . ':dbname=' . $CFG->dbname . ';host=' . $CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
$db->query("SET NAMES utf8");

$time = time();

$sql = "update " . $CFG->prefix . "sharedpanel_cards " .
    "set timemodified=?, positionx=?, positiony=? " .
    " where id = ?";
$stmt = $db->prepare($sql);

foreach ($data as $d) {
    preg_match('/^card(\d+)/', $d->cardid, $ma);
    $id = $ma[1];
    $stmt->bindParam(1, $time);
    $stmt->bindParam(2, $d->positionx);
    $stmt->bindParam(3, $d->positiony);
    $stmt->bindParam(4, $id);
    $db->beginTransaction();
    $stmt->execute();
    $db->commit();
}

// Group Cards
$sql2 = "update " . $CFG->prefix . "sharedpanel_gcards " .
    "set timemodified=?, positionx=?, positiony=? " .
    " where id = ?";
$stmt2 = $db->prepare($sql2);

foreach ($data as $d) {
    preg_match('/^gcard(\d+)/', $d->cardid, $ma);
    $id = $ma[1];
    $stmt2->bindParam(1, $time);
    $stmt2->bindParam(2, $d->positionx);
    $stmt2->bindParam(3, $d->positiony);
    $stmt2->bindParam(4, $id);
    $db->beginTransaction();
    $stmt2->execute();
    $db->commit();
}
