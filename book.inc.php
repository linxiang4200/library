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
$actionarr = array('index','gf_index',
    'my',
    'add',
    'edit',
    'info',
    'import_from_douban',
    'douban_refresh',
    'search','searchpro', 'category',
    'delete',
    'delete_cover',
    'delete_preview',
    'delete_comment',
);
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';
$navtitle = lang('plugin/library', 'book');
//游客可用功能
$noAuthAction = array('index','gf_index','info', 'search', 'category','searchpro');
if (!$_G['uid'] && !in_array($action, $noAuthAction)) {
    showmessage('to_login', '', array(), array('login' => 1));
}
//普通用户可用功能
$_noAdminAction = array('index',
    'my',
	'gf_index',
    'info',
	'add',
	'edit','delete',
    'import_from_douban',
    'douban_refresh',
    'search',
    'category',

);

$noAdminAction = array_merge($noAuthAction, $_noAdminAction);
if ((empty($_G['uid']) || 1 != $_G['uid']) && !in_array($action, $noAdminAction)) {
    showmessage(lang('plugin/library', 'only_admin'));
}

$bookCategoryList = '';
$bookCategoryList = library_showBookCateboryList('cids[]', $book['cids']);



$getcid = isset($_GET['cid']) ? intval($_GET['cid']) : 1;

if($action != 'search' && $action != 'searchpro')
{
	$categroy_style = library_categoryNumbers();
}

	$gsort = isset($_GET['sort']) ? $_GET['sort'] : 'title';
	switch($gsort)
    {
	    case 'title':$sort='title';break;
	    case 'author':$sort='author';break;
	    case 'publisher':$sort='publisher';break;
	    case 'pubdate':$sort='pubdate';break;
	    case 'format':$sort='format';break;
	    case 'category':$sort='category';break;
	    case 'notes':$sort='notes';break;
	    case 'tags':$sort='tags';break;
	    default: $sort='title';break;
    }


if ('index' == $action) {
	//$navtitle = lang('plugin/library', 'book_name');
	
	
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $page < 1 && $page = 1;
    $perpage = 15;
    $start = ($page - 1) * $perpage;
    $cid = intval($_REQUEST['cid']);
	if($cid > 0)
    {
    	$sqladd .= " cids = '$cid' ";
    }
    
    $navtitle = $_G['cache']['library_bookcategory'][$cid]['name'];
    
    /*
    $list = array();
    $objs = C::t('#library#library_book')->range($start, $perpage, 'desc');
    
    foreach ($objs as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        //$list[] = $obj;
    }
	*/
    $list = C::t('#library#library_book')->getBooks('search', $perpage, true, $sqladd, '', $sort);
    $multi = array();
    
    //$multi = simplepage(count($list), $perpage, $page, 'plugin.php?id=library:book');
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $number = C::t('#library#library_book')->countBooks($cid);
    $multi = multi($number, $perpage, $page, 'plugin.php?id=library:book&sort='.$sort."&cid=$cid");
    //$list = C::t('#library#library_book')->getBooks('search', $perpage, true, $sqladd, 'plugin.php?id=library:book&action=search&srchtxt=' . $srchtxt . '&sctype=' . $sctype);
    
    $navtitle .= ' ('.$number.')';
    
    
    
    include_once template('library:book_index');
}elseif ('gf_index' == $action) {
	$navtitle = '本站书库';
	include_once template('library:gf_index');
}elseif ('add' == $action) {
    $navtitle = lang('plugin/library', 'book');

    // 图书分类
    $bookCategory = '';
    //$bookCategory = library_showBookCateborySelect('cids[]');
    
    $categoryarray = library_categoryArray();
	unset($categoryarray[0]);
    $book = array();

    //设置默认值
    $store_default_warehouse = $_G['cache']['plugin']['library']['store_default_warehouse'];
    $store_default_accession_number = $_G['cache']['plugin']['library']['store_default_accession_number'];
    $store_default_owner = $_G['cache']['plugin']['library']['store_default_owner'];
    $store = array(
        'warehouse' => $store_default_warehouse,
        'accession_number' => $store_default_accession_number,
        'owner' => $store_default_owner,
    );

    if (submitcheck('submit', 1)) {
        $title = $_GET['title'];
        $title || showmessage(lang('plugin/library', 'book_title_null'));
        $cids = $_GET['cids'];
        $cids || showmessage(lang('plugin/library', '分类不能为空'));
        $isbn = time();//$_GET['isbn13'];
        $isbn || showmessage(lang('plugin/library', 'book_isbn_null'));
        $isbn = preg_replace(plugin_library::ISBN_PREG, '', $isbn);
        $book = C::t('#library#library_book')->fetch_by_isbn($isbn);
        
        if ($book) {
            $msg = lang('plugin/library', 'book_isbn_exist');
            $success = 0;
            $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $book['bid'];
            showmessage($msg
                , $returnUrl, array()
                , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
        }

        //处理分类
        
        if (is_array($cids)) {
            $cids = join(',', $cids);
        }
        $_bookData = array(
        	'uid' => $_G['uid'],
            'title' => $_GET['title'],
            'author' => $_GET['author'],
            'translator' => $_GET['translator'],
            'publisher' => $_GET['publisher'],
            'pubdate' => $_GET['pubdate'],
            'isbn13' => $isbn,//isbn
            'binding' => $_GET['binding'],
            'price' => $_GET['price'],
            'pages' => $_GET['pages'],
            'rating' => $_GET['rating'],
            'douban_id' => $_GET['douban_id'],
            'tags' => $_GET['tags'],
            'cids' => intval($_GET['cids']),
        	'category' => library_showBookCateboryName(intval($_GET['cids'])),
            'author-intro' => $_GET['author-intro'],
            'summary' => $_GET['summary'],
        	'notes' => $_GET['notes'],
        	'format' => intval($_GET['format']),
        	'dateline' => time(),
            'editor_recommendation' => $_GET['editor_recommendation'],
        );
        
        $_catalogData = array(
            'warehouse' => trim($_GET['warehouse']),
            'accession_number' => trim($_GET['accession_number']),
            'owner' => trim($_GET['owner']),
        );

        $msg = '';
        
        try {
        	
         	//处理上传图片
            $uploadFiles = library_getNormalizedFILES();
            //处理封面
            $upload = new discuz_upload();
            $upload->init($uploadFiles['cover'][0], 'common', time(), 'book_cover');
            
            $attach = $upload->attach;
            if ($attach['isimage']) {
                $newCoverUrl = $attach['attachment'];
               $_bookData['cover_image'] = $newCoverUrl;
               
                C::t('#library#library_book')->updateCover($bid, $newCoverUrl);
                $upload->save();
            }
            
       
        	
            $ret = $bid = C::t('#library#library_book')->add($_bookData);
            
            $ret2 = C::t('#library#library_store')->add($isbn, $_catalogData, true, true);
           
			
            //处理预览图
            foreach ($uploadFiles['preview'] as $key => $file) {
                $upload->init($file, 'common', $bid);
                $attach = $upload->attach;
                if ($attach['isimage']) {
                    $newAttachUrl = $attach['attachment'];
                    C::t('#library#library_book_attachment')->add($bid, $newAttachUrl, 1, $attach);
                    $upload->save();
                }
            }
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
        $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid='.$ret;
        showmessage($msg
            , $returnUrl, array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    }

    include_once template('library:book_form');
} elseif ('edit' == $action) {
	
    $bid = $_GET['bid'];
    $bid || showmessage(lang('plugin/library', 'book_bid_null'));
    $book = C::t('#library#library_book')->fetch($bid);
    $book || showmessage(lang('plugin/library', 'can_not_find_book'));
    $navtitle = lang('plugin/library', 'edit') . ' - ' . $book['title'];

    $bookCategory = '';
    //$bookCategory = library_showBookCateborySelect('cids[]', $book['cids']);
    $categoryarray = library_categoryArray();
    unset($categoryarray[0]);
    
    

    // 预览图
    $previewImages = C::t('#library#library_book_attachment')->fetch_all_by_bid($bid);

    // 保存
    if (submitcheck('submit', 1)) {

        $title = $_GET['title'];
        $title || showmessage(lang('plugin/library', 'book_title_null'));
        $isbn = time();//$_GET['isbn13'];
        $isbn || showmessage(lang('plugin/library', 'book_isbn_null'));
        $isbn = preg_replace(plugin_library::ISBN_PREG, '', $isbn);

        $uploadFiles = library_getNormalizedFILES();
        //处理封面
        $upload = new discuz_upload();
        $upload->init($uploadFiles['cover'][0], 'common', $bid, 'book_cover');
        $attach = $upload->attach;
        
        if ($attach['isimage']) {
            $newCoverUrl = $attach['attachment'];
            C::t('#library#library_book')->update($bid, $newCoverUrl);
            $upload->save();
            //die;
        }

        //处理预览图
        foreach ($uploadFiles['preview'] as $key => $file) {
            $upload->init($file, 'common', $bid);
            $attach = $upload->attach;
            if ($attach['isimage']) {
                $newAttachUrl = $attach['attachment'];
                C::t('#library#library_book_attachment')->add($bid, $newAttachUrl, 1, $attach);
                $upload->save();
            }
        }

        //处理分类
        $cids = $_GET['cids'];
        if (is_array($cids)) {
            $cids = join(',', $cids);
        }
        $category_name = library_showBookCateboryName($cids);
        $_bookData = array(
        	
            'title' => $_GET['title'],
            'author' => $_GET['author'],
            'translator' => $_GET['translator'],
            'publisher' => $_GET['publisher'],
            'pubdate' => $_GET['pubdate'],
            'isbn13' => $isbn,
        	'binding' => $_GET['binding'],
            //'cover_image' => $newCoverUrl,
            'price' => $_GET['price'],
            'pages' => $_GET['pages'],
            'rating' => $_GET['rating'],
            'douban_id' => $_GET['douban_id'],
            'tags' => $_GET['tags'],
            'cids' => $cids,
        	'category' => $category_name,
            'author-intro' => $_GET['author-intro'],
            'summary' => $_GET['summary'],
        	'format' => intval($_GET['format']),
        	'notes' => $_GET['notes'],
            'editor_recommendation' => $_GET['editor_recommendation'],
        );
        if($newCoverUrl)$_bookData['cover_image'] = $newCoverUrl;
        $msg = '';
        try {
            $ret = C::t('#library#library_book')->update($bid, $_bookData);
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
        showmessage(
            $msg
            , $returnUrl
            , array()
            , array(
                'timeout' => ($success ? 1 : 0)
                , 'alert' => ($success ? 'right' : 'error')
                , 'striptags' => false)
            );
    }

    include_once template('library:book_form');
} elseif ('info' == $action) {
    $bid = $_GET['bid'];
    $bid || showmessage(lang('plugin/library', 'book_bid_null'));
    $book = C::t('#library#library_book')->fetch($bid);
    $book || showmessage(lang('plugin/library', 'can_not_find_book'));
    $navtitle = $book['title'];
	
	
    //预览图
    $previewImages = C::t('#library#library_book_attachment')->fetch_all_by_bid($bid);

    //评论
    $limit = 20;
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $url = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $bid;
    $comments0 = C::t('#library#library_book_comment')->fetch_all_for_page_by_bid($bid, $limit, $page, $url);
    $comments = $commentsUids = array();
    foreach ($comments0 as &$obj) {
        $commentsUids[] = $obj['uid'];
        $obj['content'] = dhtmlspecialchars($obj['content']);
        $comments[$obj['bcid']] = $obj;
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
    /*
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
*/

    // 保存评论
    if (submitcheck('submit', 1)) {
        // 游客不许评论
        if (!$_G['uid']) {
            showmessage('to_login', '', array(), array('login' => 1));
        }

        $newComment = trim($_GET['newComment']);
        $newComment = dhtmlspecialchars($newComment);
        $msg = '';
        $success = 0;
        if (!$newComment) {
            $msg .= lang('plugin/library', 'operate_fail') . '：' . lang('plugin/library', 'comment_content_null');
        } else {
            $commentData = array(
                'bid' => $bid,
                'content' => $newComment,
            );

            try {
                $ret = C::t('#library#library_book_comment')->add($commentData);
            } catch (Exception $e) {
                $msg = $e->getMessage();
            }

            if ($ret || 0 == $ret) {
                $success = 1;
                $msg .= lang('plugin/library', 'operate_success');
            } else {
                $msg .= lang('plugin/library', 'operate_fail');
            }
        }
        $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $bid;
        showmessage($msg
            , $returnUrl, array()
            , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
    }

    include_once template('library:book_info');
} elseif ('import_from_douban' == $action) {
    $navtitle = lang('plugin/library', 'import_from_douban');
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        global $_G;
        $items = explode("\n", $_POST['items']);
        foreach ($items as $item) {
            $item = preg_replace(plugin_library::ISBN_PREG, '', $item);
            if (!in_array($item, $_isbns) && strlen($item) > 0) {
                $_isbns[] = $item;
            }
        }
        $isbns = implode(',', $_isbns);
        if (empty($isbns)) {
            showmessage(lang('plugin/library', 'please_check_input'), $_G['siteurl'] . 'plugin.php?id=library:book&action=import_from_douban&hash=' . FORMHASH, array(), array('timeout' => 0, 'alert' => 'error', 'striptags' => false));
        }
        if (!intval($_GET['checking'])) {
            showmessage('<h4 class="infotitle1">' . lang('plugin/library', 'douban_importing') . '</h4><img src="static/image/admincp/ajax_loader.gif" class="marginbot">'
                , $_G['siteurl'] . 'plugin.php?id=library:book&action=import_from_douban&isbns=' . $isbns . '&checking=1', array(), array('refreshtime' => 3), false);
        }
    } else {
        $checking = intval($_GET['checking']);
        $force = empty($_GET['force']) ? 0 : (intval(trim($_GET['force'])) == 1 ? 1 : 0);
        $_isbns = $_GET['isbns'];
        if ($checking && strlen($_isbns) > 0) {
            $itemSuccessCount = 0;
            $itemFalseCount = 0;
            $successes = array();
            $falsees = array();
            $isbns = explode(',', $_isbns);
            foreach ($isbns as $isbn) {
                // $auth = base64_encode(authcode(TIMESTAMP, 'ENCODE', $_G['config']['security']['authkey']));
                // $remoteUrl = $_G['siteurl'] . 'plugin.php?id=library:import_from_douban&json=1&force=' . $force . '&isbn=' . $isbn . '&auth=' . $auth;
                // $ret = dfsockopen($remoteUrl, 0, '', '', false, '127.0.0.1', 30000);
                // $ret = file_get_contents($remoteUrl);
                // $ret = json_decode($ret);
                $ret = library_import_book_from_douban($isbn);
                if ($ret['success']) {
                    $successes[] = $ret['msg'];
                    $itemSuccessCount++;
                } else {
                    $falsees[] = $ret['msg'];
                    $itemFalseCount++;
                }
            }
            $msg = '';
            if ($itemSuccessCount > 0) {
                $msg .= lang('plugin/library', 'import_success_count', array('count' => $itemSuccessCount));
                foreach ($successes as $book) {
                    $msg .= $book . '<br/>';
                }
                $msg .= '<hr/>';
            }
            if ($itemFalseCount > 0) {
                $msg .= lang('plugin/library', 'import_fail_count', array('count' => $itemFalseCount));
                foreach ($falsees as $isbn) {
                    $msg .= $isbn . '<br/>';
                }
                $msg .= '<hr/>';
            }
            //返回的URL
            $_getUrl = !empty($_GET['return_url']) ? urldecode($_GET['return_url']) : '';
            $returnUrl = !empty($_getUrl) ? $_getUrl : $_G['siteurl'] . 'plugin.php?id=library:book&action=import_from_douban&hash=' . FORMHASH;
            showmessage($msg, $returnUrl, array(), array('timeout' => ($itemFalseCount > 0 ? 0 : 1), 'alert' => ($itemFalseCount > 0 ? 'error' : 'right'), 'striptags' => false, 'refreshtime' => $itemSuccessCount));
        }
        include_once template('library:book_import_from_douban');
    }
} else if ('douban_refresh' == $action) {
    $_isbns = preg_replace(plugin_library::ISBN_PREG, '', $_GET['isbn']);
    if (!intval($_GET['checking'])) {
        $fowardUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=import_from_douban'
            . '&isbns=' . $_isbns
            . '&return_url=' . urlencode($_SERVER['HTTP_REFERER'])
            . '&force=1'
            . '&checking=1';
        showmessage('<h4 class="infotitle1">' . lang('plugin/library', 'douban_importing') . '</h4><img src="static/image/admincp/ajax_loader.gif" class="marginbot">'
            , $fowardUrl, array(), array('timeout' => 1, 'refreshtime' => 1));
    }
} else if ('search' == $action) {
    $perpage = 20;
    $cid = intval($_REQUEST['cid']);
    $srchtxt = trim($_GET['srchtxt']);
    $sctype = in_array($_GET['sctype'], array(1, 2, 3)) ? $_GET['sctype'] : '0';
    $sqladd = '';
    switch ($sctype) {
    case '0':
        $sqladd = ($sqladd ? 'AND' : '') . " title LIKE '%$srchtxt%'";
        break;
    case '1':
        $sqladd = ($sqladd ? 'AND' : '') . " isbn13 LIKE '%$srchtxt%'";
        break;
    case '2':
        $sqladd = ($sqladd ? 'AND' : '') . " author LIKE '%$srchtxt%'";
        break;
    case '3':
        $sqladd = ($sqladd ? 'AND' : '') . " summary LIKE '%$srchtxt%'";
        break;
    case '4':
        $sqladd = ($sqladd ? 'AND' : '') . " category LIKE '%$srchtxt%'";
        break;
    }
    
    $categroy_style = library_categoryNumbers(' and '.$sqladd);
    
    if($cid > 0)
    {
    	$sqladd .= " AND cids = '$cid' ";
    }
    
    
    
    $list = C::t('#library#library_book')->getBooks('search', $perpage, true, $sqladd, 'plugin.php?id=library:book&action=search&srchtxt=' . $srchtxt . '&sctype=' . $sctype."&sort=$sort"."&cid=$cid");
    include_once template('library:book_index');
}elseif ('searchpro' == $action) {
	
	$navtitle = '高级搜索';
	$submitgf = 1;
	
	$submitgf = ($_REQUEST['submitgf'] == 1) ? 1:0;
	$submitsy = ($_REQUEST['submitsy'] == 1) ? 1:0;
	
	
	$categoryarray = library_categoryArray();
	
	
	if ($submitgf == 1 || $submitsy == 1) {
	    $perpage = 20;
	    $srchtxt = trim($_REQUEST['srchtxt']);
	    $sctype = in_array($_REQUEST['sctype'], array(1, 2, 3)) ? $_GET['sctype'] : '0';
	    $sql = '';
	    if($submitgf == 1)
	    {
	    	$sql = " uid = '1' ";
	    	$isgf = 1;
	    }else{
	    	$sql = " uid != '1' ";
	    	$issy = 1;
	    }
	    
	    $cid = intval($_REQUEST['cid']);
	    $title = trim(htmlspecialchars($_REQUEST['title']));
	    $author = trim(htmlspecialchars($_REQUEST['author']));
	    $publisher = trim(htmlspecialchars($_REQUEST['publisher']));
	    $pubdate_sy = intval(htmlspecialchars($_REQUEST['pubdate_sy']));
	    $pubdate_ey = intval(htmlspecialchars($_REQUEST['pubdate_ey']));
	    $format = trim(htmlspecialchars($_REQUEST['format']));
	    $tags = trim(htmlspecialchars($_REQUEST['tags']));
	    $summary = trim(htmlspecialchars($_REQUEST['summary']));
	    $exception = trim(htmlspecialchars($_REQUEST['exception']));
	    
	    
	    if(!empty($title)) $sql .= " AND title LIKE '%$title%'";
	    if(!empty($author)) $sql .= " AND author LIKE '%$author%'";
	    if(!empty($publisher)) $sql .= " AND publisher LIKE '%$publisher%'";
	    if(!empty($pubdate_sy)) $sql .= " AND left(pubdate,4) >= '$pubdate_sy'";
	    if(!empty($pubdate_ey)) $sql .= " AND left(pubdate,4) <= '$pubdate_ey'";
	    if(!empty($format)) $sql .= " AND format LIKE '%$format%'";
	    if(!empty($tags)) $sql .= " AND tags LIKE '%$tags%'";
	    if(!empty($summary)) $sql .= " AND summary LIKE '%$summary%'";
	    
	    if(!empty($exception)) $sql .= " AND title NOT LIKE '%$exception%'";
	    
	    $categroy_style = library_categoryNumbers(' and '.$sql);
	    
		if($cid > 0)
	    {
	    	$sql .= " AND cids = '$cid' ";
	    }
	    
	    $list = C::t('#library#library_book')->getBooks('search', $perpage, true, $sql, "plugin.php?id=library:book&action=searchpro&title=$title&author=$author&publisher=$publisher&pubdate=$pubdate&format=$format&tags=$tags&summary=$summary&pubdate_sy=$pubdate_sy&pubdate_ey=$pubdate_ey&exception=$exception&submitgf=$submitgf&submitsy=$submitsy&sort=$sort" . $sctype."&cid=$cid");
	    include_once template('library:book_index');
	    die;
	}
	
	include_once template('library:book_search');
} else if ('category' == $action) {
    $perpage = 15;
    $cid = intval($_GET['cid']);
    //echo $sqladd = " category LIKE '%$cid%'";
    $list = C::t('#library#library_book')->getBooksByCategoryId($cid, $perpage, 'plugin.php?id=library:book&action=category&cid=' . $cid);
    include_once template('library:book_index');
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
} elseif ('delete' == $action) {
    $bid = intval($_GET['bid']);
    $msg = '';
    
    $book = C::t('#library#library_book')->fetch($bid);
    
    if($_G['uid'] != $book['uid'])
    {
    	showmessage(lang('plugin/library', 'only_admin'));
    }
    
    try {
        $ret = C::t('#library#library_book')->delete($bid);
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
    $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book';
    showmessage($msg
        , $returnUrl, array()
        , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));

} elseif ('delete_cover' == $action) {

    $bid = intval($_GET['bid']);
    $msg = '';
    try {
        $ret = C::t('#library#library_book')->deleteCover($bid);
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
    $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=edit&bid=' . $bid;
    showmessage($msg
        , $returnUrl, array()
        , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} elseif ('delete_preview' == $action) {

    $aid = intval($_GET['aid']);
    $bid = intval($_GET['bid']);
    $msg = '';
    try {
        $ret = C::t('#library#library_book_attachment')->delete($aid);
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
    $returnUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=edit&bid=' . $bid;
    showmessage($msg
        , $returnUrl, array()
        , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
} elseif ('delete_comment' == $action) {

    $bid = intval($_GET['bid']);
    $bcid = intval($_GET['bcid']);
    $msg = '';
    try {
        $ret = C::t('#library#library_book_comment')->delete($bcid);
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

