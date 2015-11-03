<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_store extends discuz_table {

    const STATUS_NOMAL = 0;
    const STATUS_LENDED = 1;
    const STATUS_RESERVED = 2;
    const STATUS_OVERTIME = -1;

    public function __construct() {

	$this->_table = 'library_store';
	$this->_pk = 'sid';
	parent::__construct();
    }

    public function fetch_all_by_bid($bid) {
	$data = array();
	if ($bid) {
	    $data = DB::fetch_all('SELECT * FROM %t WHERE bid=%s', array($this->_table, addslashes($bid)));
	}
	return $data;
    }

    public function fetch_all_by_uid($uid) {
	$data = array();
	if ($uid) {
	    $data = DB::fetch_all('SELECT * FROM %t WHERE uid=%s', array($this->_table, addslashes($uid)));
	}
	return $data;
    }

    public function fetch_by_sno($sno) {
	$data = array();
	if ($sno) {
	    $data = DB::fetch_first('SELECT * FROM %t WHERE sno=%s', array($this->_table, addslashes($sno)));
	}
	return $data;
    }

    public function add($isbn, $data, $replace = true) {
	$book = C::t('#library#library_book')->fetch_by_isbn($isbn);
	if (empty($book)) {
	    throw new Exception(lang('plugin/library', 'can_not_find_book') . ' ISBN = ' . addslashes($isbn));
	    return false;
	}
	$_store = $this->fetch_all_by_bid($book['bid']);
	if ($replace && !empty($_store) && count($_store) > 1) {
	    throw new Exception(lang('plugin/library', 'can_not_select_store'));
	    return false;
	}
	if ($replace && count($_store) == 1) {
	    $_data = $_store[0];
	} else {
	    $_data = array();
	}
	$_data['bid'] = $book['bid'];
	$_data['title'] = $book['title'];
	$_data['isbn13'] = $book['isbn13'];
	$_data['dateline'] = TIMESTAMP;
	$_data['store_amount'] = 1;
	$_data['lend_amount'] = 1;
	if ($replace && !empty($_data['sid']) && intval($_data['sid']) > 0) {
	    return $this->update($_data['sid'], array_merge($_data, $data));
	} else {
	    $sno = $this->getNextSno($book['isbn13']);
	    $_data['sno'] = $sno;
	    $retId = $this->insert(array_merge($_data, $data), true);
	    if ($retId > 0) {
		//更新 book 中的数量
		C::t('#library#library_book')->increase(array($book['bid']), array('store_amount' => 1, 'lend_amount' => 1));
	    }
	    return $retId;
	}
    }

    function getNextSno($isbn) {
	$book = C::t('#library#library_book')->fetch_by_isbn($isbn);
	if (empty($book)) {
	    throw new Exception(lang('plugin/library', 'can_not_find_book'));
	    return;
	}
	$stores = $this->fetch_all_by_bid($book['bid']);
	$bigSno = 0;
	foreach ($stores as $store) {
	    $_sno = str_replace($isbn, '', $store['sno']);
	    intval($_sno) > $bigSno && $bigSno = intval($_sno);
	}
	$bigSno++;
	$bigSno = $isbn . sprintf('%03d', $bigSno);
	return $bigSno;
    }

    public function increase($sids, $datas) {
	$sids = dintval((array) $sids, true);
	$sql = array();
	$allowkey = array('lend_amount', 'lended_amount', 'renew_count');
	foreach ($datas as $key => $value) {
	    if (($value = intval($value)) && $value && in_array($key, $allowkey)) {
		$sql[] = "`$key`=`$key`+'$value'";
	    }
	}
	if (!empty($sql)) {
	    return DB::query("UPDATE " . DB::table($this->_table) . " SET " . implode(',', $sql) . " WHERE sid IN (" . dimplode($sids) . ")", 'UNBUFFERED');
	}
    }

    

    function getStores($type, $limit, $pages = false, $sqladd = '', $url = '') {
	global $_G, $multi;
	$data = array();
	$page = intval($_G['gp_page']);
	$page = $page > 0 ? $page : 1;
	$start = !$pages ? 0 : ($page - 1) * $limit;
	$table = DB::table('library_store');
	if ($type == 'index') {
	    $where = $sqladd ? "WHERE $sqladd" : '';
	} elseif ($type == 'search') {
	    $where = $sqladd ? "WHERE $sqladd" : '';
	}

	$type != 'index' && $count = DB::result_first("SELECT COUNT(*) FROM " . $table . " $where");
	if ($count || $type == 'index') {
	    $sql = "SELECT * FROM " . $table . " $where LIMIT $start,$limit";
	    $query = DB::query($sql);
	    while ($result = DB::fetch($query)) {
		$result['dateline'] = dgmdate($result['dateline'], 'u');
		$data[] = $result;
	    }
	    if ($pages) {

		$multi = multi($count, $limit, $page, $url);
	    }
	}
	return $data;
    }


}
