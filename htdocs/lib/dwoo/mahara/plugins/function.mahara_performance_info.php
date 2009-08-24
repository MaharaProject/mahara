<?php

/**
 * Dwoo {mahara_performance_info} function plugin
 *
 * Type:     function<br>
 * Name:     mahara_performance_info<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch internationalized strings
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return html to display in the footer.
 */
function Dwoo_Plugin_mahara_performance_info(Dwoo $dwoo) {

    if (!get_config('perftofoot') && !get_config('perftolog')) {
        return;
    }

    $info = get_performance_info();

    $dwoo = smarty_core();

    foreach ($info as $key => $value) {
        if ($key == 'realtime') {
            $value = round($value, 3);
        }
        $dwoo->assign('perf_' . $key, $value);
    }

    // extras
    $dwoo->assign('perf_memory_total_display',  display_size($info['memory_total']));
    $dwoo->assign('perf_memory_growth_display', display_size($info['memory_growth']));

    if (get_config('perftolog')) {
        perf_to_log($info);
    }

    if (get_config('perftofoot')) {
        return $dwoo->fetch('performancefooter.tpl');
    }

}

?>
