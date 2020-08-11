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
    $search_array = array_combine(array_map('strtolower', $validtypes), $validtypes);
    $type = strtolower(param_alphanum('type', false));
    if (empty($search_array[$type])) {
        json_reply('missingparameter', '\'' . $type . '\' is not a valid graph type');
    }
    $graph = param_alphanumext('graph', null);
    $colours = param_variable('colours', null);
    $colours = json_decode($colours);
    $extradata = param_variable('extradata', null);
    $extradata = json_decode($extradata);

    require_once(get_config('libroot') . 'graph.php');
    require_once(get_config('libroot') . 'statistics.php');

    if (!function_exists($graph) || !in_array($graph, allowed_graph_functions())) {
        json_reply('invalidparameter', 'Cannot call graph function \'' . $graph . '\'');
    }
    else {
        $data = ($extradata) ? $graph($search_array[$type], $extradata) : $graph($search_array[$type]);
        if (empty($data)) {
            $data['empty'] = true;
            json_reply(false, array('data' => $data));
        }

        $data['configs'] = isset($extradata->configs) ? $extradata->configs : (object) array();

        if (!empty($data['jsondata'])) {
            $jsondata = json_decode($data['jsondata']);
            if (!empty($colours)) {
                // Update the stored graph jsondata with colours passed in via .tpl file
                // This allows us to display the graph in the theme's colours rather than
                // default colours the graph jsondata was saved in.
                $colours = get_graph_colours($data, $colours);
                $x = 0;
                foreach ($jsondata->datasets as $datasetkey => $dataset) {
                    for ($i=0; $i < count($dataset->data) ; $i++) {
                        foreach ($dataset as $property => $values) {
                            if (preg_match('/^rgba\(/', $values[$i])) {
                                $jsondata->datasets[$datasetkey]->$property[$i] = preg_replace('/\((.*\,)/', '(' . $colours[$x] . ',', $values[$i]);
                            }
                        }
                        $x = empty($colours[$x+1]) ? 0 : $x + 1;
                    }
                }
            }
            // in case any cached data get cached with a broken label string
            if (isset($jsondata->labels) && is_array($jsondata->labels)) {
                foreach ($jsondata->labels as $key => $label) {
                    if (preg_match('/^\[\[(.*?)\/(.*?)\]\]$/', $label, $matches)) {
                        $jsondata->labels[$key] = get_string($matches[1], $matches[2]);
                    }
                }
            }

            $data['datastr'] = json_encode($jsondata);
            $data['configstr'] = json_encode($data['configs']);
            json_reply(false, array('data' => $data));
        }

        $graphdata = array();

        // Now covert it to something Chart.js can understand
        switch ($data['graph']) {
         case 'Pie':
         case 'PolarArea':
         case 'Doughnut':
            list($graphdata, $configs) = get_circular_graph_json($data, $colours);
            break;
         case 'Bar':
            list($graphdata, $configs) = get_bar_graph_json($data, $colours);
            break;
         case 'Line':
            list($graphdata, $configs) = get_line_graph_json($data, $colours);
            break;
         default:
        }
        $data['datastr'] = json_encode($graphdata);
        $data['configstr'] = json_encode($configs);
        json_reply(false, array('data' => $data));
    }
}
