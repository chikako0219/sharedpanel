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
 * Japanese strings for SharedPanel
 *
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['sharedpanel:addinstance'] = 'Shared Panelを追加する';
$string['modulename'] = 'SharedPanel';
$string['modulenameplural'] = 'SharedPanels';
$string['modulename_help'] = 'Use the SharedPanel module for... | The SharedPanel module allows...';
$string['sharedpanelname'] = 'SharedPanel名';
$string['sharedpanelname_help'] = 'This is the content of the help tooltip associated with the SharedPanelname field. Markdown syntax is supported.';
$string['sharedpanel'] = 'SharedPanel';
$string['pluginadministration'] = 'SharedPanel管理';
$string['pluginname'] = 'SharedPanel';
$string['requiremodintro'] = '活動説明を必須とするかどうか';
$string['configrequiremodintro'] = '活動説明を必須とするかどうか';

$string['print'] = '印刷する';
$string['backtopanel'] = 'パネルにもどる';

$string['post_message'] = 'メッセージを投稿';
$string['message'] = 'メッセージ';
$string['name'] = '名前';

$string['msg_post_success'] = '投稿しました。';

$string['import'] = 'Twitter/Email/Evernoteからインポートする';
$string['facebookimport'] = 'Facebookからインポートする';

$string['sortedas'] = 'いいね!の多い順、投稿日時の新しい順に並んでいます。';
$string['post'] = 'Moodle上で投稿する';
$string['postmessage'] = 'スマホ・タブレットなどからテキスト・画像を投稿';
$string['postmessage_from_line'] = 'LINEから投稿';
$string['cardcontent'] = 'カードの内容';
$string['cardsender'] = 'カードの送信者';
$string['camera'] = '写真を送信';
$string['groupcard'] = 'グルーピング枠を追加';
$string['sort'] = '重要だね!の多い順でソート';
$string['sortbylike1'] = '面白いね!の多い順でソート';
$string['post_card'] = 'カードを投稿';

$string['important'] = '重要だね';
$string['interesting'] = '面白いね';

$string['gcardcontent'] = 'グループカードの本文';

$string['facebook'] = 'Facebook';
$string['twitter'] = 'Twitter';

$string['FBappID'] = 'Facebook app ID';
$string['FBappID_help'] = '取得した Facebook app ID を書いてください.';
$string['FBsecret'] = 'Facebook secret';
$string['FBsecret_help'] = 'Facebook secret';
$string['FBredirectUrl'] = 'Facebook redirect URL';
$string['FBredirectUrl_help'] = 'Facebook redirect URL';
$string['FBtoken'] = 'Facebook token';
$string['FBtoken_help'] = 'Facebook token';

$string['TWconsumerKey'] = 'Twitter consumerKey';
$string['TWconsumerKey_help'] = 'Write your Twitter consumerKey.';
$string['TWconsumerSecret'] = 'Twitter consumerSecret';
$string['TWconsumerSecret_help'] = 'Twitter consumerSecret';
$string['TWaccessToken'] = 'Twitter accessToken';
$string['TWaccessToken_help'] = 'Twitter accessToken';
$string['TWaccessTokenSecret'] = 'Twitter accessTokenSecret';
$string['TWaccessTokenSecret_help'] = 'Twitter accessTokenSecret';

$string['line_your_line'] = 'あなたのLINE';
$string['line_lineid_required'] = 'LINE IDは必須項目です。';
$string['line_registered_id'] = 'LINE IDを登録しました。';
$string['line_try_agein'] = 'もう一度入力してください。\n 例えば、ログインIDがb1007222の場合、"line_b1007222"と入力してください。';
$string['line_added_user'] = 'ユーザーID{$a}を登録しました。';
$string['line_post_message'] = 'メッセージを投稿しました';

$string['upload_required_comment'] = 'コメントは必須です';
$string['upload_optional_name'] = '名前(任意)';

$string['deletecard_no_permission'] = 'あなたはこのカードを削除することが出来ません。';
$string['deletecard_delete_success'] = 'カード #{$a}を削除(非表示)にしました。';

$string['import_twitter_no_tweets'] = '新規ツイートはありませんでした。';
$string['import_mail_no_mails'] = '新規メールはありませんでした。';
$string['import_no_new'] = '{$a}:新規はありませんでした。';
$string['import_success'] = '{$a->source}:{$a->count}件インポートに成功しました。';
$string['import_failed'] = '{$a}:インポートに失敗しました。';
$string['import_no_authinfo'] = '{$a}:認証情報が設定されていないため、スキップします。';
$string['import_finished'] = 'インポート処理が完了しました。インポートした件数は{$a}件です。';

$string['facebook_no_authinfo'] = '認証情報が入力されていません。';

$string['like_set_like'] = 'カード #{$a}に いいね! しました。';
$string['like_set_unlike'] = 'カード #{$a}の いいね!を解除しました。';

$string['form_import_tweet_hashtag'] = 'インポートするTweetのハッシュタグ';
$string['form_emailadr1'] = 'インポート対象のメールアドレス';
$string['form_emailadr2'] = 'インポート対象のメールアドレス(Evernote用)';
$string['form_emailkey1'] = 'メール表題に含まれるキーワード';
$string['form_emailkey2'] = 'メール表題に含まれるキーワード(Evernote用)';
$string['form_emailpas2'] = 'パスワード(Evernote用)';
$string['form_emailhost'] = 'IMAPサーバURI';
$string['form_emailport'] = 'IMAPサーバポート番号';
$string['form_emailisssl'] = 'SSLを使用する';
$string['form_password'] = 'パスワード';
$string['form_fbgroup1'] = 'FacebookグループID';

$string['form_line_warning_https'] = 'LINEインポートはHTTPS環境下でしか利用できません。MoodleサーバをHTTPS環境下で動作させる必要があります。';

$string['post_cancel'] = 'キャンセルしました。';
$string['post_saved'] = '保存されました。';
$string['save'] = '保存する';