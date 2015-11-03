<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_bookthelf extends discuz_table {
    const TYPE_FAVOURITE = 0;

    public function __construct() {

        $this->_table = 'library_bookthelf';
        $this->_pk = 'btid';
        parent::__construct();
    }

    public function delete($btid) {
        return parent::delete($btid);
    }

    public function fetch_all_by_uid($uid) {
        $data = array();
        if ($uid) {
            $data = DB::fetch_all('SELECT * FROM %t WHERE uid = %d', array($this->_table, addslashes($uid)));
        }
        return $data;
    }

    public function fetch_by_uid_type($uid, $type = 0) {
        $data = array();
        if ($uid) {
            $data = DB::fetch_first('SELECT * FROM %t WHERE uid = %d AND type = %d',
             array($this->_table, addslashes($uid), addslashes($type)));
        }
        return $data;
    }

    /**
    * 收藏夹，添加
    */
    public function favourite_add($bid, $uid = 0) {
        global $_G;
        $uid = $uid > 0 ? $uid : $_G['uid'];
        $favourite = DB::fetch_first('SELECT * FROM %t WHERE uid = %s AND type = %s' ,
           array($this->_table , addslashes($uid) , self::TYPE_FAVOURITE ));

        if(!$favourite){
            $btid = $this->add(array('type' => 0,'name'=>'FAVOURITE_'.$uid),true,true);
        }else{
            $btid = $favourite['btid'];
        }

        $data = array(
            'btid' => $btid,
            'bid' => $bid,
            'type' => self::TYPE_FAVOURITE,
            );
        $btiid = C::t('#library#library_bookthelf_item')->add($data);
        return $btiid;
    }

    public function favourite_delete($btiid) {
        $bookthelfItem = C::t('#library#library_bookthelf_item')->fetch($btiid);
        if(!$bookthelfItem) {
            throw new Exception('bookthelf_item fetch result is empty btiid = ' . $btiid);
        }
        $ret = C::t('#library#library_bookthelf_item')->delete($btiid);
        $ret && $this->increase(array($bookthelfItem['btid']), array('amount' => -1));
        return $btiid;
    }

    /**
     * 添加
     * @param array $data
     */
    public function add($data = array()) {
        global $_G;
        if (!empty($data)) {
            $data['uid'] = $_G['uid'];
            $data['dateline'] = TIMESTAMP;
            return $this->insert($data, true, true);
        }
        return false;
    }

    public function fetch_all_for_page($sqladd = '', $limit = 20, $page = 1, $url = '') {
        global $library_bookthelfMulti;
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
                $data[] = $result;
            }
            $library_bookthelfMulti = multi($count, $limit, $page, $url);
        }
        return $data;
    }

    public function fetch_all_for_page_by_uid($uid, $limit = 20, $page = 1, $url = '') {
        if (!intval($uid) > 0) {
            throw new Exception('parame uid must > 0');
        }
        $sqladd = 'uid = ' . $uid;
        return $this->fetch_all_for_page($sqladd, $limit, $page, $url);
    }

    public function increase($btids, $datas) {
        $btids = dintval((array) $btids, true);
        $sql = array();
        $allowkey = array(
            'amount',
            );
        foreach ($datas as $key => $value) {
            if (($value = intval($value)) && $value && in_array($key, $allowkey)) {
                $sql[] = "`$key`=`$key`+'$value'";
            }
        }
        if (!empty($sql)) {
            return DB::query("UPDATE " . DB::table($this->_table) . " SET " . implode(',', $sql) . " WHERE btid IN (" . dimplode($btids) . ")", 'UNBUFFERED');
        }
    }

}
