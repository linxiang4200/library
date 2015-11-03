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
//header('Location: plugin.php?id=library:book');die;
global $_G;

$actionarr = array('index', 'reader');
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';
$navtitle = lang('plugin/library', 'library');

$noAdminAction = array('index', 'reader');
//只有管理员才能进行此操作
if ((empty($_G['library']['adminid']) || 1 != $_G['library']['adminid']) && !in_array($action, $noAdminAction)) {
    showmessage(lang('plugin/library', 'only_admin'));
}

$bookCategoryList = '';
$bookCategoryList = library_showBookCateboryList('cids[]', $book['cids']);
$forums = C::t('forum_forum')->fetch_all_by_status(1);

foreach($forums as $key => $r)
{
	$fms[$r['fid']]['todayposts'] = $r['todayposts'];
	$fms[$r['fid']]['threads'] = $r['threads'];
}

if ('index' == $action) {
    
    include_once template('library:library_index');
} else if ($action == 'reader') {
    $navtitle = lang('plugin/library', 'my_info');
    $uid = $_GET['uid'];
    if ($_G['library']['adminid']) {
        $reader = C::t('#library#library_member')->fetch_by_uid($uid);
    } else {
        showmessage(lang('plugin/library', 'goto_user_profile')
                , 'home.php?mod=space&uid=' . $uid, array()
                , array('timeout' => 1));
    }
    include_once template('library:my_info');
}