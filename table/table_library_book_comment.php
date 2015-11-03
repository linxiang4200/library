<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_library_book_comment extends discuz_table {

	public function __construct() {

		$this->_table = 'library_book_comment';
		$this->_pk = 'bcid';
		parent::__construct();
	}

	public function delete($aid) {
		return parent::delete($aid);
	}

	public function fetch_all_by_bid($bid) {
		$data = array();
		if ($bid) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE bid=%s', array($this->_table, addslashes($bid)));
		}
		return $data;
	}

	/**
	 * 添加
	 * @param array $data
	 */
	public function add($data = array()) {
		global $_G;
		if (intval($data['bid']) > 0 && !empty($data)) {
			$data['uid'] = $_G['uid'];
			$data['dateline'] = TIMESTAMP;
			return $this->insert($data, true, true);
		}
		return false;
	}

	public function fetch_all_for_page($sqladd = '', $limit = 20, $page = 1, $url = '') {
		global $commentMulti;
		$data = array();
		$page = $page > 0 ? $page : 1;
		$start = ($page - 1) * $limit;
		$table = DB::table($this->_table);
		$where = $sqladd ? "WHERE $sqladd" : '';
		$count = DB::result_first("SELECT COUNT(*) FROM " . $table . " $where");
		if ($count) {
			$sql = "SELECT * FROM " . $table . " $where ORDER BY bcid DESC LIMIT $start,$limit";
			$query = DB::query($sql);
			while ($result = DB::fetch($query)) {
				isset($result['dateline']) && $result['udateline'] = dgmdate($result['dateline'], 'u');
				$data[$result['bcid']] = $result;
			}
			$commentMulti = multi($count, $limit, $page, $url);
		}
		return $data;
	}

	public function fetch_all_for_page_by_bid($bid, $limit = 20, $page = 1, $url = '') {
		if (!intval($bid) > 0) {
			throw new Exception('parame bid must > 0');
		}
		$sqladd = 'bid = ' . $bid;
		return $this->fetch_all_for_page($sqladd, $limit, $page, $url);
	}

	/** 
	 * 按照 bid 删除图书
	 * 
	 * @param $bid
	 * 
	 * @return 
	 */
	public function delete_by_bid($bid) {

		if ($bid) {
			return DB::fetch_all('DELETE FROM %t WHERE bid=%s', array($this->_table, addslashes($bid)));
		}

		return false;
	}
}
