<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.inc.php 29292 2012-03-31 11:00:07Z yaojungang $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access denied');
}
//<a href="http://img1.douban.com/lpic/s6094220.jpg" target="_blank"><img src="http://img1.douban.com/lpic/s6094220.jpg" border="0" width="150"></a>
$file = $_GET['file'];
global $_G;
define('DOUBAN_IMG_URL', 'http://img1.douban.com/lpic/');
if (!$file) {
	$pic_url = 'source/plugin/library/images/nopic.png';
} else {
	$_i = preg_replace('/[^0-9]/', '', $file);
	$_i = sprintf("%012d", $_i);
	$dir1 = substr($_i, 0, 4);
	$dir2 = substr($_i, 4, 4);
	$datadir = './data/library/pic/' . $dir1 . '/' . $dir2 . '/';
	$pic = $datadir . $file;
	$random = !empty($random) ? rand(1000, 9999) : '';
	$pic_url = empty($random) ? $pic : $pic . '?random=' . $random;
	if (!file_exists(DISCUZ_ROOT . $pic)) {
		try {
			if (!is_dir($datadir)) {
				dmkdir($datadir);
			}
			get_file(DOUBAN_IMG_URL . $file, DISCUZ_ROOT . $pic);
		} catch (Exception $exc) {
			echo $exc->getTraceAsString();
			exit;
			$pic_url = 'source/plugin/library/images/nopic.png';
		}
	}
}
if (empty($random)) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Last-Modified:" . date('r'));
	header("Expires: " . date('r', time() + 86400));
}

header('Location: ' . $_G['siteurl'] . $pic_url);
exit;

function get_file($url, $pic_name) {
	set_time_limit(24 * 60 * 60);
	$file = fopen($url, "rb");
	if ($file) {
		$newf = fopen($pic_name, "wb");
		if ($newf)
			while (!feof($file)) {
				fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
			}
	}
	if ($file) {
		fclose($file);
	}
	if ($newf) {
		fclose($newf);
	}
}

