<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_circulation extends discuz_table {

    const TYPE_LEND = 0;
    const TYPE_RETURN = 1;

    public function __construct() {

	$this->_table = 'library_circulation';
	$this->_pk = 'cid';
	parent::__construct();
    }

    public function getType() {
	$types = array(
	    self::TYPE_LEND => lang('plugin/library', 'lend'),
	    self::TYPE_RETURN => lang('plugin/library', 'return'),
	);
	return $types;
    }

    public function fetch_all_by_bid($bid) {
	$data = array();
	if ($bid) {
	    $data = DB::fetch_all('SELECT * FROM %t WHERE bid=%s ORDER BY cid DESC', array($this->_table, addslashes($bid)));
	}
	return $data;
    }

    

}