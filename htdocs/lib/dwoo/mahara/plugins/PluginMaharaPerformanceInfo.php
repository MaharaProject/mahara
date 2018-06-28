<?php

/**
 * Core {mahara_performance_info} function plugin
 *
 * Type:     function<br>
 * Name:     mahara_performance_info<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch internationalized strings
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return html to display in the footer.
 */
use Dwoo\Core;

function PluginMaharaPerformanceInfo(Core $core) {

    if (!get_config('perftofoot') && !get_config('perftolog')) {
        return;
    }

    $info = get_performance_info();

    $core = smarty_core();

    foreach ($info as $key => $value) {
        if ($key == 'realtime') {
            $value = round($value, 3);
        }
        $core->assign('perf_' . $key, $value);
    }

    // extras
    $core->assign('perf_memory_total_display',  display_size($info['memory_total']));
    $core->assign('perf_memory_growth_display', display_size($info['memory_growth']));

    if (get_config('perftolog')) {
        perf_to_log($info);
    }

    if (get_config('perftofoot')) {
        return $core->fetch('performancefooter.tpl');
    }

}

?>
