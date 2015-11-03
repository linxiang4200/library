<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_reservation extends discuz_table {

    public function __construct() {

	$this->_table = 'library_reservation';
	$this->_pk = 'rid';
	parent::__construct();
    }

    //'当前状态:0:提交申请；1:准备书籍；2:排队；3；等待取书；-1:过期',

    const STATUS_APPLY = 0;
    const STATUS_PREPARATION = 1;
    const STATUS_QUEUE = 2;
    const STATUS_FETCH = 3;
    const STATUS_OVERTIME = -1;

    function getStatus($statusId = null) {
	$status = array(
	    self::STATUS_APPLY => lang('plugin/library', 'apply'),
	    self::STATUS_PREPARATION => lang('plugin/library', 'preparation'),
	    self::STATUS_QUEUE => lang('plugin/library', 'queue'),
	    self::STATUS_FETCH => lang('plugin/library', 'fetch'),
	    self::STATUS_OVERTIME => lang('plugin/library', 'overtime'),
	);
	if (isset($statusId) && isset($status[$statusId])) {
	    return $status[$statusId];
	}
	return $status;
    }

    /**
     * 获取下一个 预约序列号
     * @param type $isbn
     * @return string
     * @throws Exception 
     */
    private function getNextRno($isbn) {
	$book = C::t('#library#library_book')->fetch_by_isbn($isbn);
	if (empty($book)) {
	    throw new Exception(lang('plugin/library', 'can_not_find_book'));
	    return;
	}
	$reservations = $this->fetch_all_by_bid($book['bid']);
	$bigRno = 0;
	foreach ($reservations as $r) {
	    $_rno = str_replace($isbn, '', $r['rno']);
	    intval($_rno) > $bigRno && $bigRno = intval($_rno);
	}
	$bigRno++;
	$bigRno = $isbn . sprintf('%03d', $bigRno);
	return $bigRno;
    }

    /**
     * 获取下一个排队号码
     * @param type $bid
     * @return type 
     */
    private function getNextOrderNumber($bid) {
	$reservations = $this->fetch_all_by_bid($bid);
	$bigNo = 0;
	foreach ($reservations as $r) {
	    $_no = intval($r['order_number']);
	    $_no > $bigNo && $bigNo = $_no;
	}
	$bigNo++;
	return $bigNo;
    }

    /**
     * 获取排第一的预约
     * @param type $bid
     * @param array $exclude 排除这些 rid
     * @return array 
     */
    public function getFirstOrder($bid, $excludeRids = array()) {
	$reservations = $this->fetch_all_by_bid($bid);
	$minObj = null;
	foreach ($reservations as $r) {
	    if (in_array($r['rid'], $excludeRids)) {
		continue;
	    }
	    if (empty($minObj)) {
		$minObj = $r;
	    } else {
		intval($r['order_number']) < intval($minObj['order_number']) && $minObj = $r;
	    }
	}
	return $minObj;
    }

    public function fetch_all_by_bid($bid) {
	$data = array();
	if ($bid) {
	    $data = DB::fetch_all('SELECT * FROM %t WHERE bid=%s ORDER BY rid DESC', array($this->_table, addslashes($bid)));
	}
	return $data;
    }

    public function fetch_by_bid_and_uid($bid, $uid) {
	$data = array();
	if ($bid) {
	    $data = DB::fetch_first('SELECT * FROM %t WHERE bid=%s AND uid=%s', array($this->_table, addslashes($bid), addslashes($uid)));
	}
	return $data;
    }

    public function fetch_all_by_uid($uid) {
	$datas = array();
	if ($uid) {
	    $datas = DB::fetch_all('SELECT * FROM %t WHERE uid=%s', array($this->_table, addslashes($uid)));
	}
	return $datas;
    }
    
	

    

}