<?php

/**
 * 重新安装插件
 * 
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
global $_G;

$pluginLang = $scriptlang['library'];
if (!$_GET['confirmed']) {
	cpmsg($pluginLang['reinstall_confirmed'], 'action=plugins&identifier=library&pmod=reinstall&confirmed=yes', 'form', array());
} else {
	if (file_exists(dirname(__FILE__) . '/uninstall.php')) {
		include dirname(__FILE__) . '/uninstall.php';
	}
	if (file_exists(dirname(__FILE__) . '/install.php')) {
		include dirname(__FILE__) . '/install.php';
	}
	updatecache('setting');
	updatemenu('plugin');
	cpmsg($pluginLang['reinstall_success'], "action=plugins", 'succeed');
}
