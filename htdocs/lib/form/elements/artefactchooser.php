<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    list($html, $pagination, $count) = View::build_artefactchooser_data($element, $form->get_property('viewgroup'), $form->get_property('viewinstitution'));

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

    $baseurl = View::make_base_url();
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

    // TODO: This is quite a lot of javascript to be sending inline, especially the ArtefactChooserData 
    // class.

    if (!empty($element['selectone'])) {
        $artefactchooserdata = '';
    }
    else {
        $artefactchooserdata = 'new ArtefactChooserData();';
    }
    $pagination_js .= <<<EOF
var ul = getFirstElementByTagAndClassName('ul', 'artefactchooser-tabs', '{$form->get_name()}_{$element['name']}_container');
var doneBrowse = false;
var browseA = null;
var searchA = null;
var browseTabCurrent = true;
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
                if (!browseTabCurrent) {
                    {$artefactchooserdata}
                    browseTabCurrent = true;
                }
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
                if (browseTabCurrent) {
                    {$artefactchooserdata}
                    browseTabCurrent = false;
                }
                e.stop();
            });

            connect('artefactchooser-searchfield', 'onkeypress', function(e) {
                if (e.key().code == 13) { // enter pressed - submitting form
                    e.stop();
                    signal('artefactchooser-searchsubmit', 'onclick', true);
                }
            });

            // Wire up the search button
            connect('artefactchooser-searchsubmit', 'onclick', function(e) {
                if (e._event != true) {
                    e.stop();
                }

                var loc = searchA.href.indexOf('?');
                var queryData = [];
                if (loc != -1) {
                    queryData = parseQueryString(searchA.href.substring(loc + 1, searchA.href.length));
                    queryData.extradata = serializeJSON(p.extraData);
                    queryData.search = $('artefactchooser-searchfield').value;
                }

                sendjsonrequest(p.jsonScript, queryData, 'GET', function(data) {
                    var tbody = getFirstElementByTagAndClassName('tbody', null, p.datatable);
                    if (tbody) {
                        if (
                            (document.all && document.documentElement && typeof(document.documentElement.style.maxHeight) != "undefined" && !window.opera)
                            ||
                            (/Konqueror|AppleWebKit|KHTML/.test(navigator.userAgent))) {
                            var temp = DIV({'id':'ie-workaround'});
                            temp.innerHTML = '<table><tbody>' + data.data.tablerows + '</tbody></table>';
                            swapDOM(tbody, temp.childNodes[0].childNodes[0]);
                            removeElement(temp);
                        }
                        else {
                            // This does not work in IE and Konqueror, the tbody 
                            // innerHTML property is readonly.
                            // http://www.ericvasilik.com/2006/07/code-karma.html
                            tbody.innerHTML = data['data']['tablerows'];
                        }
                    }

                    {$artefactchooserdata}

                    // Update the pagination
                    if ($(p.id)) {
                        var tmp = DIV();
                        tmp.innerHTML = data['data']['pagination'];
                        swapDOM(p.id, tmp.firstChild);

                        // Run the pagination js to make it live
                        eval(data['data']['pagination_js']);

                        // Update the result count
                        var results = getFirstElementByTagAndClassName('div', 'results', p.id);
                        if (results) {
                            results.innerHTML = data['data']['results'];
                        }
                    }
                });
            });
        }
    });
}
EOF;
    if (empty($element['selectone'])) {
        $pagination_js .=<<<EOF
/**
 * Manages the problem of changing pages in the artefact chooser losing what 
 * things were selected/not selected
 */
function ArtefactChooserData() {
    var self = this;

    this.init = function() {
        self.insertElementContainers();
        self.connectPagination();
        self.connectCheckboxes();
        self.scrapeForOnpage();
        self.scrapeForSelected();
    }

    /**
     * Puts two containers into the DOM, that will each contain hidden form elements 
     * - one for all the elements on the current page of results, and one for 
     * the currently selected options.
     *
     * Clears out existing containers instead of making new ones, if containers 
     * already exist. This happens when changing tabs on the artefact chooser
     */
    this.insertElementContainers = function() {
        self.seenElementsContainer     = $('seen-elements-container');
        self.selectedElementsContainer = $('selected-elements-container');

        if (self.seenElementsContainer) {
            // Clear out the list of seen elements
            replaceChildNodes(self.seenElementsContainer);
        }
        else {
            self.seenElementsContainer = DIV({'id': 'seen-elements-container', 'style': 'display: none;'});
            insertSiblingNodesAfter('artefactchooser-body', self.seenElementsContainer);
        }

        if (self.selectedElementsContainer) {
            // Clear out the list of selected elements
            replaceChildNodes(self.selectedElementsContainer);
        }
        else {
            self.selectedElementsContainer = DIV({'id': 'selected-elements-container', 'style': 'display: none;'});
            insertSiblingNodesAfter('artefactchooser-body', self.selectedElementsContainer);
        }
    }

    /**
     * Connects pagination so that when a page is changed, we are told about it
     */
    this.connectPagination = function() {
        paginatorProxy.addObserver(self);
        connect(self, 'pagechanged', self.pageChanged);
    }

    /**
     * Connects checkboxes so when they're clicked we can deal with it
     */
    this.connectCheckboxes = function() {
        forEach(getElementsByTagAndClassName('input', 'artefactid-checkbox', 'artefactchooser-body'), function(checkBox) {
            connect(checkBox, 'onclick', partial(self.checkboxClicked, checkBox));
        });
    }

    /**
     * Find all hidden onpage inputs, and move them to the container, otherwise 
     * destroy them if they're already in there (which happens if we go to a 
     * page we've already seen)
     */
    this.scrapeForOnpage = function() {
        forEach(getElementsByTagAndClassName('input', 'artefactid-onpage', 'artefactchooser-body'), function(i) {
            var append = true;
            forEach(self.seenElementsContainer.childNodes, function(seen) {
                if (seen.value == i.value) {
                    append = false;
                    throw MochiKit.Iter.StopIteration;
                }
            });
            if (append) {
                appendChildNodes(self.seenElementsContainer, i);
            }
            else {
                // Element is surplus to requirements
                removeElement(i);
            }
        });
    }

    /**
     * Find all hidden currently selected inputs, and move them to the selected container
     */
    this.scrapeForSelected = function() {
        forEach(getElementsByTagAndClassName('input', 'artefactid-checkbox', 'artefactchooser-body'), function(i) {
            if (i.checked) {
                self.ensureSelectedElement(i);
            }
        });
    }

    /**
     * When a checkbox is clicked, update the list of selected inputs
     */
    this.checkboxClicked = function(checkbox) {
        if (checkbox.checked) {
            // Add to the list if it's not there
            self.ensureSelectedElement(checkbox);
        }
        else {
            // Remove from the list if it's there
            self.removeSelectedElement(checkbox);
        }
    }

    /**
     * When a pagination link is clicked, update the list of seen inputs
     */
    this.pageChanged = function(data) {
        self.scrapeForOnpage();
        if (findValue(self.seenOffsets, data.offset) == -1) {
            self.scrapeForSelected();
            self.seenOffsets.push(data.offset);
        }
        else {
            self.syncroniseCheckboxStateFromContainer();
        }
        self.connectCheckboxes();
    }

    /**
     * Ensures that the element we have been given is in the list of selected 
     * elements
     */
    this.ensureSelectedElement = function(element) {
        var append = true;
        forEach(self.selectedElementsContainer.childNodes, function(selected) {
            if (selected.name == element.name) {
                append = false;
                throw MochiKit.Iter.StopIteration;
            }
        });

        if (append) {
            appendChildNodes(self.selectedElementsContainer,
                INPUT({'type': 'hidden', 'name': element.name, 'value': 1})
            );
        }
    }

    /**
     * Ensures that the element we have been given is NOT in the list of 
     * selected elements
     */
    this.removeSelectedElement = function(element) {
        forEach(self.selectedElementsContainer.childNodes, function(selected) {
            if (selected.name == element.name) {
                removeElement(selected);
                throw MochiKit.Iter.StopIteration;
            }
        });
    }

    /**
     * Called when the user browses back to a page they have already seen. They 
     * may have added/removed what they have checked on that page, so we need 
     * to syncronise the display with their choices
     */
    this.syncroniseCheckboxStateFromContainer = function() {
        forEach(getElementsByTagAndClassName('input', 'artefactid-checkbox', 'artefactchooser-body'), function(checkbox) {
            checkbox.checked = false;
            forEach(self.selectedElementsContainer.childNodes, function(selected) {
                if (selected.name == checkbox.name) {
                    // Checkbox should be checked
                    checkbox.checked = true;
                    throw MochiKit.Iter.StopIteration;
                }
            });
        });
    }

    // Contains hidden elements representing every artefact we have seen, 
    // regardless of whether it has been selected
    this.seenElementsContainer = null;

    // Contains hidden elements representing every artefact that has been 
    // selected on any page we have seen
    this.selectedElementsContainer = null;

    // Pagination offsets we have already seen. We have always seen offset 0 
    // when we begin.
    this.seenOffsets = [0];

    self.init();
}

new ArtefactChooserData();

EOF;
    }
    return $pagination_js;
}
