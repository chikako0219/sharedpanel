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

defined('MOODLE_INTERNAL') || die();

/*
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 *function sharedpanel_do_something_useful(array $things) {
 *    return new stdClass();
 *}
 */


function mod_sharedpanel_get_tags($s){
  $manum= preg_match_all('/#\S+/',$s,$match);
  $ta = array();
  for($i=0; $i<$manum; $i++){
    $ta[]= $match[0][$i];
  }
  return $ta;
}

function mod_sharedpanel_compress_img($attached, $width){
//  $imagea= imap_base64($attached);
//  $imagea= imagecreatefromstring($imagea);
  $imagea= imagecreatefromstring($attached);
  $imagea= imagescale($imagea, $width, -1);  // proportionally compress image with $width
  $jpegfile= tempnam("/tmp", "email-jpg-");
  imagejpeg($imagea,$jpegfile);
  imagedestroy($imagea);
  $attached= base64_encode(file_get_contents($jpegfile));
  unlink($jpegfile);
  return $attached;
}

// utf8mb4_encode_numericentity
// utf8mb4_decode_numericentity
// UTF-8 の4バイト文字を HTML 数値文字参照に変換する
// MySQL 5.5 で導入された utf8mb4 を使えない場合
// http://qiita.com/masakielastic/items/ec483b00ff6337a02878

function mod_sharedpanel_utf8mb4_encode_numericentity($str)
{
    $re = '/[^\x{0}-\x{FFFF}]/u';
    return preg_replace_callback($re, function($m) {
        $char = $m[0];
        $x = ord($char[0]);
        $y = ord($char[1]);
        $z = ord($char[2]);
        $w = ord($char[3]);
        $cp = (($x & 0x7) << 18) | (($y & 0x3F) << 12) | (($z & 0x3F) << 6) | ($w & 0x3F);
        return sprintf("&#x%X;", $cp);
    }, $str);
}
/*
function utf8mb4_decode_numericentity($str)
{
    $re = '/&#(x[0-9a-fA-F]{5,6}|\d{5,7});/';
    return preg_replace_callback($re, function($m) {
        return html_entity_decode($m[0]);
    }, $str);
}
*/
