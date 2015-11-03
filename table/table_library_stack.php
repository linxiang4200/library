<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_stack extends discuz_table {

	protected $table_c;
	protected $table_b;
	
	public function __construct() {

	$this->_table = 'library_stack';
	$this->table_c = 'library_stack_category';
	$this->table_b = 'library_book';
	$this->_pk = 'uid';
	parent::__construct();
    }

    //'当前状态:0:提交申请；1:准备书籍；2:排队；3；等待取书；-1:过期',

	public function get_shuku_status($uid)
	{
		$data = DB::fetch_first('SELECT * FROM %t WHERE uid=%s', array($this->_table, addslashes($uid)));
		
		if(empty($data))
		{
			return 0;
		}else{
			return 1;
		}
	}
	
	public function get_shuku_stack($uid)
	{
		$data = DB::fetch_first('SELECT * FROM %t WHERE uid=%s', array($this->_table, addslashes($uid)));
		if(!empty($data))
		{
			$data['shengyu_time'] = intval(($data['shiyong_time'] - time())/86400);
			$data['total_time'] = intval(($data['daoqi_time'] - $data['create_time'])/86400);
			$data['total_time_percent'] = intval(($data['shengyu_time'] / $data['total_time'])*100);
			
			//已添加图书数量
			$data['book_num'] = C::t('#library#library_stack')->get_book_num($uid);
			$data['shengyu_book'] = $data['capacity'] - $data['book_num'];
			$data['shengyu_book_percent'] = intval(($data['shengyu_book']/$data['capacity'])*100);
			
			//分类数量
			$data['category_num'] = C::t('#library#library_stack')->get_category_num($uid);
		}
		return $data;
	}
	
	public function add($data)
	{
		$this->insert($data, true, false);
		return $this->insert_id();
	}
	
	public function insert_id()
    {
    	return DB::insert_id();
    }
    
    public function get_book_num($uid)
    {
    	return $data = DB::result_first('SELECT count(*) FROM %t WHERE uid=%s', array($this->table_b, addslashes($uid)));
    }
    
	public function get_category_num($uid)
    {
    	return $data = DB::result_first('SELECT count(*) FROM %t WHERE uid=%s', array($this->table_c, addslashes($uid)));
    }
    
    public function get_shuku_category($uid)
    {
    	return $data = DB::fetch_all('SELECT * FROM %t WHERE uid=%s', array($this->table_c, addslashes($uid)));
    }
    
    public function del_shuku_cat($ids)
    {
    	$table = 'pre_'.$this->table_c;
    	return DB::query("DELETE FROM $table WHERE cat_id in ($ids)");
    }
    
    public function add_cat($data)
    {
    	return DB::insert($this->table_c, $data, true, false);
    }
    
	public function edit_shuku_cat_name($cat_name,$cat_id)
    {
    	return DB::query("UPDATE " . DB::table($this->table_c) . " SET cat_name='$cat_name' WHERE cat_id = '$cat_id'", 'UNBUFFERED');
    }
    
	public function serilaze_ids($ids)
    {
    	$arr = explode(',',$ids);
    	//array_filter($arr);
    	foreach($arr as $key => $r)
    	{
    		$arr[$key] = intval($r);
    		if($arr[$key] == 0)unset($arr[$key]);
    	}
    	
    	return implode(',',$arr);
    }

    
    
	

    

}