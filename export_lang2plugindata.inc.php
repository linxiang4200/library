<?php
/**
 * 把xml中的language导出到 plugindata 里去
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

global $_G;

$pluginName = 'library';
$langFileName = DISCUZ_ROOT . './data/plugindata/' . $pluginName . '.lang.php';

loadcache('pluginlanguage_script');
loadcache('pluginlanguage_template');
loadcache('pluginlanguage_install');
loadcache('pluginlanguage_system');

$content = "<?php\n\n";
$content .= '$scriptlang["' . $pluginName . '"] = ' . var_export($_G['cache']['pluginlanguage_script'][$pluginName], true) . ";\n\n";
$content .= '$templatelang["' . $pluginName . '"] = ' . var_export($_G['cache']['pluginlanguage_template'][$pluginName], true) . ";\n\n";
$content .= '$installlang["' . $pluginName . '"] = ' . var_export($_G['cache']['pluginlanguage_install'][$pluginName], true) . ";\n\n";
$content .= '$systemlang["' . $pluginName . '"] = ' . var_export($_G['cache']['pluginlanguage_system'][$pluginName], true) . ";\n\n";

echo '<hr /><textarea style="width:660px;height: 600px;">';
echo $content;
echo '</textarea>';

$pluginLang = $scriptlang['library'];
loadcache('pluginlanguage_script');

if (!file_exists($langFileName)) {
	file_put_contents($langFileName, $content);
} else {
	if (!$_GET['confirmed']) {
		cpmsg(sprintf($pluginLang['export_lang2plugindata_confirmed'], $langFileName), 'action=plugins&identifier=library&pmod=export_lang2plugindata&confirmed=yes', 'form', array());
	} else {
		file_put_contents($langFileName, $content);
		cpmsg($pluginLang['export_lang2plugindata_success'], "action=plugins", 'succeed');
	}
}

