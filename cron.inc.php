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
echo 'start:'.date('Y-m-d H:i:s', TIMESTAMP).'<br />'."\n";
//预约申请提醒
C::t('#library#library_reservation')->cron();
//图书超期提醒
C::t('#library#library_store')->cron();
echo 'end:'.date('Y-m-d H:i:s', TIMESTAMP).'<br />'."\n";