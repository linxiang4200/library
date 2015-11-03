<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.inc.php 29292 2012-03-31 11:00:07Z yaojungang $
 */
if (!defined('IN_DISCUZ')) {
    exit('Access denied');
}

include DISCUZ_ROOT . '/source/plugin/mobile/qrcode.class.php';

$str = $_GET['str'];
if ($str) {
    $obj = new QRencode();
    $obj->encodePNG($str);
}
