<?php
/**
 *
 * @package    mahara
 * @subpackage flexible layouts
 * @author     Mike Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 Mike Kelly m.f.kelly@arts.ac.uk
 */

defined('INTERNAL') || die();

$wwwroot = get_config('wwwroot');
$templatedata = $this->data['templatedata'];

if (!isset($templatedata['basiclayoutoptions']) || !is_array($templatedata['basiclayoutoptions']) || count($templatedata['basiclayoutoptions']) < 1) {
    throw new PieformException('Radio elements should have at least one option');
}
if (!isset($templatedata['layoutoptions']) || !is_array($templatedata['layoutoptions']) || count($templatedata['layoutoptions']) < 1) {
    throw new PieformException('Radio elements should have at least one option');
}

echo $form_tag;
$output = '<div id="viewlayout_basic_container" class="basiclayoutfieldset form-group collapsible-group">';
$output .= '<fieldset class="pieform-fieldset basiclayoutfieldset collapsible">'
        . '<legend><h3><a href="#viewlayout_layoutselect_container" data-toggle="collapse" aria-expanded="true" aria-controls="#viewlayout_layoutselect_container" class="">'
        . get_string('basicoptions', 'view')
        . '<span class="icon icon-chevron-down collapse-indicator pull-right" role="presentation" aria-hidden="true"></span></a></h3></legend>';
$output .= '<div id="viewlayout_layoutselect_container" class="layoutselect fieldset-body collapse in">';
$output .= '<h4 class="title form-group">'
        . get_string('viewlayoutpagedescription', 'view');
$output .= '<span class="help" id="basiclayouthelp">'
        . '<a href="javascript:void(0);">'
        . '<span class="icon icon-question-circle" role="presentation" aria-hidden="true"></span>'
        . '</a></span>'
        . '</h4>';

// basic layout options
$output .= '<div id="basiclayoutoptions" class="layoutoptions-container">';

foreach ($templatedata['basiclayoutoptions'] as $value => $data) {
    $description = (isset($data['description'])) ? $data['description'] : '';
    $text = Pieform::hsc($data['columns']);
    $output .= '<div class="layoutoption"><div class="thumbnail text-center">'
            . '<label class="accessible-hidden sr-only" for="radiolayout_' . Pieform::hsc($value) . '">' . $text . '</label>'
            . ($description != '' ? '<div class="radio-description">' . $description . '</div>' : '')
            . '<div class="layoutthumb">' . $data['layout'] . '</div>'
            . '<input type="radio" id="radiolayout_' . Pieform::hsc($value) . '" name="layoutselect"'
            . ' value="' . Pieform::hsc($value) . '"'
            . (($templatedata['currentlayout'] == $value) ? ' checked="checked"' : '') . '>'
            . '</div></div>';
}

$output .= '</div>';
$output .= '</div></fieldset></div>';

// advanced layout options
$output .= '<div id="viewlayout_adv_container" class="advancedlayoutfieldset form-group collapsible-group">';

$output .= '<fieldset class="pieform-fieldset advancedlayoutfieldset collapsible collapsed">'
        . '<legend><h3><a href="#viewlayout_advancedlayoutselect_container" data-toggle="collapse" aria-expanded="false" aria-controls="#viewlayout_advancedlayoutselect_container" class="collapsed">'
        . get_string('advancedoptions', 'view')
        . '<span class="icon icon-chevron-down collapse-indicator pull-right" role="presentation" aria-hidden="true"> </span></a></h3></legend>';

$output .= '<div id="viewlayout_advancedlayoutselect_container" class="advancedlayoutselect fieldset-body collapse">';
for ($row = 0; $row < $templatedata['maxrows']; $row++) {


    $output .= '<h4 class="title">' . ($row + 1) . ' ';

    $output .= get_string('row', 'view');

    $output .= '</h4>';
    $output .= '<div class="layoutoptions-container" id="viewlayout_advancedlayoutselect_row' . ($row + 1) . '">';

    foreach ($templatedata['layoutoptions'] as $value => $data) {

        if ($data['rows'] == $row+1) {
            $description = (isset($data['description'])) ? $data['description'] : '';
            $text = Pieform::hsc($data['columns']);
            $output .= '<div class="layoutoption"><div class="thumbnail text-center">'
            . '<label class="accessible-hidden sr-only" for="advancedlayout_' . Pieform::hsc($value) . '">' . $text . '</label>'

            . ($description != '' ? '<div class="radio-description">' . $description . '</div>' : '')
            . '<div class="layoutthumb">' . $data['layout'] . '</div>'
            . '<input type="radio" name="advancedlayoutselect" id="advancedlayout_' .  Pieform::hsc($value) . '"'
            . ' value="' . Pieform::hsc($value) . '"'
            . (($templatedata['currentlayout'] == $value) ? ' checked="checked"' : '') . '>'
            . '</div></div>';
        }
    }
    $output .= '</div>';
}
$output .= '</div>';
$output .= '</fieldset>';
$output .= '<fieldset class="pieform-fieldset cretelayoutfieldset collapsible collapsed last">'
        . '<legend><h3><a href="#viewlayout_createcustomlayout_container" data-toggle="collapse" aria-expanded="false" aria-controls="#viewlayout_createcustomlayout_container" class="collapsed">'
        . get_string('createcustomlayout', 'view')
        . '<span class="icon icon-chevron-down collapse-indicator pull-right" role="presentation" aria-hidden="true"> </span></a></h3></legend>';

$output .= '<div id="viewlayout_createcustomlayout_container" class="createcustomlayout fieldset-body collapse">';


// custom layout design options
if (!isset($templatedata['clnumcolumnsoptions']) || !is_array($templatedata['clnumcolumnsoptions']) || count($templatedata['clnumcolumnsoptions']) < 1 || !isset($templatedata['columnlayoutoptions']) || !is_array($templatedata['columnlayoutoptions']) || count($templatedata['columnlayoutoptions']) < 1) {
    throw new PieformException('Custom layouts need a set of possible layout options.');
}

$output .= '<div id="createcustomlayoutpane" class="row col-static">'

        . '<div class="col-xs-12 col-sm-2">'
        . '<div class="user-icon layoutthumb preview"><div id="custompreview">' . $templatedata['customlayout'] . '</div><p class="metadata text-center">' . get_string('layoutpreview', 'view') .'</p></div>'
        . '</div>'
        . '<div id="customrows" class="col-xs-12 col-sm-10">'
            . '<div id="customrow_1" class="customrow form-group five-across multi-label clearfix">'
            . '<div class="customrowtitle pull-left field"><strong>' . get_string('Row', 'view') . ' 1</strong></div>'
            . '<div class="pull-left field field-selectnumcols">'
            . '<label for="selectnumcolsrow_1">' . get_string('numberofcolumns', 'view') . '</label>'
            . '<select name="selectnumcols" id="selectnumcolsrow_1" class="selectnumcols input-sm" onchange="CustomLayoutManager.customlayout_change_numcolumns(this)">';

                    foreach ($templatedata['clnumcolumnsoptions'] as $value => $data) {
                        $output .= '<option value="' . $value . '" ' . (($templatedata['clnumcolumnsdefault'] == $value)? 'selected="selected"' : '') . '>' . $data . '</option>';
                    }

    $output .= '</select></div>'
            . '<div class="pull-left field">'
            . '<label for="selectcollayoutrow_1">' . get_string('columnlayout', 'view') . '</label>'
            . '<select name="selectcollayout" id="selectcollayoutrow_1" class="selectcollayout input-sm" onchange="CustomLayoutManager.customlayout_change_column_layout()">';

                    foreach ($templatedata['columnlayoutoptions'] as $value => $data) {
                        $numcols = count(explode('-', $data));
                        $selectionstring = 'disabled';
                        if ($value == $templatedata['customlayoutid']) {
                            $selectionstring = 'selected="selected"';
                        }
                        else if ($numcols == $templatedata['clnumcolumnsdefault']) {
                            $selectionstring = '';
                        }
                        $output .= '<option value="' . $value . '" ' . $selectionstring . '>' . $data . '</option>';
                    }

    $output .= '</select>'
            . '</div>'
            . '</div>';


    // 'Add row' button
    $output .='<button type="button" name="addrow" class="btn btn-sm btn-default" id="addrow" onclick="CustomLayoutManager.customlayout_add_row()">'
            .'<span class="icon icon-lg icon-plus-circle left" role="presentation" aria-hidden="true"></span>'
            . get_string('addarow', 'view')
            .'</button>'
            . '</div>';

// close 'createcustomlayoutpane'
$output .= '</div>';

// preview pane
$output .= '<div id="previewcustomlayoutpane" class="panel-footer">'

        . '<button type="button" name="submitlayout" id="addlayout" class="btn btn-primary" onclick="CustomLayoutManager.customlayout_submit_layout()">'
        .'<span class="icon icon-lg icon-check left" role="presentation" aria-hidden="true"></span>'
        . get_string('createnewlayout', 'view')
        . '</button>'
        . '</div>';


// close 'createcustomlayoutpane' container
$output .= '</fieldset></div>';
echo $output;
echo '<div id="exportsubmitcontainer" class="button">';
echo $elements['submit']['html'];
echo '</div>';
echo $hidden_elements;
echo '</form>';
