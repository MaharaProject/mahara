<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

if (!defined('INTERNAL')) {
    define('INTERNAL', 1);
}
require_once(dirname(dirname(__FILE__)) . '/init.php');

if (!defined('CRON')) {
    define('JSON', 1);

    json_headers();

    $validtypes = array('Line', 'Bar', 'Radar', 'PolarArea', 'Pie', 'Doughnut');
    $type = ucfirst(param_alphanum('type', false));
    if (!in_array($type, $validtypes)) {
        json_reply('missingparameter', '\'' . $type . '\' is not a valid graph type');
    }
    $graph = param_alphanumext('graph', null);
    $colours = param_variable('colours', null);
    $colours = json_decode($colours);
    $extradata = param_variable('extradata', null);
    $extradata = json_decode($extradata);

    require_once(get_config('libroot') . 'graph.php');
    require_once(get_config('libroot') . 'registration.php');

    if (!function_exists($graph) || !in_array($graph, allowed_graph_functions())) {
        json_reply('invalidparameter', 'Cannot call graph function \'' . $graph . '\'');
    }
    else {
        $data = ($extradata) ? $graph($type, $extradata) : $graph($type);

        if (empty($data)) {
            $data['empty'] = true;
            json_reply(false, array('data' => $data));
        }

        if (!empty($data['jsondata'])) {
            $data['datastr'] = $data['jsondata'];
            json_reply(false, array('data' => $data));
        }

        $graphdata = array();
        $data['colours'] = get_graph_colours($data, $colours);

        // Now covert it to something Chart.js can understand
        switch ($data['graph']) {
         case 'Pie':
         case 'PolarArea':
         case 'Doughnut':
            $graphdata = get_circular_graph_json($data, $colours);
            break;
         case 'Bar':
            $graphdata = get_bar_graph_json($data, $colours);
            break;
         case 'Line':
            $graphdata = get_line_graph_json($data, $colours);
            break;
         default:
        }
        $data['datastr'] = json_encode($graphdata);
        json_reply(false, array('data' => $data));
    }
}
