<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$pagination_js = '';
/**
 * Provides a mechanism for choosing one or more artefacts from a list of them.
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_artefactchooser(Pieform $form, $element) {
    global $USER, $pagination_js;

    $value = $form->get_value($element);

    $element['offset'] = param_integer('offset', 0);
    list($html, $pagination, $count) = View::build_artefactchooser_data($element);

    $smarty = smarty_core();
    $smarty->assign('datatable', $element['name'] . '_data');
    $smarty->assign('artefacts', $html);
    $smarty->assign('pagination', $pagination['html']);

    $formname = $form->get_name();
    $smarty->assign('blockinstance', substr($formname, strpos($formname, '_') + 1));

    // Save the pagination javascript for later, when it is asked for. This is 
    // messy, but can't be helped until Pieforms goes to a more OO way of 
    // managing stuff.
    $pagination_js = $pagination['javascript'];

    $baseurl = view::make_base_url();
    $smarty->assign('browseurl', $baseurl);
    $smarty->assign('searchurl', $baseurl . '&s=1');
    $smarty->assign('searchable', !empty($element['search']));

    return $smarty->fetch('form/artefactchooser.tpl');
}

function pieform_element_artefactchooser_get_value(Pieform $form, $element) {
    $name = $element['name'];

    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;

    if (isset($global[$name]) || isset($global["{$name}_onpage"])) {
        $value  = (isset($global[$name])) ? $global[$name] : array();

        if ($element['selectone']) {
            if (!$value) {
                return null;
            }

            if (preg_match('/^\d+$/', $value)) {
                return intval($value);
            }
        }
        else {
            $onpage = (isset($global["{$name}_onpage"])) ? $global["{$name}_onpage"] : array();
            $selected = (is_array($value)) ? array_map('intval', array_keys($value)) : array();
            $default  = (is_array($element['defaultvalue'])) ? $element['defaultvalue'] : array();

            // 1) Start with what's currently available
            // 2) Remove everything on the page that was active when submitted
            // 3) Add in everything that was selected
            $value = array_merge(array_diff($default, $onpage), $selected);
            return array_map('intval', $value);
        }

        throw new PieformException("Invalid value for artefactchooser form element '$name' = '$value'");
    }

    if (isset($element['defaultvalue'])) {
        return $element['defaultvalue'];
    }

    return null;
}

//function pieform_element_artefactchooser_rule_required(Pieform $form, $value, $element) {
//    if (is_array($value) && count($value)) {
//        return null;
//    }
//
//    return $form->i18n('rule', 'required', 'required', $element);
//}

function pieform_element_artefactchooser_set_attributes($element) {
    if (!isset($element['selectone'])) {
        $element['selectone'] = true;
    }
    if (!isset($element['limit'])) {
        $element['limit'] = 10;
    }
    if (!isset($element['template'])) {
        $element['template'] = 'form/artefactchooser-element.tpl';
    }
    if (!isset($element['search'])) {
        $element['search'] = true;
    }

    return $element;
}

/**
 * Extension by Mahara. This api function returns the javascript required to 
 * set up the element, assuming the element has been placed in the page using 
 * javascript. This feature is used in the views interface.
 *
 * In theory, this could go upstream to pieforms itself
 *
 * @param Pieform $form     The form
 * @param array   $element  The element
 */
function pieform_element_artefactchooser_views_js(Pieform $form, $element) {
    global $pagination_js;

    // NOTE: $element['name'] is not set properly at this point
    $element = pieform_element_artefactchooser_set_attributes($element);
    $element['name'] = (!empty($element['selectone'])) ? 'artefactid' : 'artefactids';

    $pagination_js = 'var p = ' . $pagination_js;

    $pagination_js .= <<<EOF
var ul = getFirstElementByTagAndClassName('ul', 'artefactchooser-tabs', '{$form->get_name()}_{$element['name']}_container');
var doneBrowse = false;
var browseA = null;
var searchA = null;
if (ul) {
    forEach(getElementsByTagAndClassName('a', null, ul), function(a) {
        p.rewritePaginatorLink(a);
        if (!doneBrowse) {
            doneBrowse = true;

            browseA = a;
            // Hide the search form
            connect(a, 'onclick', function(e) {
                hideElement('artefactchooser-searchform');
                removeElementClass(searchA.parentNode, 'current');
                addElementClass(browseA.parentNode, 'current');
                browseA.blur();
                $('artefactchooser-searchfield').value = ''; // forget the search for now, easier than making the tabs remember it
                e.stop();
            });
        }
        else {
            searchA = a;

            // Display the search form
            connect(a, 'onclick', function(e) {
                showElement('artefactchooser-searchform');
                removeElementClass(browseA.parentNode, 'current');
                addElementClass(searchA.parentNode, 'current');
                $('artefactchooser-searchfield').focus();
                e.stop();
            });

            // Wire up the search button
            connect('artefactchooser-searchsubmit', 'onclick', function(e) {
                e.stop();

                var loc = searchA.href.indexOf('?');
                var queryData = [];
                if (loc != -1) {
                    queryData = parseQueryString(searchA.href.substring(loc + 1, searchA.href.length));
                    queryData.extradata = serializeJSON(p.extraData);
                    queryData.search = $('artefactchooser-searchfield').value;
                }

                sendjsonrequest(p.jsonScript, queryData, 'GET', function(data) {
                    getFirstElementByTagAndClassName('tbody', null, p.datatable).innerHTML = data['data']['tablerows'];

                    // Update the pagination
                    var tmp = DIV();
                    tmp.innerHTML = data['data']['pagination'];
                    swapDOM(p.id, tmp.firstChild);

                    // Run the pagination js to make it live
                    eval(data['data']['pagination_js']);

                    // Update the result count
                    getFirstElementByTagAndClassName('div', 'results', p.id).innerHTML = data['data']['count'] + ' results'; // TODO i18n and pluralisation
                });
            });
        }
    });
}

EOF;
    return $pagination_js;
}

?>
