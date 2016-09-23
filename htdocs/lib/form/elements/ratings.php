<?php
/**
 *
 * @package    mahara
 * @subpackage artefact
 * @author     Robert Lyon <rlyon@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// To use in forms:
// 'ELEMENTNAME'  => array(
//      'type'         => 'ratings',
//      'title'        => TEXT,
//      'readonly'     => Set to true if you only want to display the rating and not have it manipulated.
//      'colouron'     => The colour to use for the selected ratings, either #hex string or 'default'.
//      'colouroff'    => The colour to use for the non selected ratings, either #hex string or 'default'.
//      'limit'        => The number of stars, default 5.
//      'icon'         => The type of icon to use for the rating points, default 'star'.
//      'iconempty'    => To display the empty icon rather than the icon greyed out. Note the icon needs to have a '-o' eqiuvalent
//      'officon'      => The type of icon to use for the 'no rating' point, default 'ban-circle'.
//      'onclick'      => JS function for manipulation.
// ),

defined('INTERNAL') || die();

/**
 * jQuery Rating selector element
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_ratings(Pieform $form, $element) {

    $wwwroot = get_config('wwwroot');
    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);
    if (is_null($value) || !is_int($value)) {
        $value = !empty($element['defaultvalue']) ? $element['defaultvalue'] : 0;
    }
    $limit = 5;
    if (get_config_plugin('artefact', 'comment', 'ratinglength')) {
        $limit = get_config_plugin('artefact', 'comment', 'ratinglength');
    }
    $limit = (!empty($element['limit']) && is_int($element['limit'])) ? $element['limit'] : $limit;

    $smarty->assign('id', $form->get_name() . '_' . $element['id']);
    $smarty->assign('name', $element['name']);
    $smarty->assign('value', $value);
    $smarty->assign('readonly', !empty($element['readonly']) ? true : false);
    $defaultcolour = '#DBB80E';
    if (get_config_plugin('artefact', 'comment', 'ratingcolour')) {
        $defaultcolour =  get_config_plugin('artefact', 'comment', 'ratingcolour');
    }
    $smarty->assign('iconempty', !empty($element['iconempty']) ? 1 : 0);
    $colouron = (empty($element['colouron']) || $element['colouron'] == 'default') ? $defaultcolour : $element['colouron'];
    $colouroff = (empty($element['colouroff']) || $element['colouroff'] == 'default') ? '#AAAAAA' : $element['colouroff'];
    $smarty->assign('colouron', $colouron);
    if (!empty($element['iconempty'])) {
        $colouroff = $colouron;
    }
    $smarty->assign('colouroff', $colouroff);
    $smarty->assign('limit', (int) $limit);
    $defaulticon = 'star';
    if (get_config_plugin('artefact', 'comment', 'ratingicon')) {
        $defaulticon =  get_config_plugin('artefact', 'comment', 'ratingicon');
    }
    $smarty->assign('icon', (empty($element['icon']) || $element['icon'] == 'default') ? $defaulticon : $element['icon']);
    $smarty->assign('officon', (empty($element['officon']) || $element['officon'] == 'default') ? 'ban' : $element['officon']);
    $smarty->assign('onclick', (!empty($element['onclick'])) ? $element['onclick'] : false);
    return $smarty->fetch('form/ratings.tpl');
}

/**
 * Returns code to go in <head> for the given ratings instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_ratings_get_headdata($element) {
    $headdata = array();

    $strings = array('artefact.comment' => array('removerating', 'ratingoption'));
    $jsstrings = '';
    foreach ($strings as $section => $sectionstrings) {
        foreach ($sectionstrings as $s) {
            $jsstrings .= "strings.$s=" . json_encode(get_raw_string($s, $section)) . ';';
        }
    }
    $headdata[] = '<script type="application/javascript">' . $jsstrings . '</script>';

    $libfile = get_config('wwwroot')  . 'js/bootstrap-ratings.js';
    $headdata[] = '<script type="application/javascript" src="' . $libfile . '"></script>';
    return $headdata;
}
