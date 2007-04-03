<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {mahara_performance_info} function plugin
 *
 * Type:     function<br>
 * Name:     mahara_performance_info<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch internationalized strings
 * @author   Penny Leach <penny@catalyst.net.nz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return html to display in the footer.
 */
function smarty_function_mahara_performance_info($params, &$smarty) {

    if (!get_config('perftofoot') && !get_config('perftolog')) {
        return;
    }

    $info = get_performance_info();

    $smarty = smarty();

    foreach ($info as $key => $value) {
        $smarty->assign('perf_' . $key, $value);
    }

    // extras
    $smarty->assign('perf_memory_total_display',  display_size($info['memory_total']));
    $smarty->assign('perf_memory_growth_display', display_size($info['memory_growth']));

    if (get_config('perftolog')) {
        $logstring = 'PERF: ' .  strip_querystring(get_script_path()). ': ';
        $logstring .= ' memory_total: '.$info['memory_total'].'B (' . display_size($info['memory_total']).') memory_growth: '.$info['memory_growth'].'B ('.display_size($info['memory_growth']).')';
        $logstring .= ' time: '.$info['realtime'].'s';
        $logstring .= ' includecount: '.$info['includecount'];
        $logstring .= ' dbqueries: '.$info['dbqueries'];
        $logstring .= ' ticks: ' . $info['ticks']  . ' user: ' . $info['utime'] . ' sys: ' . $info['stime'] .' cuser: ' . $info['cutime'] . ' csys: ' . $info['cstime'];
        $logstring .= ' serverload: ' . $info['serverload'];
        log_debug($logstring);
    }

    if (get_config('perftofoot')) {
        return $smarty->fetch('performancefooter.tpl');
    }

}

?>
