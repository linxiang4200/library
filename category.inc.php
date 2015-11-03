<?php

/*
 * (C)2001-2012 Comsenz Inc.
 * This is NOT a freeware, use is subject to license terms
 * Time: 2012-6-21 20:28:10
 * Id: credits.inc.php 
 * @author: GavinYao<yaojungang@comsenz.com> 
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
cpheader();
$operation = $_GET['operation'];
$operation = ($operation == 'delete') ? 'delete' : 'list';

include_once DISCUZ_ROOT. './source/plugin/library/function/function_library.php';
library_build_cache_bookategory();
loadcache('library_bookcategory');
global $_G;
$category = $_G['cache']['library_bookcategory'];

if($operation == 'list') {

    if(!submitcheck('editsubmit')) {

        showformheader('plugins&identifier=library&pmod=category');
        showtableheader();
        echo '<tr><td colspan="2">';
        showtableheader();
        showsubtitle(array('order', 'name', 'operation'));
        foreach ($category as $key=>$value) {
            if($value['level'] == 0) {
                echo library_showcategoryrow($key, 0, '');
            }
        }
        echo '<tr>
        <td class="td25">&nbsp;</td>
        <td colspan="3"><div><a class="addtr" onclick="addrow(this, 0, 0)" href="###">'.cplang('blogcategory_addcategory').'</a></div></td>
        </tr>';
        showtablefooter();
        echo '</td></tr>';

        showtableheader('', 'notop');
        showsubmit('editsubmit');
        showtablefooter();
        showformfooter();

        $langs = array();
        $keys = array('blogcategory_addcategory', 'blogcategory_addsubcategory', 'blogcategory_addthirdcategory');
        foreach ($keys as $key) {
            $langs[$key] = cplang($key);
        }
        echo <<<SCRIPT
<script type="text/JavaScript">
var rowtypedata = [
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="parentboard"><input type="text" class="txt" value="$lang[blogcategory_addcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="board"><input type="text" class="txt" value="$lang[blogcategory_addsubcategory]" name="newname[{1}][]"/></div>']],
	[[1,'<input type="text" class="txt" name="neworder[{1}][]" value="0" />', 'td25'], [3, '<div class="childboard"><input type="text" class="txt" value="$lang[blogcategory_addthirdcategory]" name="newname[{1}][]"/></div>']],
];
</script>
SCRIPT;

    } else {

        if($_POST['name']) {
            foreach($_POST['name'] as $key=>$value) {
                $sets = array();
                $value = trim($value);
                if($category[$key] && $category[$key]['name'] != $value) {
                    $sets['name'] = $value;
                }
                if($category[$key] && $category[$key]['displayorder'] != $_POST['order'][$key]) {
                    $sets['displayorder'] = $_POST['order'][$key] ? $_POST['order'][$key] : '0';
                }
                if($sets) {
                    C::t('#library#library_category')->update($key, $sets);
                }
            }
        }
        if($_POST['newname']) {
            foreach ($_POST['newname'] as $cup=>$names) {
                foreach ($names as $nameid=>$name) {
                    C::t('#library#library_category')->insert(array('cup' => $cup, 'name' => trim($name), 'displayorder'=>intval($_POST['neworder'][$cup][$nameid])));
                }
            }
        }

        if($_POST['settingnew']) {
            $_POST['settingnew'] = array_map('intval', $_POST['settingnew']);
            C::t('common_setting')->update_batch($_POST['settingnew']);
            updatecache('setting');
        }

        library_build_cache_bookategory();

        cpmsg('library:operate_success', 'action=plugins&identifier=library&pmod=category', 'succeed');
    }

} elseif($operation == 'delete') {

    if(!$_GET['cid'] || !$category[$_GET['cid']]) {
        cpmsg('library:category_not_found', '', 'error');
    }
    $cid = intval($_GET['cid']);
    $count = C::t('#library#library_category')->count_by_cup($cid);
    if($count > 0){
        cpmsg('library:operate_fail_children_category_not_null', 'action=plugins&identifier=library&pmod=category', 'error');
    }

    C::t('#library#library_category')->delete($cid);
    library_build_cache_bookategory();
    loadcache('library_bookcategory');
    cpmsg('library:operate_success', 'action=plugins&identifier=library&pmod=category', 'succeed');
}

function library_showcategoryrow($key, $level = 0, $last = '') {
    global $_G;

    loadcache('library_bookcategory');
    $value = $_G['cache']['library_bookcategory'][$key];
    $return = '';

    if($level == 2) {
        $class = $last ? 'lastchildboard' : 'childboard';
        $return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['cid'].']" value="'.$value['displayorder'].'" /></td><td><div class="'.$class.'">'.
            '<input type="text" name="name['.$value['cid'].']" value="'.$value['name'].'" class="txt" />'.
            '</div>'.
            '</td><td><a href="'.ADMINSCRIPT.'?action=plugins&identifier=library&pmod=category&operation=delete&cid='.$value['cid'].'">'.cplang('delete').'</a></td></tr>';
    } elseif($level == 1) {
        $return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['cid'].']" value="'.$value['displayorder'].'" /></td><td><div class="board">'.
            '<input type="text" name="name['.$value['cid'].']" value="'.$value['name'].'" class="txt" />'.
            '<a class="addchildboard" onclick="addrowdirect = 1;addrow(this, 2, '.$value['cid'].')" href="###">'.cplang('blogcategory_addthirdcategory').'</a></div>'.
            '</td><td><a href="'.ADMINSCRIPT.'?action=plugins&identifier=library&pmod=category&operation=delete&cid='.$value['cid'].'">'.cplang('delete').'</a></td></tr>';
        for($i=0,$L=count($value['children']); $i<$L; $i++) {
            $return .= library_showcategoryrow($value['children'][$i], 2, $i==$L-1);
        }
    } else {
        $return = '<tr class="hover"><td class="td25"><input type="text" class="txt" name="order['.$value['cid'].']" value="'.$value['displayorder'].'" /></td><td><div class="parentboard">'.
            '<input type="text" name="name['.$value['cid'].']" value="'.$value['name'].'" class="txt" />'.
            '</div>'.
            '</td><td><a href="'.ADMINSCRIPT.'?action=plugins&identifier=library&pmod=category&operation=delete&cid='.$value['cid'].'">'.cplang('delete').'</a></td></tr>';
        for($i=0,$L=count($value['children']); $i<$L; $i++) {
            $return .= library_showcategoryrow($value['children'][$i], 1, '');
        }
        $return .= '<tr><td class="td25"></td><td colspan="3"><div class="lastboard"><a class="addtr" onclick="addrow(this, 1, '.$value['cid'].')" href="###">'.cplang('blogcategory_addsubcategory').'</a></div>';
    }
    return $return;
}


