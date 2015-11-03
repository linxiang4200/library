<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_library_bookthelf_item extends discuz_table {

	public function __construct() {

		$this->_table = 'library_bookthelf_item';
		$this->_pk = 'btiid';
		parent::__construct();
	}

	public function delete($btiid) {
		return parent::delete($btiid);
	}

	public function fetch_all_by_btid($btid) {
		$data = array();
		if ($btid) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE btid=%s', array($this->_table, addslashes($uid)));
		}
		return $data;
	}


	public function fetch_by_uid_type_bid($uid, $type, $bid) {
		$data = array();
		if ($uid && $bid) {
			$data = DB::fetch_first('SELECT * FROM %t WHERE uid = %s AND type = %s AND bid = %s',
				array($this->_table, addslashes($uid), addslashes($type), addslashes($bid))
			);
		}
		return $data;
	}

	/**
	 * 添加
	 * @param array $data
	 */
	public function add($data = array()) {
		global $_G;
		if (isset($data['btid']) 
			&& intval($data['btid'] > 0)
			&& isset($data['bid']) 
			&& intval($data['bid'] > 0)) {

				$bid = intval($data['bid']);
				$btid = intval($data['btid']);
				$type = isset($data['type']) ? intval($data['type']) : 0;
				$book = C::t('#library#library_book')->fetch($bid);
				$uid = isset($data['uid']) ? $data['uid'] : $_G['uid'];

				$data['uid'] = $uid;
				$data['type'] = $type;
				$data['dateline'] = TIMESTAMP;
				$data['title'] = $book['title'];
				$data['isbn13'] = $book['isbn13'];
				$old = $this->fetch_by_uid_type_bid($uid, $type, $bid);

				if($old) {
					return $this->update($old['btid'], $data);
				} else {
					$insertId = $this->insert($data, true, true);
					$insertId && C::t('#library#library_bookthelf')->increase(array($btid), array('amount' => 1));
					return $insertId;
				}
			}
		return false;
	}

	public function fetch_all_for_page($sqladd = '', $limit = 20, $page = 1, $url = '') {
		global $library_bookthelf_itemMulti;
		$data = array();
		$page = $page > 0 ? $page : 1;
		$start = ($page - 1) * $limit;
		$table = DB::table($this->_table);
		$where = $sqladd ? "WHERE $sqladd" : '';
		$count = DB::result_first("SELECT COUNT(*) FROM " . $table . " $where");
		if ($count) {
			$sql = "SELECT * FROM " . $table . " $where LIMIT $start,$limit";
			$query = DB::query($sql);
			while ($result = DB::fetch($query)) {
				isset($result['dateline']) && $result['dateline'] = dgmdate($result['dateline'], 'u');
				$r = $this->get_book_detail($result['bid']);
				
				$result['publisher'] = $r['publisher'];
				$result['pubdate'] = $r['pubdate'];
				$result['format'] = $r['format'];
				$result['category'] = $r['category'];
				$result['author'] = $r['author'];
				$result['tags'] = $r['tags'];
				$result['notes'] = $r['notes'];
				$result['notes'] = $r['notes'];
				$data[] = $result;
			}
			$library_bookthelf_itemMulti = multi($count, $limit, $page, $url);
		}
		return $data;
	}
	
	public function get_book_detail($bid)
	{
		$table = DB::table('library_book');
		$sql = "SELECT * FROM $table where bid = '$bid'";
		$query = DB::query($sql);
		
		return $result = DB::fetch($query);
	}

	public function fetch_all_for_page_by_btid($btid, $limit = 20, $page = 1, $url = '') {
		if (!intval($btid) > 0) {
			throw new Exception('parame btid must > 0');
		}
		$sqladd = 'btid = ' . $btid . ' ORDER BY dateline DESC';
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
