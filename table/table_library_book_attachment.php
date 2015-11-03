<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_library_book_attachment extends discuz_table {

	public function __construct() {

		$this->_table = 'library_book_attachment';
		$this->_pk = 'aid';
		parent::__construct();
	}

	public function delete($aid) {
		$attachment = $this->fetch($aid);
		$basedir = !getglobal('setting/attachdir') ? (DISCUZ_ROOT . './data/attachment') : getglobal('setting/attachdir');
		$oldFile = $basedir . './common/' . $attachment['attachment'];
		file_exists($oldFile) && @unlink($oldFile);
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
	 * 添加图书预览图片
	 * @param type $bid
	 * @param type $newCoverUrl
	 */
	public function add($bid, $newAttachUrl, $isimage = 1, $atts = array()) {
		if ($bid > 0 && strlen($newAttachUrl) > 0) {
			$data = array();
			$data['bid'] = $bid;
			$data['attachment'] = $newAttachUrl;
			$data['isimage'] = $isimage;
			$data['description'] = json_encode($atts);
			$data['dateline'] = TIMESTAMP;
			return $this->insert($data, true, true);
		}
		return false;
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
