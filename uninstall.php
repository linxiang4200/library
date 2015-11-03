<?php

/**
 * 卸载插件
 * 
 * 分析 ./data/install.sql 自动删除数据库
 * 
 * 为防止用户插件数据丢失，默认把 table 改名为 tablename_backup 
 * 若要关闭此特性 $_config['plugin']['data_security'] = 0;
 * 
 * $Id: uninstall.php 89 2012-05-26 11:12:40 yaojungang $
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
global $_G;

if (file_exists(dirname(__FILE__) . '/data/install.sql')) {
    $sql_install = file_get_contents(dirname(__FILE__) . '/data/install.sql');
    $tables = getTableName($sql_install);
    foreach ($tables as $table) {
        if (!empty($_G['config']['plugin']['data_security'])
                && 1 == $_G['config']['plugin']['data_security']) {
            runquery('DROP TABLE IF EXISTS `' . $table . '_backup`');
            isTableExist($table) && runquery('RENAME TABLE `' . $table . '` TO  .`' . $table . '_backup`');
        } else {
            runquery('DROP TABLE IF EXISTS `' . $table . '`');
            runquery('DROP TABLE IF EXISTS `' . $table . '_backup`');
        }
    }
}

/**
 * 判断 table 是否存在
 * @param string $tableName
 * @return bool 
 */
function isTableExist($tableName) {
    $sql = 'select 1 from `' . $tableName . '`';
    DB::query($sql, array(), true);
    return DB::errno() == 0;
}

/**
 * 从SQL建表语句中，获取 tablename
 * @global array $_G
 * @param string $sql
 * @return array 
 */
function getTableName($sql) {
    global $_G;
    $return = array();
    $tablepre = $_G['config']['db'][1]['tablepre'];
    $dbcharset = $_G['config']['db'][1]['dbcharset'];

    $sql = str_replace(array(' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'), $sql);
    $sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' `{tablepre}'), array(' ' . $tablepre, ' `' . $tablepre), $sql));

    $ret = array();
    $num = 0;
    foreach (explode(";\n", trim($sql)) as $query) {
        $queries = explode("\n", trim($query));
        foreach ($queries as $query) {
            $ret[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
        }
        $num++;
    }
    unset($sql);

    foreach ($ret as $query) {
        $query = trim($query);
        if ($query) {
            if (substr($query, 0, 12) == 'CREATE TABLE') {
                //$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
                $name = substr($query, strpos($query, '`') + 1, (strpos($query, '(') - 2) - strpos($query, '`') - 1);
                $return[] = $name;
            }
        }
    }
    return $return;
}

//删除自定义积分策略
$sql = "DELETE FROM `pre_common_credit_rule` WHERE `action` LIKE 'library_%'";
runquery($sql);
$finish = TRUE;
