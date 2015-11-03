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
global $_G;

cpheader();
$poperation = $_GET['poperation'] ? $_GET['poperation'] : 'list';

if ($poperation == 'list') {
    $rules = array();
    foreach (C::t('#library#library_credit_rule')->fetch_all_rule() as $value) {
        $rules[$value['rid']] = $value;
    }
    if (!submitcheck('rulesubmit')) {

        showformheader("plugins&identifier=library&pmod=credits");
        showtableheader('setting_credits_policy', 'nobottom', 'id="policytable"' . '');
        echo'<tr class="header"><th class="td28 nowrap">' . $lang['setting_credits_policy_name']
            . '</th><th class="td28 nowrap">' . $lang['setting_credits_policy_cycletype']
            . '</th><th class="td28 nowrap">' . $lang['setting_credits_policy_rewardnum'] . '</th>';
        for ($i = 1; $i <= 8; $i++) {
            if ($_G['setting']['extcredits'][$i]) {
                echo"<th class=\"td25\" id=\"policy$i\" " . ($_G['setting']['extcredits'][$i] ? '' : 'disabled')
                    . " valign=\"top\">" . $_G['setting']['extcredits'][$i]['title'] . "</th>";
            }
        }
        echo '<th class="td25">&nbsp;</th></tr>';

        foreach ($rules as $rid => $rule) {
            $tdarr = array($rule['rulename'],
                           $rule['rid'] ? $lang['setting_credits_policy_cycletype_' . $rule['cycletype']] : 'N/A',
                           $rule['rid'] && $rule['cycletype'] ? $rule['rewardnum'] : 'N/A');
            for ($i = 1; $i <= 8; $i++) {
                if ($_G['setting']['extcredits'][$i]) {
                    array_push(
                        $tdarr, '<input name="credit[' . $rule['rid'] . '][' . $i . ']" class="txt" value="' . $rule[
                    'extcredits' . $i] . '" />'
                    );
                }
            }
            $opstr = '<a href="' . ADMINSCRIPT . '?action=plugins&identifier=library&pmod=credits&poperation=edit&rid='
                . $rule['rid'] . '" title="" class="act">' . $lang['edit'] . '</a>';
            array_push($tdarr, $opstr);
            showtablerow('', array_fill(0, count($_G['setting']['extcredits']) + 4, 'class="td25"'), $tdarr);
        }
        showtablerow('', 'class="lineheight" colspan="9"', $lang['setting_credits_policy_comment']);
        showsubmit('rulesubmit');
        showtablefooter();
        showformfooter();
    } else {
        foreach ($_GET['credit'] as $rid => $credits) {
            $rule = array();
            for ($i = 1; $i <= 8; $i++) {
                if ($_G['setting']['extcredits'][$i]) {
                    $rule['extcredits' . $i] = $credits[$i];
                }
            }
            C::t('common_credit_rule')->update($rid, $rule);
        }
        updatecache(array('setting', 'creditrule'));
        cpmsg('credits_update_succeed', 'action=plugins&identifier=library&pmod=credits', 'succeed');
    }
} elseif ($poperation == 'edit') {

    $rid = intval($_GET['rid']);
    if ($rid) {
        $globalrule = $ruleinfo = C::t('common_credit_rule')->fetch($rid);
    }
    if (!submitcheck('rulesubmit')) {
        if (!$rid) {
            $ruleinfo['rulename'] = $lang['credits_edit_lowerlimit'];
        }
        shownav('global', 'credits_edit');
        showsubmenu("$lang[credits_edit] - $ruleinfo[rulename]");
        showformheader("plugins&identifier=library&pmod=credits&poperation=edit&rid=$rid");
        $extra = '';
        showtips('setting_credits_policy_comment');
        showtableheader('credits_edit', 'nobottom', 'id="edit"' . $extra);
        if ($rid) {
            showsetting(
                'setting_credits_policy_cycletype', array('rule[cycletype]', array(
                    array(0, $lang['setting_credits_policy_cycletype_0'],
                          array('cycletimetd' => 'none', 'rewardnumtd' => 'none')),
                    array(1, $lang['setting_credits_policy_cycletype_1'],
                          array('cycletimetd' => 'none', 'rewardnumtd' => '')),
                    array(2, $lang['setting_credits_policy_cycletype_2'],
                          array('cycletimetd' => '', 'rewardnumtd' => '')),
                    array(3, $lang['setting_credits_policy_cycletype_3'],
                          array('cycletimetd' => '', 'rewardnumtd' => '')),
                    array(4, $lang['setting_credits_policy_cycletype_4'],
                          array('cycletimetd' => 'none', 'rewardnumtd' => '')),
                )), $ruleinfo['cycletype'], 'mradio'
            );
            showtagheader('tbody', 'cycletimetd', in_array($ruleinfo['cycletype'], array(2, 3)), 'sub');
            showsetting('credits_edit_cycletime', 'rule[cycletime]', $ruleinfo['cycletime'], 'text');
            showtagfooter('tbody');
            showtagheader('tbody', 'rewardnumtd', in_array($ruleinfo['cycletype'], array(1, 2, 3, 4)), 'sub');
            showsetting('credits_edit_rewardnum', 'rule[rewardnum]', $ruleinfo['rewardnum'], 'text');
            showtagfooter('tbody');
        }
        for ($i = 1; $i <= 8; $i++) {
            if ($_G['setting']['extcredits'][$i]) {
                if ($rid) {
                    showsetting(
                        "extcredits{$i}(" . $_G['setting']['extcredits'][$i]['title'] . ')', "rule[extcredits{$i}]",
                        $ruleinfo['extcredits' . $i], 'text', '', 0,
                        $fid ? '(' . $lang['credits_edit_globalrule'] . ':' . $globalrule['extcredits' . $i] . ')' : ''
                    );
                } else {
                    showsetting(
                        "extcredits{$i}(" . $_G['setting']['extcredits'][$i]['title'] . ')', "rule[extcredits{$i}]",
                        $_G['setting']['creditspolicy']['lowerlimit'][$i], 'text'
                    );
                }
            }
        }
        showtablefooter();
        showtableheader('', 'nobottom');
        showsubmit('rulesubmit');
        showtablefooter();
        showformfooter();
    } else {
        $rid = $_GET['rid'];
        $rule = $_GET['rule'];
        if ($rid) {
            if (!$rule['cycletype']) {
                $rule['cycletime'] = 0;
                $rule['rewardnum'] = 1;
            }
            $havecredit = $rule['usecustom'] ? true : false;
            for ($i = 1; $i <= 8; $i++) {
                if (!$_G['setting']['extcredits'][$i]) {
                    $rule['extcredits' . $i] = 0;
                }
            }
            foreach ($rule as $key => $val) {
                $rule[$key] = intval($val);
            }
            C::t('common_credit_rule')->update($rid, $rule);
            updatecache('creditrule');
        } else {
            $lowerlimit['creditspolicy']['lowerlimit'] = array();
            for ($i = 1; $i <= 8; $i++) {
                if ($_G['setting']['extcredits'][$i]) {
                    $lowerlimit['creditspolicy']['lowerlimit'][$i] = (float)$rule['extcredits' . $i];
                }
            }
            C::t('common_setting')->update('creditspolicy', $lowerlimit['creditspolicy']);
            updatecache(array('setting', 'creditrule'));
        }
        cpmsg('credits_update_succeed', 'action=plugins&identifier=library&pmod=credits', 'succeed');
    }
}
