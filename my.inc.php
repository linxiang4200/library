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
$actionarr = array('index','activation','del_cat','add_cat','edit_cat', 'favourite', 'favourite_add', 'favourite_delete', 'info');
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';
$navtitle = lang('plugin/library', 'my_library');

$noAdminAction = array('index','activation','del_cat','edit_cat','add_cat', 'favourite', 'favourite_add', 'favourite_delete', 'info');
//只有管理员才能进行此操作
if ((empty($_G['library']['adminid']) || 1 != $_G['library']['adminid']) && !in_array($action, $noAdminAction)) {
    showmessage(lang('plugin/library', 'only_admin'));
}

$bookCategoryList = '';
$bookCategoryList = library_showBookCateboryList('cids[]', $book['cids']);

//个人书库首页
if ('index' == $action) {
    $navtitle = '我的书库';//lang('plugin/library', 'my_library');

    //数据数据
	$stack = C::t('#library#library_stack')->get_shuku_stack($_G['uid']);
	
	if(empty($stack))
	{
		header("Location:plugin.php?id=library:my&action=activation");
	}
	
	
	//分类数据
    $category = C::t('#library#library_stack')->get_shuku_category($_G['uid']);
    
	
   
    include_once template('library:my_index');
    
//激活
} elseif ('activation' == $action) {
	
	$navtitle = '激活个人图书管理系统';
	
	$activation_status = C::t('#library#library_stack')->get_shuku_status($_G['uid']);
	
	if($activation_status == 1)
	{
		header("Location:plugin.php?id=library:my");
	}
	
	if(!empty($_POST))
	{
		
		if(empty($_POST['stack_name']))showmessage(lang('plugin/library', '书库名不能为空'));
		
		$uploadFiles = library_getNormalizedFILES();
        //处理封面
        $upload = new discuz_upload();
        $upload->init($uploadFiles['shuku_umg'][0], 'common', time(), 'shuku_cover');
        
        $attach = $upload->attach;
        
        $shuku_img = $attach['attachment'];
        
        $upload->save();
        
        $sk_data = array(
        'stack_name' 	=> addslashes(trim($_POST['shuku_name'])),
        'stack_img'		=> empty($shuku_img) ? '44/default_cat_cover.jpg' : $shuku_img,
        'uid'			=> $_G['uid'],
        'capacity'		=> '500',
        'share_status'	=> intval($_POST['share_status']),
        'shiyong_time'	=> time()+86400*180,
        'daoqi_time'	=> time()+86400*180,
        'create_time'	=> time()
        );
        
        //插入书库数据
        $ret =  C::t('#library#library_stack')->add($sk_data);
        
        
        $sk_cat_data = array(
	        'stack_id'		=> $ret,
	        'uid'			=> $_G['uid'],
	        'cat_name'		=> '默认分类',
	        'cat_img'		=> '44/default_cat_cover.jpg',
	        'create_time'	=> time()
        );
        
        //插入默认分类数据
        C::t('#library#library_stack')->add_cat($sk_cat_data);
        
        if ($ret > 0) {
            $success = 1;
            $msg .= lang('plugin/library', 'operate_success');
        } else {
            $success = 0;
            $msg .= lang('plugin/library', 'operate_fail');
        }
        $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:my';
        showmessage($msg
            , $returnUrl, array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
        
	}else{
		include_once template('library:my_activation');
	}

//删除分类
} elseif ('del_cat' == $action) {
	
	$ids = addslashes(trim($_GET['cat_id']));
	$res = array('error' => 1);
	
	if(empty($ids))
	{
		echo json_encode($res);
		exit();
	}
	
	$id = C::t('#library#library_stack')->serilaze_ids($ids);
	$s = C::t('#library#library_stack')->del_shuku_cat($id);
	if($s > 0)
	{
		$res['error']= 0;
		echo json_encode($res);
		exit();
	}

//分类添加
} elseif ('add_cat' == $action) {
	
	if(empty($_POST['cat_name']))showmessage(lang('plugin/library', '分类名不能为空'));
	if(empty($_POST['stack_id']))showmessage(lang('plugin/library', '未知错误，请重试！'));
	
	$uploadFiles = library_getNormalizedFILES();
    //处理封面
    $upload = new discuz_upload();
    $upload->init($uploadFiles['cat_img'][0], 'common', time(), 'shuku_cover');
        
    $attach = $upload->attach;
    if ($attach['isimage']) {
	    $cat_img = $attach['attachment'];
	        
	    $upload->save();
    }
    
    $sk_cat_data = array(
        'stack_id'		=> intval($_POST['stack_id']),
        'uid'			=> $_G['uid'],
        'cat_name'		=> empty($_POST['cat_name']) ? '默认分类' : addslashes(trim($_POST['cat_name'])),
        'cat_img'		=> empty($cat_img) ? '44/default_cat_cover.jpg' : $cat_img,
        'create_time'	=> time()
    );
    
    $res = C::t('#library#library_stack')->add_cat($sk_cat_data);
    
     if ($res > 0) {
     	$success = 1;
     	$msg .= lang('plugin/library', 'operate_success');
     } else {
     	$success = 0;
     	$msg .= lang('plugin/library', 'operate_fail');
	}
	$returnUrl = $_G['siteurl'] . 'plugin.php?id=library:my';
	showmessage($msg, $returnUrl, array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    

//分类编辑
} elseif ('edit_cat' == $action) {
	
	if(empty($_POST['cat_name_edit']))showmessage(lang('plugin/library', '要编辑的信息不能为空'));
	
	foreach($_POST['cat_name_edit'] as $key => $r)
	{
		$res = C::t('#library#library_stack')->edit_shuku_cat_name(addslashes(trim($r)),$key);
	}
	
	if ($res > 0) {
     	$success = 1;
     	$msg .= lang('plugin/library', 'operate_success');
    }else {
     	$success = 0;
     	$msg .= lang('plugin/library', 'operate_fail');
	}
	$returnUrl = $_G['siteurl'] . 'plugin.php?id=library:my';
	showmessage($msg, $returnUrl, array(), array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
	
} elseif ('favourite' == $action) {
    $navtitle = lang('plugin/library', 'favourite');
    $uid = $_G['uid'];
    $myFavouriteThelf = C::t('#library#library_bookthelf')->fetch_by_uid_type($uid);
    if (!$myFavouriteThelf) {
        showmessage(lang('plugin/library', 'favourite_null'));
    }
    
    $btid = $myFavouriteThelf['btid'];
    $limit = 20;
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $url = $_G['siteurl'] . 'plugin.php?id=library:my&action=favourite';
    
    $list = C::t('#library#library_bookthelf_item')->fetch_all_for_page_by_btid($btid, $limit, $page, $url);
    
    include_once template('library:my_favourite');
} elseif ('favourite_add' == $action) {
    $bid = intval($_GET['bid']);
    $msg = '';
    try {
        $ret = C::t('#library#library_bookthelf')->favourite_add($bid);
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }

    if ($ret || 0 == $ret) {
        $success = 1;
        $msg .= lang('plugin/library', 'favourite') . lang('plugin/library', 'operate_success');
    } else {
        $success = 0;
        $msg .= lang('plugin/library', 'favourite') . lang('plugin/library', 'operate_fail');
    }
    showmessage($msg
            , '', array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} elseif ('favourite_delete' == $action) {
    $btiid = intval($_GET['btiid']);
    $msg = '';
    try {
        $ret = C::t('#library#library_bookthelf')->favourite_delete($btiid);
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }

    if ($ret || 0 == $ret) {
        $success = 1;
        $msg .= lang('plugin/library', 'delete') . lang('plugin/library', 'favourite') . lang('plugin/library', 'operate_success');
    } else {
        $success = 0;
        $msg .= lang('plugin/library', 'delete') . lang('plugin/library', 'favourite') . lang('plugin/library', 'favourite') . lang('plugin/library', 'operate_fail');
    }
    include template('common/header_ajax');
    showmessage($msg
            , '', array()
            , array('timeout' => ($success ? 3 : 0),
        'alert' => ($success ? 'right' : 'error'),
        'striptags' => false, 'extrajs' => '<script>setTimeout("location.reload()", 2000);</script>'
    ));
    include template('common/footer_ajax');
} elseif ('info' == $action) {
    $navtitle = lang('plugin/library', 'my_info');
    if($_G['library']['adminid']) {
            $uid = $_GET['uid'];
            $username = $_GET['username'];
        } else {
            $uid = $_G['uid'];
            $username = $_G['username'];
        }
    if (submitcheck('submit', 1)) {
        $data = array(
            'uid' => $uid,
            'username' => $username,
            'receiver_name' => $_POST['receiver_name'],
            'receiver_address' => $_POST['receiver_address'],
            'mobile' => $_POST['mobile'],
            'receiver_phone' => $_POST['receiver_phone'],
            'receiver_qq' => $_POST['receiver_qq'],
            'receiver_time' => $_POST['receiver_time'],
            'receiver_remark' => $_POST['receiver_remark'],
        );
        $ret = C::t('#library#library_member')->add($data);
        if ($ret || 0 == $ret) {
            $success = 1;
            $msg .= lang('plugin/library', 'update_myinfo') . ' ' . lang('plugin/library', 'operate_success');
        } else {
            $success = 0;
            $msg .= lang('plugin/library', 'update_myinfo') . ' ' . lang('plugin/library', 'operate_fail');
        }
        showmessage($msg
                , $_SERVER['HTTP_REFERER'], array()
                , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    } else {
        $reader = C::t('#library#library_member')->fetch_by_uid($uid);
    }
    include_once template('library:my_info');
}
