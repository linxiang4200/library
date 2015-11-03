<?php

/*
 * 安装测试数据
 * 
 */

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
global $_G;

$Plang = $scriptlang['library'];
if (!$_GET['confirmed']) {
    cpmsg($Plang['install_testdata_confirmed'], 'action=plugins&identifier=library&pmod=install_testdata&confirmed=yes', 'form', array());
} else {
    $install_testdata_file = strtolower($_G['charset']) == 'gbk' ? dirname(__FILE__) . '/data/install_testdata_gbk.sql' : dirname(__FILE__) . '/data/install_testdata.sql';
    if (file_exists($install_testdata_file)) {
	$sql_install_data = file_get_contents($install_testdata_file);
	runquery($sql_install_data);
    }
    cpmsg($Plang['install_testdata_success'], "action=plugins", 'succeed');
}


$finish = TRUE;