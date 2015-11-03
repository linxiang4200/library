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
if (!$_G['uid']) {
	showmessage('to_login', '', array(), array('login' => 1));
}

$actionarr = array('index', 'apply', 'approve', 'notify_fetch', 'reservate_cancel', 'json_is_trun_to');
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';

$noAdminAction = array('apply', 'my', 'json_is_trun_to', 'reservate_cancel');
//只有管理员才能进行此操作
if ((empty($_G['library']['adminid']) || 1 != $_G['library']['adminid']) && !in_array($action, $noAdminAction)) {
	showmessage(lang('plugin/library', 'only_admin'));
}

$navtitle = lang('plugin/library', 'reservation');
if ('index' == $action) {
	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	$page < 1 && $page = 1;
	$perpage = 20;
	$start = ($page - 1) * $perpage;

	$list = array();
	$readerIds = $adminIds = array();
	$objs = C::t('#library#library_reservation')->range($start, $perpage, 'desc');
	foreach ($objs as $obj) {
		$obj['dateline'] = dgmdate($obj['dateline'], 'u');
		$list[] = $obj;
		$readerIds[] = $obj['uid'];
		!empty($obj['admin_uid']) && $adminIds[] = $obj['admin_uid'];
	}
	$_userIds = array_flip(array_flip(array_merge($readerIds, $adminIds)));
	$userIds = array();
	$users = array();
	foreach ($_userIds as $uid) {
		$userIds[] = $uid;
	}
	if (count($userIds) == 1) {
		$user = getuserbyuid($userIds[0]);
		$users[$user['uid']] = $user;
	} else {
		$_users = C::t('common_member')->fetch_all($userIds);
		foreach ($_users as $user) {
			$users[$user['uid']] = $user;
		}
	}
	$reservation_status = C::t('#library#library_reservation')->getStatus();
	$multi = simplepage(count($list), $perpage, $page, 'plugin.php?id=library:reservation');
	include_once template('library:reservation_index');
} elseif ('apply' == $action) {
	$bid = intval($_GET['bid']);
	$success = true;
	try {
		$ret = C::t('#library#library_reservation')->apply($bid);
	} catch (Exception $e) {
		$success = false;
		$msg = $e->getMessage();
	}
	if (true == $ret) {
		$msg = lang('plugin/library', 'reservation_success');
		//给管理员发送消息
		$delay_apply_msg = $_G['cache']['plugin']['library']['delay_apply_msg'];
		if (!$delay_apply_msg) {
			$admin_receivers = $_G['cache']['plugin']['library']['admin_receivers'];
			$admin_receiver_ids = array();
			if (!empty($admin_receivers)) {
				$admin_receivers = explode(',', $admin_receivers);
				foreach ($admin_receivers as $username) {
					$user = C::t('common_member')->fetch_by_username($username);
					$user && $admin_receiver_ids[] = $user['uid'];
				}
			}
			$book = C::t('#library#library_book')->fetch($bid);
			$applyMsg = array(
				'subject' => lang('plugin/library', 'notice'),
				'message' => lang('plugin/library', 'notice_username_bookname_apply', array('username' => $_G['username'], 'bookname' => $book['title'])),
			);
			foreach ($admin_receiver_ids as $_id) {
				library_message($_id, $applyMsg);
			}
		}
	} else {
		$success = false;
		$msg = lang('plugin/library', 'reservation_fail') . ':' . $msg;
	}
	$returnUrl = $_SERVER['HTTP_REFERER'];
	showmessage($msg
			, $returnUrl, array()
			, array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} elseif ('approve' == $action) {
	$rid = intval($_GET['rid']);
	$success = true;
	try {
		$ret = C::t('#library#library_reservation')->approve($rid);
	} catch (Exception $e) {
		$success = false;
		$msg = $e->getMessage();
	}
	if (true == $ret) {
		$msg = lang('plugin/library', 'approve') . lang('plugin/library', 'operate_success');
	} else {
		$success = false;
		$msg = lang('plugin/library', 'approve') . lang('plugin/library', 'operate_fail') . ':' . $msg;
	}
	$returnUrl = $_SERVER['HTTP_REFERER'];
	showmessage($msg
			, $returnUrl, array()
			, array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} elseif ('notify_fetch' == $action) {
	$rid = intval($_GET['rid']);
	$success = true;
	try {
		$ret = C::t('#library#library_reservation')->notify_fetch($rid);
	} catch (Exception $e) {
		$success = false;
		$msg = $e->getMessage();
	}
	if (true == $ret) {
		$msg = lang('plugin/library', 'notify_fetch') . lang('plugin/library', 'operate_success');
	} else {
		$success = false;
		$msg = lang('plugin/library', 'notify_fetch') . lang('plugin/library', 'operate_fail') . ':' . $msg;
	}
	$returnUrl = $_SERVER['HTTP_REFERER'];
	showmessage($msg
			, $returnUrl, array()
			, array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} elseif ('reservate_cancel' == $action) {
	$rid = intval($_GET['rid']);
	$success = true;
	try {
		$ret = C::t('#library#library_reservation')->reservate_cancel($rid);
	} catch (Exception $e) {
		$success = false;
		$msg = $e->getMessage();
	}
	if (true == $ret) {
		$msg = lang('plugin/library', 'reservate_cancel') . lang('plugin/library', 'operate_success');
	} else {
		$success = false;
		$msg = lang('plugin/library', 'reservate_cancel') . lang('plugin/library', 'operate_fail') . ':' . $msg;
	}
	$returnUrl = $_SERVER['HTTP_REFERER'];
	showmessage($msg
			, $returnUrl, array()
			, array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} else if ('json_is_trun_to' == $action) {
	//是不是轮到我了？
	$sno = $_GET['sno'];
	$isbn = preg_replace('/[^0-9]/i', '', $_GET['isbn']);
	$username = $_GET['username'];
	$bid = NULL;
	if (!empty($sno)) {
		$store = C::t('#library#library_store')->fetch_by_sno($sno);
		$store && $bid = $store['bid'];
	} else {
		$book = C::t('#library#library_book')->fetch_by_isbn($isbn);
		$book && $bid = $book['bid'];
	}
	//验证用户
	$user = C::t('common_member')->fetch_by_username($username);
	if (empty($bid)) {
		$ret = array(
			'successed' => false,
			'confirm' => false,
			'msg' => lang('plugin/library', 'can_not_find_book'),
		);
	} else if (empty($user)) {
		$ret = array(
			'successed' => false,
			'confirm' => false,
			'msg' => lang('plugin/library', 'can_not_find_user') . ' username = ' . $username,
		);
	} else {
		$reservation_first = C::t('#library#library_reservation')->getFirstOrder($bid);
		if (empty($reservation_first)) {
			$ret = array(
				'successed' => true
			);
		} else {
			if (!empty($reservation_first) && ($firstPerson = getuserbyuid($reservation_first['uid'])) && $username == $firstPerson['username']) {
				$ret = array(
					'successed' => true
				);
			} elseif ($username != $firstPerson['username']) {
				$ret = array(
					'successed' => false,
					'confirm' => true,
					'msg' => lang('plugin/library', 'already_by_user_reservated_confirm', array('username' => $firstPerson['username'])),
					'first' => $firstPerson,
				);
			}
		}
	}
	ob_start();
	include template('common/header_ajax');
	echo json_encode($ret);
	include template('common/footer_ajax');
	dexit();
}
