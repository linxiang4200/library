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
if (empty($_G['library']['adminid']) || 1 != $_G['library']['adminid']) {
    showmessage(lang('plugin/library', 'only_admin'));
}

$bookCategoryList = '';
$bookCategoryList = library_showBookCateboryList('cids[]', $book['cids']);

$actionarr = array('index', 'catalog', 'delete', 'add', 'edit', 'return_book', 'lend_book');
$action = in_array($_GET['action'], $actionarr) ? $_GET['action'] : 'index';
$navtitle = lang('plugin/library', 'store');

//设置默认值
$store_default_warehouse = $_G['cache']['plugin']['library']['store_default_warehouse'];
$store_default_accession_number = $_G['cache']['plugin']['library']['store_default_accession_number'];
$store_default_owner = $_G['cache']['plugin']['library']['store_default_owner'];

if ('index' == $action) {
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $page < 1 && $page = 1;
    $perpage = 20;
    $start = ($page - 1) * $perpage;

    $list = array();
    $objs = C::t('#library#library_store')->range($start, $perpage);
    foreach ($objs as $obj) {
        $obj['dateline'] = dgmdate($obj['dateline'], 'u');
        $list[] = $obj;
    }
    $multi = simplepage(count($list), $perpage, $page, 'plugin.php?id=library:store');

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
    include_once template('library:store_index');
} elseif ('add' == $action) {
    $navtitle = lang('plugin/library', 'add') . lang('plugin/library', 'book') . lang('plugin/library', 'store');
    $bid = intval($_GET['bid']);
    $bid || showmessage(lang('plugin/library', 'book_bid_null'));
    $book = C::t('#library#library_book')->fetch($bid);
    $book || showmessage(lang('plugin/library', 'can_not_find_book'));

    // 新建库存
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        global $_G;
        $_data = array(
            'warehouse' => trim($_GET['warehouse']),
            'accession_number' => trim($_GET['accession_number']),
            'owner' => trim($_GET['owner']),
        );

        $ret = C::t('#library#library_store')->add($bid, $_data, false, false);
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

    $store = array(
        'warehouse' => $store_default_warehouse,
        'accession_number' => $store_default_accession_number,
        'owner' => $store_default_owner,
    );
    include_once template('library:store_form');
} elseif ('edit' == $action) {
    $navtitle = lang('plugin/library', 'edit') . lang('plugin/library', 'book') . lang('plugin/library', 'store');
    $bid = intval($_GET['bid']);
    $bid || showmessage(lang('plugin/library', 'book_bid_null'));
    $book = C::t('#library#library_book')->fetch($bid);
    $book || showmessage(lang('plugin/library', 'can_not_find_book'));
    $sid = intval($_GET['sid']);
    $store = C::t('#library#library_store')->fetch($sid);
    $store || showmessage(lang('plugin/library', 'can_not_find_store'));


    // 编辑库存
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        global $_G;
        $_data = array(
            'warehouse' => trim($_GET['warehouse']),
            'accession_number' => trim($_GET['accession_number']),
            'owner' => trim($_GET['owner']),
        );

        $ret = C::t('#library#library_store')->update($sid, $_data);
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

    include_once template('library:store_form');
} elseif ('catalog' == $action) {
    $navtitle = lang('plugin/library', 'catalog');
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        global $_G;
        $_data = array(
            'warehouse' => urlencode($_GET['warehouse']),
            'accession_number' => urlencode($_GET['accession_number']),
            'owner' => urlencode($_GET['owner']),
        );
        $replace = $_GET['replace'];
        $force = $_GET['force'];
        $data = base64_encode(json_encode($_data));
        $items = explode("\n", $_POST['isbns']);
        $_isbns = array();
        foreach ($items as $item) {
            $item = preg_replace(plugin_library::ISBN_PREG, '', $item);
            if (!in_array($item, $_isbns) && strlen($item) > 0) {
                $_isbns[] = substr($item, 0, 13);
            }
        }
        $isbns = implode(',', $_isbns);
        if (empty($isbns)) {
            showmessage(lang('plugin/library', 'please_check_input'), $_G['siteurl'] . 'plugin.php?id=library:store&action=catalog&hash=' . FORMHASH, array(), array('timeout' => 0, 'alert' => 'error', 'striptags' => false));
        }
        if (!intval($_GET['checking'])) {
            $remoteUrl = $_G['siteurl']
                . 'plugin.php?id=library:store&action=catalog'
                . '&isbns=' . $isbns
                . '&data=' . $data
                . '&replace=' . $replace
                . '&force=' . $force
                . '&checking=1';
            showmessage('<h4 class="infotitle1">' . lang('plugin/library', 'douban_importing') . '</h4><img src="static/image/admincp/ajax_loader.gif" class="marginbot">'
                , $remoteUrl, array(), array('timeout' => 1, 'refreshtime' => 1));
        }
    } else {
        $checking = intval($_GET['checking']);
        $_isbns = $_GET['isbns'];
        if ($checking && strlen($_isbns) > 0) {
            $_data = json_decode(base64_decode($_GET['data']));
            $data = array();
            foreach ($_data as $key => $value) {
                $data[$key] = urldecode($value);
            }
            $replace = $_GET['replace'];
            $force = $_GET['force'];
            $itemSuccessCount = 0;
            $itemFalseCount = 0;
            $successes = array();
            $falsees = array();
            $isbns = explode(',', $_isbns);
            foreach ($isbns as $isbn) {
                // $auth = base64_encode(authcode(TIMESTAMP, 'ENCODE', $_G['config']['security']['authkey']));
                // $remoteUrl = $_G['siteurl'] . 'plugin.php?id=library:import_from_douban&json=1&isbn=' . $isbn . '&force=' . $force . '&auth=' . $auth;
                // $_ret = dfsockopen($remoteUrl, 0, '', '', false, '127.0.0.1', 30000);
                // $_ret = file_get_contents($remoteUrl);

                // $ret1 = json_decode($_ret);
                $ret1 = library_import_book_from_douban($isbn);
                if ($ret1['success']) {
                    $bid = $ret1['bid'];
                    //编目
                    try {
                        $ret2 = C::t('#library#library_store')->add($isbn, $data, $replace, true);
                        if ($ret2) {
                            $successes[] = $ret1['msg'];
                            $itemSuccessCount++;
                        } else {
                            $falsees[] = $ret1['msg'];
                            $itemFalseCount++;
                        }
                    } catch (Exception $e) {
                        $falsees[] = $isbn . ' : ' . lang('plugin/library', 'douban_import_success_db_fail') . $e->getMessage();
                        $itemFalseCount++;
                    }
                } else {
                    $falsees[] = $ret1['msg'];
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
            if ($itemSuccessCount == 1 && $bid) {
                $fowardUrl = $_G['siteurl'] . 'plugin.php?id=library:book&action=info&bid=' . $bid;
            } else {
                $fowardUrl = $_G['siteurl'] . 'plugin.php?id=library:book';
            }
            showmessage($msg, $fowardUrl, array(), array('timeout' => ($itemFalseCount > 0 ? 0 : 1), 'alert' => ($itemFalseCount > 0 ? 'error' : 'right'), 'striptags' => false, 'refreshtime' => 2 + $itemSuccessCount), false);
        }

        $store = array(
            'warehouse' => $store_default_warehouse,
            'accession_number' => $store_default_accession_number,
            'owner' => $store_default_owner,
        );
        include_once template('library:store_catalog');
    }
} elseif ('lend_book' == $action) {
    $navtitle = lang('plugin/library', 'lend_book');
    $sno = isset($_GET['sno']) ? preg_replace('/[^0-9]/', '', $_GET['sno']) : '';
    include_once template('library:circulation_lend');
} elseif ('return_book' == $action) {
    $navtitle = lang('plugin/library', 'return_book');
    $sno = isset($_GET['sno']) ? preg_replace('/[^0-9]/', '', $_GET['sno']) : '';
    $username = addslashes($_GET['username']);
    include_once template('library:circulation_return');
} elseif ('delete' == $action) {
    $sid = $_GET['sid'];
    $msg = '';
    try {
        $ret = C::t('#library#library_store')->delete($sid);
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }

    if ($ret) {
        $success = 1;
        $msg .= lang('plugin/library', 'operate_success');
    } else {
        $success = 0;
        $msg .= lang('plugin/library', 'operate_fail');
    }
    showmessage($msg
        , $_SERVER['HTTP_REFERER'], array()
        , array('timeout' => ($success ? 1 : 0), 'alert' => ($success ? 'right' : 'error'), 'striptags' => false,));
}

