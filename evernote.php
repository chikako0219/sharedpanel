<?php
//メール ----------------------------------------------------------------------------
//エラー表示
//ini_set( 'display_errors', 1 );

function add_card_from_evernote($sharedpanel)
{
    global $DB, $USER;

    $GMAIL_ACCOUNT = $sharedpanel->emailadr2;
//  $GMAIL_PASSWORD= $sharedpanel->emailpas2;
    $andkey = $sharedpanel->emailkey2;

    $key = $sharedpanel->encryptionkey;
    $GMAIL_PASSWORD = openssl_decrypt($sharedpanel->emailpas2, 'AES-128-ECB', $key);

    echo "<br/><hr>importing evernote ($GMAIL_ACCOUNT; $andkey) ...  ";
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

// メールボックスへの IMAP ストリームをオープン
    if (($mbox = imap_open(SERVER . "INBOX", $GMAIL_ACCOUNT, $GMAIL_PASSWORD)) == false) {
        // 失敗処理を記述...
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
            // ヘッダー情報の取得
            $head = imap_headerinfo($mbox, $mailno);
            // アドレスの取得
            $mail[$mailno]['address'] = $head->from[0]->mailbox . '@' . $head->from[0]->host;
            // evernote 以外は処理中断
            if ($head->from[0]->host != "evernote.com") {
                continue;
            }
            // 日付
            $mail[$mailno]['date'] = $head->date;
            // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
            $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => strtotime($head->date), 'inputsrc' => 'evernote', 'sharedpanelid' => $sharedpanel->id, 'hidden' => 0));
            if ($samecard != null) {
                $mail[$mailno]['address'] = "xxx";
                echo "Not imported : same evernote email as at " . $head->date . "<br>\n";
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
            $info = imap_fetchstructure($mbox, $mailno);
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
                    //$mail[$mailno]['attached_file'][$key]['image']=imap_base64($attached);
                    $mail[$mailno]['attached_file'][$key]['imageb'] = $attached;
                    $mail[$mailno]['attached_file'][$key]['content_type'] = 'Content-type: image/' . strtolower($value['content_type']);
                }
            }

            // メールの削除
//      imap_delete($mbox, $mailno);
        }
        // 削除用にマークされたすべてのメッセージを削除
//  imap_expunge($mbox);

        // $mailの中身を確認
//var_dump($mail);

        foreach ($mail as $mail2) {
            $ret1 = "";
//Evernote処理
            if ($mail2['address'] == "no-reply@evernote.com") {
                $mail5 = $mail2['attached_file'];
                //view.phpで定義をすることにしたため，下記のコードは削除（2016.07.21）
                //echo '<div-evernote>';
                $ret1 .= $mail2['subject'] . "<br><br><hr><br>";

                foreach ($mail5 as $mail6) {
                    //$ret1.= "<img src='data:image/gif;base64'".$mail6['imageb']." width=250px><br><br>";
                    $ret1 .= "<img src='data:image/gif;base64," . $mail6['imageb'] . "'  width=250px>" . "<br><br>";
                }
                $ret1 .= $mail2['body'] . "<br>" . $mail2['date'] . "<br><br> from Evernote";

                // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
                if ($mail2['address'] == "xxx") {
                    continue;
                }

                // DBにカードを追加
                $data = new stdClass;
                $data->sharedpanelid = $sharedpanel->id;
                $data->userid = $USER->id;
                $data->timeposted = strtotime($mail2['date']);
                $data->timecreated = time();
                $data->timemodified = $data->timecreated;
                $data->inputsrc = "evernote";
                $data->content = $ret1;
                $data->id = $DB->insert_record('sharedpanel_cards', $data);

                // DBにタグを追加
                $tag = new stdClass;
                $tag->cardid = $data->id;
                $tag->userid = $USER->id;
                $tag->timecreated = $data->timecreated;
                $tag->tag = "evernote($GMAIL_ACCOUNT)";
                $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
                echo "A card imported from Evernote : a note as " . $mail2['subject'] . " (" . $mail2['date'] . ")<br>\n";
                ob_flush();
                flush();
                //メール処理
            } else {
                continue;
            }

        }  // foreach ($mail as $mail2)
    }
}
