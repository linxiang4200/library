<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_book extends discuz_table {

    public function __construct() {

        $this->_table = 'library_book';
        $this->_pk = 'bid';
        parent::__construct();
    }

    public function add($data) {
        $isIsbn10 = strlen($isbn) == 10 ? true : false;
        $isbn = $isIsbn10 ? $data['isbn10'] : $data['isbn13'];
        if (empty($isbn)) {
            return false;
        } else {
            $book = $this->fetch_by_isbn($isbn, $isIsbn10);
        }
        $book = array_merge($book, $data);
        $book['dateline'] = TIMESTAMP;
        $this->insert($book, true, true);
        return $this->insert_id();
    }
    
    public function insert_id()
    {
    	return DB::insert_id();
    }

    /**
     * 获取 book 
     * @param type $isbn
     * @param type $isIsbn10
     * @return array 
     */
    public function fetch_by_isbn($isbn, $isIsbn10 = false) {
        $book = array();
        if ($isbn) {
            $book = DB::fetch_first('SELECT * FROM %t WHERE ' . ($isIsbn10 ? 'isbn10' : 'isbn13') . '=%s', array($this->_table, addslashes($isbn)));
        }
        return $book;
    }

    public function increase($bids, $datas) {
        $bids = dintval((array) $bids, true);
        $sql = array();
        $allowkey = array(
            'store_amount',
            'lend_amount',
            'lended_amount',
            'circulation_count',
            'reservation_count');
        foreach ($datas as $key => $value) {
            if (($value = intval($value)) && $value && in_array($key, $allowkey)) {
                $sql[] = "`$key`=`$key`+'$value'";
            }
        }
        if (!empty($sql)) {
            //流通数+1
            $sql[] = "`circulation_count`=`circulation_count`+'1'";
            //记录最后流通时间
            $sql[] = "`last_circulation_timeline`='" . TIMESTAMP . "'";
            return DB::query("UPDATE " . DB::table($this->_table) . " SET " . implode(',', $sql) . " WHERE bid IN (" . dimplode($bids) . ")", 'UNBUFFERED');
        }
    }

    public function getBooksByCategoryId($cid, $limit, $url = '') {
        $category = C::t('#library#library_category')->fetch($cid);
        //$sqladd = " category LIKE '%|" . $category['name'] . "|%'";
        //debug($sqladd);
        
        $type = 'search';
        $sqladd = " cids = '$cid'";
        
        return $this->getBooks($type, $limit, true, $sqladd, $url);
    }
    
    function countBooks($cid = '',$where = '')
    {
    	$table = DB::table('library_book');
    	if($cid > 0)
    	{
    		$condition = " AND cids='$cid' ";
    		
    	}
    	
    	
    	return DB::result_first("SELECT COUNT(*) FROM " . $table . ' WHERE 1=1 ' . $condition . $where);
    }
    

    function getBooks($type, $limit, $pages = false, $sqladd = '', $url = '', $sort = '') {
        global $_G, $multi;
        $data = array();
        $page = intval($_G['gp_page']);
        $page = $page > 0 ? $page : 1;
        $start = !$pages ? 0 : ($page - 1) * $limit;
        $table = DB::table('library_book');
        switch($sort)
	    {
	    	case 'title':$sort='title';break;
	    	case 'author':$sort='author';break;
	    	case 'publisher':$sort='publisher';break;
	    	case 'pubdate':$sort='pubdate';break;
	    	case 'format':$sort='format';break;
	    	case 'category':$sort='cids';break;
	    	case 'notes':$sort='notes';break;
	    	case 'tags':$sort='tags';break;
	    	default: $sort='title';break;
	    }
        
        $sortsql = isset($sort) ? "  order by CONVERT( $sort USING gbk ) asc " : ' order by CONVERT( title USING gbk ) asc ';
        if ($type == 'index') {
            $where = $sqladd ? "WHERE $sqladd" : '';
        } elseif ($type == 'search') {
            $where = $sqladd ? "WHERE $sqladd" : '';
        }
		
        $type != 'index' && $count = DB::result_first("SELECT COUNT(*) FROM " . $table . " $where");
        if ($count || $type == 'index') {
            $sql = "SELECT * FROM " . $table . " $where $sortsql LIMIT $start,$limit";
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
