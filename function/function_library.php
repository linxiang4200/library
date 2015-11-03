<?php

/*
 * (C)2001-2012 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * Time: 2012-6-4 9:56:50
 * Id: function_library.php 
 * @author: GavinYao<yaojungang@comsenz.com> 
 */

/**
 * 给用户发消息
 * @param int $uid
 * @param array|string $msg 
 * @param array $type array(1,4) 1:发提醒 2：发消息 3：发邮件 4：发RTX 
 */
function library_message($uid, $msg, $type) {
    global $_G;
    if (empty($type)) {
        $library_send_message_types = unserialize($_G['cache']['plugin']['library']['send_message_types']);
    } else {
        $library_send_message_types = $type;
    }
    if (is_string($msg)) {
        $msg = array(
            'subject' => lang('plugin/library', 'message'),
            'message' => $msg,
        );
    }
    $member = getuserbyuid($uid);
    $subject = lang('plugin/library', 'library') . ':' . $msg['subject'];
    $message = $msg['message'];
    $from_id = (isset($msg['from_id']) && intval($msg['from_id']) > 0) ? intval($msg['from_id']) : $_G['uid'];
    if ($library_send_message_types && in_array(1, $library_send_message_types)) {
        //发提醒
        notification_add($member['uid'], 'system', 'system_notice', array(
            'subject' => $subject,
            'message' => $message,
            'from_id' => $from_id,
            'from_idtype' => 'sendnotice'
        ), 1);
    }
    if ($library_send_message_types && in_array(2, $library_send_message_types)) {
        //发消息
        sendpm($uid, $subject, $message, $from_id);
    }
    if ($library_send_message_types && in_array(3, $library_send_message_types)) {
        //发邮件
        //添加日期
        $message_mail = $message . "\n" . date('Y-m-d H:i:s', TIMESTAMP) . "\n";
        //添加链接
        $message_mail .= '<a href="' . $_G['siteurl'] . 'plugin.php?id=library:book' . '">' . lang('plugin/library', 'library') . '</a>';
        include_once DISCUZ_ROOT . './source/function/function_mail.php';
        if (!sendmail("$member[username] <$member[email]>", $subject, $message_mail)) {
            runlog('sendmail', "$member[email] sendmail failed.");
        }
    }
    if ($library_send_message_types && in_array(4, $library_send_message_types)) {
        //发RTX
        library_sendRtxMsg($member['username'], $msg);
    }
}

/**
 * 发送RTX 消息
 * @global type $_G
 * @param type $username
 * @param type $msg
 * @return type 
 */
function library_sendRtxMsg($username, $msg) {
    global $_G;
    //$rtx_url = 'http://rtx.comsenz.com:8012';
    $rtx_url = $_G['cache']['plugin']['library']['rtx_url'];
    if (!$rtx_url) {
        return;
    }
    $sendmessage_url = $rtx_url . '/sendnotify.cgi';
    $subject = lang('plugin/library', 'library') . ':' . $msg['subject'];
    $message = $msg['message'];
    //去掉空格
    $message = str_ireplace('&nbsp;', ' ', $message);
    //添加日期
    $message .= "\n" . lang('plugin/library', 'time') . ':' . date('Y-m-d H:i:s', TIMESTAMP);
    //添加链接
    $message .= "\n" . '[' . lang('plugin/library', 'library') . '|' . $_G['siteurl'] . 'plugin.php?id=library:index' . ']';
    $msg = diconv(strip_tags($message), $_G['charset'], 'GBK');
    //$msg = iconv("UTF-8", "GBK", strip_tags($message));
    $msg = rawurlencode($msg);
    //$title = iconv("UTF-8", "GBK", strip_tags($subject));
    $title = diconv(strip_tags($subject), $_G['charset'], 'GBK');
    $title = rawurlencode($title);
    $url = $sendmessage_url . '?msg=' . $msg . '&receiver=' . $username . '&title=' . $title;
    file_get_contents($url);
}

/**
 * 更新用户积分
 * @param type $action
 * @param type $uid 
 */
function library_update_credit_by_action($action, $uid) {
    global $_G;
    $credit_on = $_G['cache']['plugin']['library']['credit_on'];
    if ($credit_on) {
        updatecreditbyaction($action, $uid);
    }
}

/**
 *
 */
function library_build_cache_bookategory() {
    $data = array();
    $query = C::t('#library#library_category')->fetch_all_by_displayorder();

    foreach ($query as $value) {
        $value['name'] = dhtmlspecialchars($value['name']);
        $data[$value['cid']] = $value;
    }
    foreach ($data as $key => $value) {
        $cup = $value['cup'];
        $data[$key]['level'] = 0;
        if ($cup && isset($data[$cup])) {
            $data[$cup]['children'][] = $key;
            while ($cup && isset($data[$cup])) {
                $data[$key]['level'] += 1;
                $cup = $data[$cup]['cup'];
            }
        }
    }

    savecache('library_bookcategory', $data);
}

function library_showBookCateboryList($selectName = 'category', $values = '') {
    global $_G;
    loadcache('library_bookcategory');
    $selects = explode(',', $values);
    $category = $_G['cache']['library_bookcategory'];
    $return = '<ul id="bookCategoryList" name="' . $selectName . '">';
    foreach ($category as $key => $value) {
        if ($value['level'] == 0) {
            $return .= library_showMutilList($key, 0, $selects);
        }
    }
    $return .= '</ul>';
    return $return;
}

function library_showMutilList($key, $level = 0, $selects = array()) {
    global $_G;

    loadcache('library_bookcategory');
    $value = $_G['cache']['library_bookcategory'][$key];
    $return = '';
    $selected = is_array($selects) && in_array($value['cid'], $selects) ? ' selected="selected"' : '';
    if ($level == 2) {
        $return .= '<li value="' . $value['cid'] . '"' . $selected . '>'
            . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
            . '<a href="plugin.php?id=library:book&action=category&cid=' . $value['cid'] . '">' . $value['name'] . '</a>'
            . '</li>';
    } elseif ($level == 1) {
        $return .= '<li value="' . $value['cid'] . '"' . $selected . '>'
            . '&nbsp;&nbsp;&nbsp;&nbsp;'
            . '<a href="plugin.php?id=library:book&action=category&cid=' . $value['cid'] . '">' . $value['name'] . '</a>'
            . '</li>';
        for ($i = 0, $L = count($value['children']); $i < $L; $i++) {
            $return .= library_showMutilList($value['children'][$i], 2, $$selects);
        }
    } else {
        $return .= '<li value="' . $value['cid'] . '"' . $selected . '>'
            . '<a href="plugin.php?id=library:book&action=category&cid=' . $value['cid'] . '">' . $value['name'] . '</a>'
            . '</li>';
        for ($i = 0, $L = count($value['children']); $i < $L; $i++) {
            $return .= library_showMutilList($value['children'][$i], 1, $selects);
        }
    }
    return $return;
}

function library_showBookCateborySelect($selectName = 'category', $values = '') {
    global $_G;
    loadcache('library_bookcategory');
    $selects = explode(',', $values);
    $category = $_G['cache']['library_bookcategory'];
    $return = '<select id="bookCategory" name="' . $selectName . '" class="ps" multiple="multiple" size="10">';
    foreach ($category as $key => $value) {
        if ($value['level'] == 0) {
            $return .= library_showMutilSelect($key, 0, $selects);
        }
    }
    $return .= '</select>';
    return $return;
}

function library_categoryNumbers($sqladd = '')
{
	global $_G;
	
	$category = $_G['cache']['library_bookcategory'];
	
	foreach ($category as $key => $value) {
        if ($value['level'] == 0) {
            $value['num'] =  C::t('#library#library_book')->countBooks($value['cid'],$sqladd);
        }
        
        $arr[] = $value;
    }
    return $arr;
}

function library_categoryArray()
{
	global $_G;
	
	$category = $_G['cache']['library_bookcategory'];
	
	foreach ($category as $key => $value) {
        if ($value['level'] == 0) {
            $arr[] = $value;
        }
        
        
    }
    return $arr;
}

function library_showBookCateboryName($values)
{
	global $_G;
    loadcache('library_bookcategory');
    $selects = explode(',', $values);
    
    $category = $_G['cache']['library_bookcategory'];
    
	foreach ($category as $key => $value) {
	
		if(in_array($value['cid'],$selects))
		{
			$return .= $value['name'].' ';
		}
    }
    return $return;
}

function library_showMutilSelect($key, $level = 0, $selects = array()) {
    global $_G;

    loadcache('library_bookcategory');
    $value = $_G['cache']['library_bookcategory'][$key];
    $return = '';
    $selected = is_array($selects) && in_array($value['cid'], $selects) ? ' selected="selected"' : '';
    if ($level == 2) {
        $return .= '<option value="' . $value['cid'] . '"' . $selected . '>' . '&nbsp;&nbsp;&nbsp;&nbsp;' . $value['name'] . '</option>';
    } elseif ($level == 1) {
        $return .= '<option value="' . $value['cid'] . '"' . $selected . '>' . '&nbsp;&nbsp;' . $value['name'] . '</option>';
        for ($i = 0, $L = count($value['children']); $i < $L; $i++) {
            $return .= library_showMutilSelect($value['children'][$i], 2, $selects);
        }
    } else {
        $return .= '<option value="' . $value['cid'] . '"' . $selected . '>' . $value['name'] . '</option>';
        for ($i = 0, $L = count($value['children']); $i < $L; $i++) {
            $return .= library_showMutilSelect($value['children'][$i], 1, $selects);
        }
    }
    return $return;
}

function library_bookCover($bid, $size = 90, $returnSrc = false, $showPic = false) {
    global $_G;
    $book = C::t('#library#library_book')->fetch($bid);
    
    if ($book && $book['cover_image']) {
        $url = $_G['siteurl'] . 'data/attachment/common/' . $book['cover_image'];
    } elseif ($book && $book['douban_image']) {
        $url = $_G['siteurl'] . 'plugin.php?id=library:pic&file=' . $book['douban_image'];
    } else {
        $url = $_G['siteurl'] . 'plugin.php?id=library:pic';
    }
    if ($returnSrc) {
        return $url;
    } else {
        if($showPic) {
            $ret = '<img id="aimg_1" aid="1" src="' . $url . '" zoomfile="' . $url . '" file="' . $url . '" class="zoom" onclick="zoom(this, this.src, 0, 0, 0)" width="' . $size . '" inpost="1" onmouseover="showMenu({"ctrlid":this.id,"pos":"12"})" initialized="true">';
        } else {
            $ret = '<a href="' . $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $bid . '">';
            $ret .= '<img src="' . $url . '" width="' . $size . '" onerror="this.onerror=null;this.src=\'' . $url . '\'" />';
            $ret .= '</a>';
        }
        echo $ret;
    }
}

function library_bookCoverUrl($bid, $size = 90, $returnSrc = false, $showPic = false) {
	global $_G;
    $book = C::t('#library#library_book')->fetch($bid);
    
    if ($book && $book['cover_image']) {
        $url = $_G['siteurl'] . 'data/attachment/common/' . $book['cover_image'];
    } elseif ($book && $book['douban_image']) {
        $url = $_G['siteurl'] . 'plugin.php?id=library:pic&file=' . $book['douban_image'];
    } else {
        $url = $_G['siteurl'] . 'plugin.php?id=library:pic';
    }
    echo $url;
}

function library_bookPreview($obj, $size = 90, $showPic = false) {
    $url = $_G['siteurl'] . 'data/attachment/common/' . $obj['attachment'];
    if ($showPic) {
        $ret = '<img id="aimg_1" aid="1" src="' . $url . '" zoomfile="' . $url . '" file="' . $url . '" class="zoom" onclick="zoom(this, this.src, 0, 0, 0)" width="' . $size . '" inpost="1" onmouseover="showMenu({"ctrlid":this.id,"pos":"12"})" initialized="true">';
    } else {
        $ret = '<a href="' . $url . '" target="_blank">';
        $ret .= '<img src="' . $url . '" width="' . $size . '" onerror="this.onerror=null;this.src=\'' . $url . '\'" />';
        $ret .= '</a>';
    }
    echo $ret;
}

function library_bookCanLend($bid) {
    $book = C::t('#library#library_book')->fetch($bid);
    if (intval($book['lend_amount']) > 0) {
        $ret = '<font color="green">';
        $ret .= lang('plugin/library', 'can_lend');
    } else {
        $ret = '<font color="red">';
        $ret .= lang('plugin/library', 'can_not_lend');
    }
    $ret .= '</font>';
    echo $ret;
}

function library_getNormalizedFILES() {
    $newfiles = array();
    foreach ($_FILES as $fieldname => $fieldvalue)
        foreach ($fieldvalue as $paramname => $paramvalue)
            foreach ((array) $paramvalue as $index => $value)
                $newfiles[$fieldname][$index][$paramname] = $value;
    return $newfiles;
}

function library_import_book_from_douban($isbn, $forceRefresh = false, $toDb = true) {

    global $_G;
    include_once DISCUZ_ROOT . './source/plugin/library/class/DoubanBookImporter.php';

    loadcache('library_bookcategory');
    $doubanApiUrl = $_G['cache']['plugin']['library']['douban_api_v1'];
    $doubanApiKey = $_G['cache']['plugin']['library']['douban_apikey'];

    $msg = 'ISBN = ' . $isbn . '<br />';
    $importer = new DoubanBookImporter();
    $importer->setIsbn($isbn);
    $importer->setForceRefresh($forceRefresh);
    $doubanApiUrl && $importer->setDoubanApiUrl($doubanApiUrl);
    $doubanApiKey && $importer->setDoubanApiKey($doubanApiKey);

    $bookArray = $importer->getBookArray();
    if (count($bookArray) > 0) {
        if ($_G['charset'] != 'utf-8') {
            foreach ($bookArray as $key => $value) {
                strlen(trim($value)) > 0 && $bookData[$key] = diconv(trim($value), 'UTF-8');
            }
        } else {
            $bookData = $bookArray;
        }
        $bid = C::t('#library#library_book')->add($bookData);
        if ($forceRefresh == true && strlen($bookData['douban_image'])) {
            $file = $bookData['douban_image'];
            $_i = preg_replace('/[^\d]/', '', $file);
            $_i = sprintf("%012d", $_i);
            $dir1 = substr($_i, 0, 4);
            $dir2 = substr($_i, 4, 4);
            $datadir = './data/library/pic/' . $dir1 . '/' . $dir2 . '/';
            $pic = $datadir . $file;
            if (file_exists(DISCUZ_ROOT . $pic)) {
                @unlink(DISCUZ_ROOT . $pic);
            }
        }
        if ($bid) {
            $ret['success'] = true;
            $msg .= lang('plugin/library', 'douban_import_success');
            $msg .= lang('plugin/library', 'book_name') . ' = ' . $bookData['title'] . '<br />';
            $ret['data'] = $bookData;
            $ret['bid'] = $bid;
        }
    }
    $ret['msg'] = $msg;

    return $ret;
}


