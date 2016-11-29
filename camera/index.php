<!DOCTYPE HTML>
<!-- Thanks to http://labs.opentone.co.jp/?p=4051 -->
<!-- Thanks to http://qiita.com/yasumodev/items/c9f8e8f588ded6b179c9 -->
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.2">
<title>写真を投稿</title>
</head>

<style>
label {
  color: white;  
  background-color: orange;
  padding: 16px;
  line-height: 80px;
  border-radius: 10px;
  font-size: 30px;
}
</style>

<?php
if (!$_GET['n']){
  echo "SharedPanel ID が必要です";
  exit;
}
?>

<body>
    <form action="cameraupload.php" method="post" enctype="multipart/form-data">
    <label for="capture">
       ＋写真を撮る
           <input type="file" id="capture" name="capture" accept="image/*" capture="camera" style="display:none;" />
    </label>
      <br/><br/>
      メッセージ: <br/> <textarea name="cameracomment" style='height:5em;'></textarea><br/>
      名前<span style='font-size:70%;'>（任意）</span>: <br/> <input type="text" name="name" /><br/>
      <br/>
      <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
      <input type="hidden" name="n"  value="<?php echo $_GET['n']; ?>" />
      <input type="submit" value="撮った写真をアップロード" />
    </form>

<?php echo "<br/><span style='font-size:70%;'><a href='../view.php?id=".$_GET['id']."'>カード一覧を表示する</a></span>"; ?>

</body>
</html>
