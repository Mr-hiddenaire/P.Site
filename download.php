<?php
$file = isset($_GET['f'])?urldecode($_GET['f']):'';

$filename = basename($file);

header("Content-type: application/x-bittorrent");
 
//处理中文文件名
$ua = $_SERVER["HTTP_USER_AGENT"];
$encoded_filename = rawurlencode($filename);
if (preg_match("/MSIE/", $ua)) {
 header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
} else if (preg_match("/Firefox/", $ua)) {
 header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
} else {
 header('Content-Disposition: attachment; filename="' . $filename . '"');
}

#header("Content-Length: ". filesize($file));
readfile($file);