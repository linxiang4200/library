<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *      书架
 *      $Id: bookthelf.inc.php 29292 2012-03-31 11:00:07Z yaojungang $
 */
if (!defined('IN_DISCUZ')) {
    exit('Access denied');
}
global $_G;
$actionarr = array('index', 'my', 'add', 'edit', 'info');
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';
$navtitle = lang('plugin/library', 'bookthelf');
//游客可用功能
$noAuthAction = array('info', 'search');
if (!$_G['uid'] && !in_array($action, $noAuthAction)) {
    showmessage('to_login', '', array(), array('login' => 1));
}
//普通用户可用功能
$_noAdminAction = array('index', 'my', 'info');
$noAdminAction = array_merge($noAuthAction, $_noAdminAction);
if ((empty($_G['library']['adminid']) || 1 != $_G['library']['adminid']) && !in_array($action, $noAdminAction)) {
    showmessage(lang('plugin/library', 'only_admin'));
}

if ('index' == $action) {
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $page < 1 && $page = 1;
    $perpage = 20;
    $start = ($page - 1) * $perpage;

    $list = array();
    $objs = C::t('#library#library_bookthelf')->range($start, $perpage, 'desc');
    foreach ($objs as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $list[] = $obj;
    }
    $multi = array();
    $multi = simplepage(count($list), $perpage, $page, 'plugin.php?id=library:bookthelf');
    include_once template('library:bookthelf_index');
} elseif ('add' == $action) {
    $btid = $_GET['btid'];
    $bid = $_GET['bid'];
    $btid || showmessage(lang('plugin/library', 'bookthelf_btid_null'));
    $bid || showmessage(lang('plugin/library', 'book_bid_null'));
    $type = isset($_GET['type']) ? intval($_GET['type']) : 0 ;
    $bookthelf = C::t('#library#library_bookthelf')->fetch($btid);
    $bookthelf || showmessage(lang('plugin/library', 'can_not_find_bookthelf'));
    $navtitle = $bookthelf['name'];
} elseif ('info' == $action) {
    $btid = $_GET['btid'];
    $btid || showmessage(lang('plugin/library', 'bookthelf_btid_null'));
    $bookthelf = C::t('#library#library_bookthelf')->fetch($btid);
    $bookthelf || showmessage(lang('plugin/library', 'can_not_find_bookthelf'));
    $navtitle = $bookthelf['name'];

    //书架子详情
    $limit = 20;
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $url = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $bid;
    $items = C::t('#library#library_bookthelf_item')->fetch_all_for_page_by_bid($btid, $limit, $page, $url);
debug($items);
    $commentsUids = array();
    foreach ($comments as $obj) {
        $commentsUids[] = $obj['uid'];
    }
    $commentsUids = array_unique($commentsUids);

    //流通数据
    $_circulations_list = C::t('#library#library_circulation')->fetch_all_by_bid($bid);
    $_circulations = array();
    $circulations = array();
    $_userIds = $readerIds = $adminIds = array();
    foreach ($_circulations_list as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $_circulations[] = $obj;
        $readerIds[] = $obj['uid'];
        $adminIds[] = $obj['admin_uid'];
    }
    $_userIds = array_merge($readerIds, $adminIds);
    $_userIds = array_merge($commentsUids, $_userIds);

    $userIds = array();
    foreach ($_userIds as $uid) {
        $userIds[] = $uid;
    }
    if (count($userIds) == 1) {
        $users = array();
        $user = getuserbyuid($userIds[0]);
        $users[$user['uid']] = $user;
    } else {
        $_users = C::t('common_member')->fetch_all($userIds);
        $users = array();
        foreach ($_users as $user) {
            $users[$user['uid']] = $user;
        }
    }

    foreach ($_circulations as $obj) {
        $obj['reader'] = $users[$obj['uid']];
        $obj['admin'] = $users[$obj['admin_uid']];
        $circulations[] = $obj;
    }
    $circulation_type = array();
    $circulation_type = C::t('#library#library_circulation')->getType();
    //馆藏数据
    $stores = array();
    $_stores = C::t('#library#library_store')->fetch_all_by_bid($bid);
    foreach ($_stores as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $obj['dateline_lend_info'] = intval($obj['dateline_lend']) > 0 ? dgmdate($obj['dateline_lend'], 'u') : lang('plugin/library', 'none');
        $obj['dateline_return_info'] = intval($obj['dateline_return']) > 0 ? dgmdate($obj['dateline_return'], 'u') : lang('plugin/library', 'none');
        $obj['reader'] = intval($obj['uid']) > 0 ? $users[$obj['uid']] : lang('plugin/library', 'none');
        $obj['reader_info'] = intval($obj['uid']) > 0 ? '<a href="home.php?mod=space&uid=' . $obj['uid'] . '" target="_blank">' . avatar($obj['uid'], 'small') . '</a>' : lang('plugin/library', 'none');
        $stores[] = $obj;
    }

    //评论
    if (submitcheck('submit', 1)) {
        $commentData = array(
            'bid' => $bid,
            'content' => $_GET['newComment'],
        );

        $msg = '';
        try {
            $ret = C::t('#library#library_book_comment')->add($commentData);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        if ($ret || 0 == $ret) {
            $success = 1;
            $msg .= lang('plugin/library', 'operate_success');
        } else {
            $success = 0;
            $msg .= lang('plugin/library', 'operate_fail');
        }
        $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $bid;
        showmessage($msg
                , $returnUrl, array()
                , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    }

    include_once template('library:bookthelf_info');
} elseif ('my' == $action) {
    $navtitle = lang('plugin/library', 'my_library');

    $readerIds = $adminIds = array();
    //预约
    $list_reservation = array();
    $objs = C::t('#library#library_reservation')->fetch_all_by_uid($_G['uid']);
    foreach ($objs as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $list_reservation[] = $obj;
        $readerIds[] = $obj['uid'];
        !empty($obj['admin_uid']) && $adminIds[] = $obj['admin_uid'];
    }
    unset($objs);
    //借阅
    $list_lend = array();
    $objs = C::t('#library#library_store')->fetch_all_by_uid($_G['uid']);
    foreach ($objs as $obj) {
        $readerIds[] = $obj['uid'];
        !empty($obj['admin_uid']) && $adminIds[] = $obj['admin_uid'];
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $obj['dateline_lend_info'] = intval($obj['dateline_lend']) > 0 ? dgmdate($obj['dateline_lend'], 'u') : lang('plugin/library', 'none');
        $obj['dateline_return_info'] = intval($obj['dateline_return']) > 0 ? dgmdate($obj['dateline_return'], 'u') : lang('plugin/library', 'none');
        $obj['reader'] = intval($obj['uid']) > 0 ? $users[$obj['uid']] : lang('plugin/library', 'none');
        $obj['reader_info'] = intval($obj['uid']) > 0 ? '<a href="home.php?mod=space&uid=' . $obj['uid'] . '" target="_blank">' . avatar($obj['uid'], 'small') . '</a>' : lang('plugin/library', 'none');
        $list_lend[] = $obj;
    }
    unset($objs);

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
    $reservation_status = array();
    $reservation_status = C::t('#library#library_reservation')->getStatus();
    include_once template('library:book_my');
}
