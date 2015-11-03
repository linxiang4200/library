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
global $_G;
include_once DISCUZ_ROOT . './source/plugin/library/class/DoubanBookImporter.php';
$isbn = trim($_GET['isbn']);
$ret_json = empty($_GET['json']) ? false : (intval(trim($_GET['json'])) == 1 ? true : false);
$forceRefresh = empty($_GET['force']) ? false : (intval(trim($_GET['force'])) == 1 ? true : false);
$ret = array(
	'json' => $ret_json,
	'success' => false,
	'msg' => ''
);
$_auth = base64_decode($_GET['auth']);
$auth = authcode($_auth, 'DECODE', $_G['config']['security']['authkey']);

if (empty($auth)) {
	$ret['msg'] = 'auth fail';
	showRet($ret);
}
if (empty($isbn)) {
	$ret['msg'] = 'isbn null';
	showRet($ret);
}
$msg = 'ISBN = ' . $isbn . '<br />';

$importer = new DoubanBookImporter();
$importer->setIsbn($isbn);
$importer->setForceRefresh($forceRefresh);
$bookArray = $importer->getBookArray();
if (count($bookArray) > 0) {
	if ($_G['charset'] != 'utf-8') {
		foreach ($bookArray as $key => $value) {
			strlen(trim($value)) > 0 && $bookData[$key] = diconv(trim($value), 'UTF-8');
		}
	} else {
		$bookData = $bookArray;
	}
	$toDb = C::t('#library#library_book')->add($bookData);
	if ($forceRefresh == true && strlen($bookData['douban_image'])) {
		$file = $bookData['douban_image'];
		$_i = preg_replace('/[^0-9]/', '', $file);
		$_i = sprintf("%012d", $_i);
		$dir1 = substr($_i, 0, 4);
		$dir2 = substr($_i, 4, 4);
		$datadir = './data/library/pic/' . $dir1 . '/' . $dir2 . '/';
		$pic = $datadir . $file;
		if (file_exists(DISCUZ_ROOT . $pic)) {
			@unlink(DISCUZ_ROOT . $pic);
		}
	}
	if ($toDb) {
		$ret['success'] = true;
		$msg .= lang('plugin/library', 'douban_import_success');
		$msg .= lang('plugin/library', 'book_name') . ' = ' . $bookData['title'] . '<br />';
		$ret['data'] = $bookData;
	}
}

$ret['msg'] = $msg;
showRet($ret);

/**
 * 返回结果
 * @param array $ret
 * @param type $exit 
 */
function showRet($ret, $exit = true) {
	if (false == $ret['json']) {
		echo lang('plugin/library', 'time') . ':', date('Y-m-d H:i:s', time()), '<br />';
		if (true == $ret['success']) {
			echo lang('plugin/library', 'result') . ':' . lang('plugin/library', 'operate_success') . '<br />';
		} else {
			echo lang('plugin/library', 'result') . ':' . lang('plugin/library', 'operate_fail') . '<br />';
		}
		echo lang('plugin/library', 'message') . ':', $ret['msg'], '<br />';
	} else {
		$ret['msg'] = base64_encode($ret['msg']);
		echo json_encode($ret);
	}
	$exit && exit();
}

