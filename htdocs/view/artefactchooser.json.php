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

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('form/elements/artefactchooser.php');

$extradata = json_decode(param_variable('extradata'));

safe_require('blocktype', $extradata->blocktype);
$data = pieform_element_artefactchooser_set_attributes(
    call_static_method(generate_class_name('blocktype', $extradata->blocktype), 'artefactchooser_element', $extradata->value)
);
$data['offset'] = param_integer('offset', 0);
$data['lazyload'] = false;
list($html, $pagination, $count, $offset, $artefactdata) = View::build_artefactchooser_data($data, $extradata->group, $extradata->institution);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $count,
        'results' => $count . ' ' . ($count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'artefactdata' => $artefactdata,
    )
));
