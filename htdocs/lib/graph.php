<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

defined('INTERNAL') || die();

/**
 * Only these functions are allowed to be called by fetch_graph_data().
 */
function allowed_graph_functions() {
    return array(
        'institution_view_type_graph_render',
        'institution_user_type_graph',
        'graph_institution_data_weekly',
        'user_institution_graph',
        'view_type_graph_render',
        'group_type_graph_render',
        'graph_site_data_weekly',
    );
}

/**
 * Return the chartjs structured array data for circular graphs
 * Circular graphs include pie, doughnut, polar graphs
 *
 * @param   array  $data  Array of information to graph
 *                        Includes: 'data': associative array of label -> data points
 *                                  'colours': custom colours from the function to use instead of the defaults
 *                                  'labellang': the lang file to find the label string translation
 *                                  'configs': associative array of config overrides
 * @param   array  $colours    Custom colours from js to use instead of the defaults
 * @param   bool   $cron       If function is called from cron we don't want to reply via js
 *
 * @return  array  $graphdata  An array structure that can be encoded to json for chartjs
 */
function get_circular_graph_json($data, $colours = null, $cron = false) {

    if (empty($data['data'])) {
        $data['empty'] = true;
        if ($cron) {
            return $data;
        }
        json_reply(false, array('data' => $data));
    }

    $data['colours'] = get_graph_colours($data, $colours);
    $graphdata = array();
    $x = 0;
    foreach ($data['data'] as $key => $value) {
        $dataobj['value'] = (int)$value;
        $dataobj['color'] = "rgba(" . $data['colours'][$x] . ",1)";
        $dataobj['highlight'] = "rgba(" . $data['colours'][$x] . ",0.6)";
        $dataobj['label'] = !empty($data['labellang']) ? get_string($key, $data['labellang']) : $key;
        $graphdata[] = $dataobj;
        $x = empty($data['colours'][$x+1]) ? 0 : $x + 1;
    }
    $configs = isset($data['configs']) ? $data['configs'] : (object) array();
    return array($graphdata, $configs);
}

/**
 * Return the chartjs structured array data for a bar graph
 *
 * @param   array  $data  Array of information to graph
 *                        Includes: 'data': associative array of point label -> data point
 *                                  'labels': labels for the bars
 *                                  'colours': custom colours from the function to use instead of the defaults
 *                                  'labellang': the lang file to find the label string translation
 *                                  'configs': associative array of config overrides
 * @param   array  $colours    Custom colours from js to use instead of the defaults
 * @param   bool   $cron       If function is called from cron we don't want to reply via js
 *
 * @return  array  $graphdata  An array structure that can be encoded to json for chartjs
 */
function get_bar_graph_json($data, $colours = null, $cron = false) {
    if (empty($data['data'])) {
        $data['empty'] = true;
        if ($cron) {
            return $data;
        }
        json_reply(false, array('data' => $data));
    }
    $data['colours'] = get_graph_colours($data, $colours);
    $graphdata = array();
    $x = 0;
    $graphdata['labels'] = $data['labels'];
    foreach ($data['data'] as $key => $value) {
        $dataobj['fillColor'] = "rgba(" . $data['colours'][$x] . ",0.75)";
        $dataobj['strokeColor'] = "rgba(" . $data['colours'][$x] . ",1)";
        $dataobj['highlightFill'] = "rgba(" . $data['colours'][$x] . ",0.95)";
        $dataobj['highlightStroke'] = "rgba(" . $data['colours'][$x] . ",1)";
        $dataobj['label'] = !empty($data['labellang']) ? get_string($key, $data['labellang']) : $key;
        $dataobj['data'] = is_array($value) ? array_values($value) : array($value);
        $graphdata['datasets'][] = $dataobj;
        $x = empty($data['colours'][$x+1]) ? 0 : $x + 1;
    }
    $configs = isset($data['configs']) ? $data['configs'] : (object) array();
    return array($graphdata, $configs);
}

/**
 * Return the chartjs structured array data for a line graph
 *
 * @param   array  $data  Array of information to graph
 *                        Includes: 'data': associative array of point label -> data point
 *                                  'labels': labels for the lines
 *                                  'colours': custom colours from the function to use instead of the defaults
 *                                  'labellang': the lang file to find the label string translation
 *                                  'configs': associative array of config overrides
 * @param   array  $colours    Custom colours from js to use instead of the defaults
 * @param   bool   $cron       If function is called from cron we don't want to reply via js
 *
 * @return  array  $graphdata  An array structure that can be encoded to json for chartjs
 */
function get_line_graph_json($data, $colours = null, $cron = false) {
    if (empty($data['data'])) {
        $data['empty'] = true;
        if ($cron) {
            return $data;
        }
        json_reply(false, array('data' => $data));
    }
    $data['colours'] = get_graph_colours($data, $colours);
    $graphdata = array();
    $x = 0;
    $graphdata['labels'] = $data['labels'];
    foreach ($data['data'] as $key => $value) {
        $dataobj['fillColor'] = "rgba(" . $data['colours'][$x] . ",0.2)";
        $dataobj['strokeColor'] = "rgba(" . $data['colours'][$x] . ",1)";
        $dataobj['pointColor'] = "rgba(" . $data['colours'][$x] . ",1)";
        $dataobj['pointStrokeColor'] = "rgba(255,255,255,1)";
        $dataobj['pointHighlightFill'] = "rgba(255,255,255,1)";
        $dataobj['pointHighlightStroke'] = "rgba(" . $data['colours'][$x] . ",1)";
        $dataobj['label'] = !empty($data['labellang']) ? get_string($key, $data['labellang']) : $key;
        $dataobj['data'] = is_array($value) ? array_values($value) : array($value);
        $graphdata['datasets'][] = $dataobj;
        $x = empty($data['colours'][$x+1]) ? 0 : $x + 1;
    }
    $configs = isset($data['configs']) ? $data['configs'] : (object) array();
    return array($graphdata, $configs);
}

/**
 * Returns an array of rgb colours to use in the graph
 * We use rgb colours so to allow the chartjs to use alpha transperency
 *
 * @param   array  $data  Array of information to graph
 *                        Includes: 'colours': custom colours passed in via the call to the graph function, eg view_type_graph()
 * @param   array  $colours Array of colours passed in via ajax from fetch_graph_data()
 *
 * @return  array  The merged set of colours
 */
function get_graph_colours($data, $colours = null) {
    // Using colours in rgb format to allow for the use of rgba colours in Chart.js
    // 10 defaults: Red, Green, Blue, Yellow, Sky blue, Magenta, Orange, Light blue, Grey, Purple
    $defaultcolours = array('187,35,39','59,140,46','61,132,203','227,171,0','0,74,136','139,62,138','220,109,10','29,183,197','116,116,116','62,35,110');

    // We try to set colours in this order:
    // passed in by user overides
    // passed in by function overides
    // defaults
    if (is_array($colours)) {
        if (!empty($data['colours']) && is_array($data['colours'])) {
            $data['colours'] = $colours + $data['colours'] + $defaultcolours;
        }
        else {
            $data['colours'] = $colours + $defaultcolours;
        }
    }
    else if (!empty($data['colours']) && is_array($data['colours'])) {
        $data['colours'] = $data['colours'] + $defaultcolours;
    }
    else {
        $data['colours'] = $defaultcolours;
    }
    return $data['colours'];
}