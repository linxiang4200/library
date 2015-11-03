<?php

/*
 * (C)2001-2011 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * GavinYao <Yaojungnag@comsenz.com>
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_library_category extends discuz_table {

    public function __construct() {

        $this->_table = 'library_category';
        $this->_pk = 'cid';
        parent::__construct();
    }

    /**
     * 根据分类id获取分类名称,包括上级分类
     * @param type $cids
     * @return type
     */
    public function getCategoryText($cids) {
        $data = array();
        $ret = '';
        if(empty($cids)){
            return $ret;
        }
        if (strpos($cids, ',')) {
            $cids = explode(',', $cids);
        }
        if (is_string($cids)) {
            $cids = array($cids);
        }
        $categorys = parent::fetch_all($cids);
        if ($categorys) {
            foreach ($categorys as $category) {
                if (intval($category['cup']) > 0) {
                    $pcategory = $this->fetch($category['cup']);
                    if (intval($pcategory['cup']) > 0) {
                        $ppcategory = $this->fetch($pcategory['cup']);
                        $data[] = $ppcategory['name'];
                    }
                    $data[] = $pcategory['name'];
                }
                $data[] = $category['name'];
            }
        }
        if (!empty($data)) {
            $data = array_unique($data);
            $ret = implode('|', $data);
        }
        strlen($ret) > 0 && $ret = '|' . $ret . '|';
        return $ret;
    }

    public function fetch_all_by_displayorder() {
        return DB::fetch_all("SELECT * FROM %t ORDER BY displayorder", array($this->_table), $this->_pk);
    }

    public function count_by_cup($cup) {
        return DB::result_first('SELECT COUNT(*) FROM %t WHERE cup = %d', array($this->_table, $cup));
    }

}