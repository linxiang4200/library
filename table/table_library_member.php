<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_member extends discuz_table {

    public function __construct() {

        $this->_table = 'library_member';
        $this->_pk = 'uid';
        parent::__construct();
    }

    public function add($data) {
        return $this->insert($data, true, true);
    }

    public function fetch_by_uid($uid) {
        $data = array();
        if ($uid) {
            $data = DB::fetch_first('SELECT * FROM %t WHERE uid=%s', array($this->_table, addslashes($uid)));
        }
        return $data;
    }

}