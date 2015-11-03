<?php

/*
 * (C)2001-2012 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * Time: 2012-4-9 14:21:13
 * Id: superbbs.class.php 
 * @author: GavinYao<yaojungang@comsenz.com> 
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * Description of library
 *
 */
class plugin_library {

    const ISBN_PREG = '/[^\d|^x|^X]+/';

    public function __construct() {

    }

    function common() {

        global $_G;

        if (!defined('LIBRARY_ROOT')) {
            define('LIBRARY_ROOT', DISCUZ_ROOT . './source/plugin/library');
        }

        include_once LIBRARY_ROOT . '/function/function_library.php';
        include_once LIBRARY_ROOT . '/library_version.php';

        $this->_setLibraryAdmin();

        $bookCategoryList = '';
        $bookCategoryList = library_showBookCateboryList('cids[]', array());
    }

    function global_footerlink0() {

        global $_G;
        if (!$_G['library']['y109_powered']) {
            $_G['library']['y109_powered'] = 1;
            $_str = '<span class="pipe">|</span>';
            $_str .= '<strong>';
            $_str .= '<a href="http://y109.jzland.com/" target="_blank">y109</a>';
            $_str .= '</strong>';
            return $_str;
        }

    }

    function _setLibraryAdmin() {

        global $_G;
        $_G['library']['adminid'] = 0;
        if (!empty($_G['uid'])) {
            $groupid = $_G['groupid'];
            $extgroupids = $_G['member']['extgroupids'] ? explode("\t", $_G['member']['extgroupids']) : array();
            $extgroupidsarray = array();
            foreach (array_unique(array_merge($extgroupids, array($groupid))) as $extgroupid) {
                if ($extgroupid) {
                    $extgroupidsarray[] = $extgroupid;
                }
            }
            $libraryAdminGroupIds = unserialize($_G['cache']['plugin']['library']['admin_group_ids']);
            foreach ($extgroupidsarray as $_groupId) {
                if (in_array($_groupId, $libraryAdminGroupIds)) {
                    $_G['library']['adminid'] = 1;
                    return;
                }
            }
        }
    }

}

