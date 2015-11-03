<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.inc.php 29292 2012-03-31 11:00:07Z yaojungang $
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
global $_G;

$books = C::t('#library#library_book')->range(0,9999999);
echo '<hr /><textarea style="width:160px;height: 600px;">';
foreach ($books as $book) {
    echo $book['isbn13']."\n";
}
echo '</textarea>';

