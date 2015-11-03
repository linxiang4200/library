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

$actionarr = array('index', 'lend', 'return', 'renew');
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';

$noAdminAction = array('renew');
//只有管理员才能进行此操作
if ((empty($_G['library']['adminid']) || 1 != $_G['library']['adminid']) && !in_array($action, $noAdminAction)) {
    showmessage(lang('plugin/library', 'only_admin'));
}

$bookCategoryList = '';
$bookCategoryList = library_showBookCateboryList('cids[]', $book['cids']);

$navtitle = lang('plugin/library', 'circulation');
if ('index' == $action) {
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $page < 1 && $page = 1;
    $perpage = 20;
    $start = ($page - 1) * $perpage;

    $list = array();
    $_list = array();
    $readerIds = array();
    $adminIds = array();
    $objs = C::t('#library#library_circulation')->range($start, $perpage, 'desc');
    foreach ($objs as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $_list[] = $obj;
        $readerIds[] = $obj['uid'];
        $adminIds[] = $obj['admin_uid'];
    }
    $userIds = array_flip(array_flip(array_merge($readerIds, $adminIds)));
    $_users = C::t('common_member')->fetch_all($userIds);
    $users = array();
    foreach ($_users as $user) {
        $users[$user['uid']] = $user;
    }
    foreach ($_list as $obj) {
        $obj['reader'] = $users[$obj['uid']];
        $obj['admin'] = $users[$obj['admin_uid']];
        $list[] = $obj;
    }
    unset($_list);
    unset($_users);
    unset($readerIds);
    unset($adminIds);
    $circulation_type = C::t('#library#library_circulation')->getType();
    $multi = simplepage(count($list), $perpage, $page, 'plugin.php?id=library:circulation');

    $books_lended = array();
    $sqladd = '`lended_amount` > 0 order by `circulation_count` DESC';
    $objs = C::t('#library#library_book')->getBooks('search', 10000, false, $sqladd);
    foreach ($objs as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $books_lended[] = $obj;
    }

    include_once template('library:circulation_index');
} elseif ('lend' == $action) {
    $navtitle = lang('plugin/library', 'lend_book');
    $isbn = isset($_GET['isbn']) ? preg_replace(plugin_library::ISBN_PREG, '', $_GET['isbn']) : '';
    $sno = isset($_GET['sno']) ? preg_replace('/[^\d]/', '', $_GET['sno']) : '';
    $username = isset($_GET['username']) ? $_GET['username'] : '';
    $force = isset($_GET['force']) ? (intval($_GET['force']) == 1 ? 1 : 0) : false;
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        global $_G;
        $success = false;
        try {
            $ret = C::t('#library#library_circulation')->lend($isbn, $username, $sno, $force);
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }
        if (true == $ret) {
            $success = true;
            $msg = lang('plugin/library', 'lend_success');
        } else {
            $msg = lang('plugin/library', 'lend_fail') . ':' . $msg;
        }
        showmessage($msg
            , $_G['siteurl'] . 'plugin.php?id=library:circulation&action=lend', array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    }
    include_once template('library:circulation_lend');
} elseif ('return' == $action) {
    $navtitle = lang('plugin/library', 'return_book');
    $isbn = isset($_GET['isbn']) ? preg_replace(plugin_library::ISBN_PREG, '', $_GET['isbn']) : '';
    $sno = isset($_GET['sno']) ? preg_replace('/[^\d]/', '', $_GET['sno']) : '';
    $username = isset($_GET['username']) ? $_GET['username'] : '';
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        global $_G;
        $success = true;
        try {
            // 如果用户输入了书号和用户名，就按照书号和用户名查找库存
            if ($isbn && $username) {
                //验证用户
                $user = C::t('common_member')->fetch_by_username($username);
                $user || showmessage(lang('plugin/library', 'can_not_find_user'));
                $uid = $user['uid'];

                $_store = C::t('#library#library_store')->fetch_by_isbn_and_uid($isbn, $uid);
                $_store || showmessage(lang('plugin/library', 'can_not_find_book_store'));
                $sno = $_store['sno'];
            }
            $ret = C::t('#library#library_circulation')->return_book($sno, $isbn);
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }
        if (true == $ret) {
            $msg = lang('plugin/library', 'return_book_success');
        } else {
            $success = false;
            $msg = lang('plugin/library', 'return_book_fail') . ':' . $msg;
        }
        showmessage($msg
            , $_G['siteurl'] . 'plugin.php?id=library:circulation&action=return', array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    }

    $readerIds = array();
    $adminIds = array();
    $stores_lended = array();
    $sqladd = '`lended_amount` > 0 order by `dateline_return` ASC';
    $objs = C::t('#library#library_store')->getStores('search', 50, false, $sqladd);
    foreach ($objs as $obj) {
        $readerIds[] = $obj['uid'];
        !empty($obj['admin_uid']) && $adminIds[] = $obj['admin_uid'];
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $obj['dateline_lend_info'] = intval($obj['dateline_lend']) > 0 ? dgmdate($obj['dateline_lend'], 'u') : lang('plugin/library', 'none');
        $obj['dateline_return_info'] = intval($obj['dateline_return']) > 0 ? dgmdate($obj['dateline_return'], 'u') : lang('plugin/library', 'none');
        $obj['reader'] = intval($obj['uid']) > 0 ? $users[$obj['uid']] : lang('plugin/library', 'none');
        $obj['reader_info'] = intval($obj['uid']) > 0 ? '<a href="home.php?mod=space&uid=' . $obj['uid'] . '" target="_blank">' . avatar($obj['uid'], 'small') . '</a>' : lang('plugin/library', 'none');
        $stores_lended[] = $obj;
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

    include_once template('library:circulation_return');
} elseif ('renew' == $action) {
    $sid = intval($_GET['sid']);
    $success = true;
    try {
        $ret = C::t('#library#library_circulation')->renew($sid);
    } catch (Exception $e) {
        $success = false;
        $msg = $e->getMessage();
    }
    if (true == $ret) {
        $msg = lang('plugin/library', 'operate_success');
    } else {
        $success = false;
        $msg = lang('plugin/library', 'operate_fail') . ':' . $msg;
    }
    showmessage($msg
        , $_SERVER['HTTP_REFERER'], array()
        , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
}

