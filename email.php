<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//メール ----------------------------------------------------------------------------
//エラー表示
//ini_set( 'display_errors', 1 );

require_once("locallib.php");

// 添付ファイル画像が複数付いているメールへの対応は不十分。 2016-0723

function add_card_from_email($sharedpanel, $compressimage = -1)
{
    global $DB, $USER;

    $maxnumatatime = 5;  // only $maxnumatatime tweets are imported at a time

    $GMAIL_ACCOUNT = $sharedpanel->emailadr1;
    $andkey = $sharedpanel->emailkey1;

    $key = 'くまモンくまモンくまモンくまモンくまもんくまモンくまもんくまモンくまモン１２１０';
    $GMAIL_PASSWORD = openssl_decrypt($sharedpanel->emailpas1, 'AES-128-ECB', $key);

    echo "<br/><hr>importing emails ($GMAIL_ACCOUNT; $andkey) ...  ";
    ob_flush();
    flush();

    if (preg_match('/([^@]+)@gmail[.]com/', $GMAIL_ACCOUNT, $ma)) {
        $GMAIL_HOST = 'imap.googlemail.com';
    } elseif (preg_match('/([^@]+)@yahoo[.]co[.]jp/', $GMAIL_ACCOUNT, $ma)) {
        $GMAIL_HOST = 'imap.mail.yahoo.co.jp';
    } elseif (preg_match('/([^@]+)@yahoo[.]com/', $GMAIL_ACCOUNT, $ma)) {
        $GMAIL_HOST = 'imap.mail.yahoo.com';
    } else {
        if (preg_match('/([^@]+)@([^@]+)/', $GMAIL_ACCOUNT, $ma)) {
            $GMAIL_HOST = 'imap.' . $ma[2];
        }
    }
    // echo $GMAIL_HOST; //debug

// 必要な定数を設定
    define('GMAIL_PORT', 993);
    define('SERVER', '{' . $GMAIL_HOST . ':' . GMAIL_PORT . '/novalidate-cert/imap/ssl}');
//define('SERVER','{'.$GMAIL_HOST.':'.GMAIL_PORT.'/imap/ssl}');

//    Gmail {imap.gmail.com:993/imap/ssl}INBOX
//    Yahoo {imap.mail.yahoo.co.jp:993/imap/ssl}INBOX
//    AOL {imap.aol.com:993/imap/ssl}INBOX

// メールボックスへの IMAP ストリームをオープン
    if (($mbox = imap_open(SERVER . "INBOX", $GMAIL_ACCOUNT, $GMAIL_PASSWORD)) == false) {
        echo "IMAP connection failed. <br/>";
        exit;
    }
// メールボックスの情報を取得
    $mboxes = imap_mailboxmsginfo($mbox);

    echo "... " . $mboxes->Nmsgs . " emails found... <br/>";
    ob_flush();
    flush();

// メッセージ数の有無
    if ($mboxes->Nmsgs != 0) {
        // 情報を格納する変数を初期化
        $mail = null;
        for ($mailno = 1; $mailno <= $mboxes->Nmsgs; $mailno++) {
            echo " $mailno ... ";
            ob_flush();
            flush();
            // ヘッダー情報の取得
            $head = imap_headerinfo($mbox, $mailno);
            // アドレスの取得
            $mail[$mailno]['address'] = $head->from[0]->mailbox . '@' . $head->from[0]->host;
            // evernote は処理中断
            if ($head->from[0]->host == "evernote.com") {
                continue;
            }
            // 日付
            $mail[$mailno]['date'] = $head->date;
            // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
            $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => strtotime($head->date), 'inputsrc' => 'email', 'sharedpanelid' => $sharedpanel->id, 'hidden' => 0));
            if ($samecard != null) {
                $mail[$mailno]['address'] = "xxx";
                echo "Not imported : same email as at " . $head->date . "<br>\n";
                ob_flush();
                flush();
                continue;
            }

            // タイトルの有無
            if (!empty($head->subject)) {
                // タイトルをデコード
                $mhead = imap_mime_header_decode($head->subject);
                foreach ($mhead as $key => $value) {
                    if ($value->charset != 'default') {
                        $mail[$mailno]['subject'] = mb_convert_encoding($value->text, 'utf-8', $value->charset);
                    } else {
                        $mail[$mailno]['subject'] = $value->text;
                    }
                }
            } else {
                // タイトルがない場合の処理を記述...
            }
            // 格納用変数の初期化
            $charset = null;
            $encoding = null;
            $attached_data = null;
            $parameters = null;
            // メール構造を取得
            $info = imap_fetchstructure($mbox, $mailno);  // ここでエラー？
            if (!$info) {  // re-connect to mail server
                if (($mbox = imap_open(SERVER . "INBOX", $GMAIL_ACCOUNT, $GMAIL_PASSWORD)) == false) {
                    echo "IMAP connection failed... <br/>";
                    exit;
                }
                $info = imap_fetchstructure($mbox, $mailno);
            }
            // echo "<br/><br/><pre>********************* mailno".$mailno.":"; // debug
            // echo var_dump($info); echo "</pre>"; ob_flush(); flush();  // debug
            if (!empty($info->parts)) {
                //
                $parts_cnt = count($info->parts);
                for ($p = 0; $p < $parts_cnt; $p++) {
                    // タイプにより処理を分ける
                    // [参考] http://www.php.net/manual/ja/function.imap-fetchstructure.php
                    if ($info->parts[$p]->type == 0) {
                        if (empty($charset)) {
                            $charset = $info->parts[$p]->parameters[0]->value;
                        }
                        if (empty($encoding)) {
                            $encoding = $info->parts[$p]->encoding;
                        }
                    } elseif (!empty($info->parts[$p]->parts) && $info->parts[$p]->parts[$p]->type == 0) {
                        $parameters = $info->parts[$p]->parameters[0]->value;
                        if (empty($charset)) {
                            $charset = $info->parts[$p]->parts[$p]->parameters[0]->value;
                        }
                        if (empty($encoding)) {
                            $encoding = $info->parts[$p]->parts[$p]->encoding;
                        }
                    } elseif ($info->parts[$p]->type == 5) {
                        $files = imap_mime_header_decode($info->parts[$p]->dparameters[0]->value);
                        if (!empty($files) && is_array($files)) {
                            $attached_data[$p]['file_name'] = null;
                            foreach ($files as $key => $file) {
                                if ($file->charset != 'default') {
                                    $attached_data[$p]['file_name'] .= mb_convert_encoding($file->text, 'utf-8', $file->charset);
                                } else {
                                    $attached_data[$p]['file_name'] .= $file->text;
                                }
                            }
                        }
                        $attached_data[$p]['content_type'] = $info->parts[$p]->subtype;
                    }
                }
            } else {
                $charset = $info->parameters[0]->value;
                $encoding = $info->encoding;
            }
            if (empty($charset)) {
                // エラー処理を記述...
            }
            // 本文を取得
            $body = imap_fetchbody($mbox, $mailno, 1, FT_INTERNAL);
            $body = trim($body);
            if (!empty($body)) {
                // タイプによってエンコード変更
                switch ($encoding) {
                    case 0 :
                        $mail[$mailno]['body'] = mb_convert_encoding($body, "utf-8", $charset);
                        break;
                    case 1 :
                        $encode_body = imap_8bit($body);
                        $encode_body = imap_qprint($encode_body);
                        $mail[$mailno]['body'] = mb_convert_encoding($encode_body, "utf-8", $charset);
                        break;
                    case 3 :
                        $encode_body = imap_base64($body);
                        $mail[$mailno]['body'] = mb_convert_encoding($encode_body, "utf-8", $charset);
                        break;
                    case 4 :
                        $encode_body = imap_qprint($body);
                        $mail[$mailno]['body'] = mb_convert_encoding($encode_body, 'utf-8', $charset);
                        break;
                    case 2 :
                    case 5 :
                    default:
                        // エラー処理を記述...
                        break;
                }
            } else {
                // エラー処理を記述...
            }

            // 添付を取得

            if (!empty($attached_data)) {
                foreach ($attached_data as $key => $value) {
                    $attached = imap_fetchbody($mbox, $mailno, $key + 1, FT_INTERNAL);
                    if (empty($attached)) break;
                    // ファイル名を一意の名前にする(同じファイルが存在しないように)
                    list($name, $ex) = explode('.', $value['file_name']);
                    $mail[$mailno]['attached_file'][$key]['file_name'] = $name . '_' . time() . '_' . $key . '.' . $ex;
                    // 大きい画像は小さく変換
                    if ($compressimage > 0 and strlen($attached) > 200 * 1000) {
                        $attached = mod_sharedpanel_compress_img_base64($attached, $compressimage);
                        $mail[$mailno]['attached_file'][$key]['imageb'] = $attached;
                        $mail[$mailno]['attached_file'][$key]['content_type'] = 'Content-type: image/jpeg';
                    } else {
                        $mail[$mailno]['attached_file'][$key]['imageb'] = $attached;
                        $mail[$mailno]['attached_file'][$key]['content_type'] = 'Content-type: image/' . strtolower($value['content_type']);
                    }
                }
            }

            // メールの削除
//      imap_delete($mbox, $mailno);

        } //   for( $mailno=1; $mailno<=$mboxes->Nmsgs; $mailno++ )

        // 削除用にマークされたすべてのメッセージを削除
//  imap_expunge($mbox);

        // $mailの中身を確認
//var_dump($mail);

        $co = 0;
        foreach ($mail as $mail2) {
            $ret1 = "";
//Evernote処理
            if ($mail2['address'] == "no-reply@evernote.com") {
                continue;
//メール処理
            } else {
                $mail3 = $mail2['attached_file'];
                //view.phpで定義をすることにしたため，下記のコードは削除（2016.07.21）
                //echo '<div-mail>';
                // $ret1.= $mail2['subject']."<br>"."<br>".$mail2['address']."<br>"."<hr>"."<br>";
                $ret1 .= $mail2['subject'] . "<br/>";
                //echo "<img src='".$mail3."''>"."<br>","<br>";
                foreach ($mail3 as $mail4) {
                    $ret1 .= "<img src='data:image/gif;base64," . $mail4['imageb'] . "'  width=250px>" . "<br><br>";
                }
                // $ret1.= $mail2['body']."<br>".$mail2['date']."<br><br> from Email";
                $ret1 .= $mail2['body'];

                // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
                if ($mail2['address'] == "xxx") {
                    continue;
                }

                // subject に $andkey が含まれなければ登録しない（次の投稿の処理へ）
                if (!preg_match("/$andkey/", $mail2['subject'])) {
                    continue;
                }

                // DBにカードを追加
                $data = new stdClass;
                $data->sharedpanelid = $sharedpanel->id;
                $data->userid = $USER->id;
                $data->timeposted = strtotime($mail2['date']);
                $data->timecreated = time();
                $data->timemodified = $data->timecreated;
                $data->sender = $mail2['address'];
                $data->inputsrc = "email";
                $data->content = $ret1;
                $data->id = $DB->insert_record('sharedpanel_cards', $data);

                foreach (mod_sharedpanel_get_tags($mail2['subject']) as $tagstr) {
                    // DBにタグを追加
                    $tag = new stdClass;
                    $tag->cardid = $data->id;
                    $tag->userid = $USER->id;
                    $tag->timecreated = $data->timecreated;
                    $tag->tag = $tagstr;
                    $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
                }
                echo "A card imported from email : a mail as " . $mail2['subject'] . " (" . $mail2['date'] . ")<br>\n";
                ob_flush();
                flush();
                $co++;
                if ($co >= $maxnumatatime) {
                    echo "Only $maxnumatatime emails are imported at a time. Quitting.\n";
                    break;
                }
            }
        } //  foreach ($mail as $mail2)
    }


}
