<?php
require_once '../../../config.php';

$sharedpanelid= $_POST['n'];
$id=            $_POST['id']; // course module id
$cameracomment= htmlspecialchars($_POST['cameracomment'],ENT_QUOTES);
$name=          htmlspecialchars($_POST['name'],ENT_QUOTES);

header("Refresh: 3; URL=".$CFG->wwwroot."/mod/sharedpanel/view.php?id=".$id);
?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php
if ($CFG->dbtype=="mysqli"){
    $dbtype="mysql";
}else{
    $dbtype=$CFG->dbtype;
}
//    $db = new PDO('mysql:dbname=moodle;host=localhost', $CFG->dbuser, $CFG->dbpass);
$db= new PDO($dbtype.':dbname='.$CFG->dbname.';host='.$CFG->dbhost, $CFG->dbuser, $CFG->dbpass);
$db->query("SET NAMES utf8");
$sql = "SELECT max(id) FROM ".$CFG->prefix."sharedpanel_cards;";
foreach ($db->query($sql) as $row) {
    $cardid= $row['max(id)']+1;
}
$sql2= "insert into ".$CFG->prefix."sharedpanel_cards ".
"(id, sharedpanelid, userid, rating, content, comment, hidden, timeposted, timecreated, timemodified, inputsrc, messageid, sender, positionx, positiony)".
" values (?" . str_repeat(", ?",14) . ")";
//    " values (?, ?, ?)";
$stmt = $db->prepare($sql2);

$time= time();
$z= 0;
$s= "";
$inputsrc= "camera";

if (is_uploaded_file($_FILES["capture"]["tmp_name"])){
//    move_uploaded_file($_FILES["capture"]["tmp_name"], $_FILES["capture"]["name"]);
//    echo '<a target="_blank" href="'. $_FILES["capture"]["name"] . '">view file</a><br />';

    $ret1= "";
    if ($cameracomment){  $ret1 .= $cameracomment."<br/><br/>";  }
//    $ret1 .= "<img src='data:image/gif;base64,".base64_encode(file_get_contents($_FILES["capture"]["tmp_name"]))."' width=85%><br/>";
    $ret1 .= "<img src='data:image/gif;base64,";
//    $ret1 .= base64_encode(file_get_contents($_FILES["capture"]["tmp_name"]));
    $ret1 .= rotatecompress_img( $_FILES["capture"]["tmp_name"], 600 );
    $ret1 .= "' width=85%><br/>";

//    $fp = fopen($_FILES['file']['tmp_name'], 'rb');
$stmt->bindParam(1, $cardid);
$stmt->bindParam(2, $sharedpanelid); // sharedpanelid
$stmt->bindParam(3, $z); //userid
$stmt->bindParam(4, $z); // rating
$stmt->bindParam(5, $cardid); // content
//$stmt->bindParam(5, $fp, PDO::PARAM_LOB); // content
$stmt->bindParam(5, $ret1); // content
$stmt->bindParam(6, $s); // comment
$stmt->bindParam(7, $z); // hidden
$stmt->bindParam(8, $time); //post
$stmt->bindParam(9, $time); //create
$stmt->bindParam(10, $time); // modify
$stmt->bindParam(11, $inputsrc); // inputsrc
$stmt->bindParam(12, $z); // messageid
$stmt->bindParam(13, $name); //sender
$stmt->bindParam(14, $z); // positionx 
$stmt->bindParam(15, $z); // positiony
//$stmt->bindParam(3, $fp, PDO::PARAM_LOB);
//$stmt->bindParam(2, $_FILES['file']['type']);
    $db->beginTransaction();
    $stmt->execute();
    $db->commit();
    echo "アップロードに成功しました<br />";
}else{
//    echo "アップロードするファイルがありません<br />";
    if ($cameracomment!=""){
      $ret1= $cameracomment;
//      $fp = fopen($_FILES['file']['tmp_name'], 'rb');
$stmt->bindParam(1, $cardid);
$stmt->bindParam(2, $sharedpanelid); // sharedpanelid
$stmt->bindParam(3, $z); //userid
$stmt->bindParam(4, $z); // rating
$stmt->bindParam(5, $cardid); // content
//$stmt->bindParam(5, $fp, PDO::PARAM_LOB); // content
$stmt->bindParam(5, $ret1); // content
$stmt->bindParam(6, $s); // comment
$stmt->bindParam(7, $z); // hidden
$stmt->bindParam(8, $time); //post
$stmt->bindParam(9, $time); //create
$stmt->bindParam(10, $time); // modify
$stmt->bindParam(11, $inputsrc); // inputsrc
$stmt->bindParam(12, $z); // messageid
$stmt->bindParam(13, $name); //sender
$stmt->bindParam(14, $z); // positionx 
$stmt->bindParam(15, $z); // positiony
      $db->beginTransaction();
      $stmt->execute();
      $db->commit();
      echo "メッセージを登録しました<br />";
    }
}

echo "<br/><a href='../view.php?id=$id'>カード一覧を表示する</a>";
?>

</body>
</html>

<?php
function rotatecompress_img($imgname, $width){
//  $imagea= imap_base64($attached);
//  $imagea= $jpgfile;
//  $imagea= imagecreatefromstring($imagea);
  $imagea= imagecreatefromjpeg($imgname);

  // http://blog.diginnovation.com/archives/1104/
  $exif_data = exif_read_data($imgname);
  if(isset($exif_data['Orientation']) && $exif_data['Orientation'] == 6){
    $imagea = imagerotate($imagea, 270, 0);
  }

  $imagea= imagescale($imagea, $width, -1);  // proportionally compress image with $width
  $jpegfile= tempnam("/tmp", "email-jpg-");
  imagejpeg($imagea,$jpegfile);
  imagedestroy($imagea);
  $attached= base64_encode(file_get_contents($jpegfile));
  unlink($jpegfile);
  return $attached;
}
?>
