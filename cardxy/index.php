<?php
require_once '../../../config.php';

//$sharedpanelid= $_POST['n'];
$data= json_decode($_POST['data']);

//file_put_contents('/tmp/tt',$_POST['data']);

//header("Refresh: 5; URL=".$CFG->wwwroot."/mod/sharedpanel/view.php?id=".$id);

/*
echo '
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
';
*/

if ($CFG->dbtype=="mysqli"){
    $dbtype="mysql";
}else{
    $dbtype=$CFG->dbtype;
}
//    $db = new PDO('mysql:dbname=moodle;host=localhost', $CFG->dbuser, $CFG->dbpass);
$db= new PDO($dbtype.':dbname='.$CFG->dbname.';host='.$CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
$db->query("SET NAMES utf8");

//$sql = "SELECT max(id) FROM ".$CFG->prefix."sharedpanel_cards;";
//foreach ($db->query($sql) as $row) {
//    $cardid= $row['max(id)']+1;
//}

$time= time();

$sql= "update ".$CFG->prefix."sharedpanel_cards ".
 "set timemodified=?, positionx=?, positiony=? ".
 " where id = ?";
$stmt = $db->prepare($sql);

foreach($data as $d){
  preg_match('/^card(\d+)/',$d->cardid,$ma); $id= $ma[1];
//  file_put_contents('/tmp/tt',$id);
  $stmt->bindParam(1, $time);
  $stmt->bindParam(2, $d->positionx);
  $stmt->bindParam(3, $d->positiony);
  $stmt->bindParam(4, $id);
  $db->beginTransaction();
  $stmt->execute();
  $db->commit();
}

// Group Cards
$sql2= "update ".$CFG->prefix."sharedpanel_gcards ".
 "set timemodified=?, positionx=?, positiony=? ".
 " where id = ?";
$stmt2 = $db->prepare($sql2);

foreach($data as $d){
  preg_match('/^gcard(\d+)/',$d->cardid,$ma); $id= $ma[1];
//  file_put_contents('/tmp/tt',$id);
  $stmt2->bindParam(1, $time);
  $stmt2->bindParam(2, $d->positionx);
  $stmt2->bindParam(3, $d->positiony);
  $stmt2->bindParam(4, $id);
  $db->beginTransaction();
  $stmt2->execute();
  $db->commit();
}

//echo "<br/><a href='../view.php?id=$id'>カード一覧を表示する</a>";

/*
</body>
</html>
*/

?>
