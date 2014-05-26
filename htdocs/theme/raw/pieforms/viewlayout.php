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
$output = '<div id="viewlayout_basic_container" class="basiclayoutfieldset clearfix">';
$output .= '<fieldset class="pieform-fieldset basiclayoutfieldset collapsible">'
        . '<legend><a href="javascript:void(0);">' . get_string('basicoptions', 'view') . '</a></legend>';
$output .= '<div id="viewlayout_layoutselect_container" class="layoutselect">';
$output .= '<h3 class="title">'
        . get_string('viewlayoutpagedescription', 'view');
$output .= '<span class="help" id="basiclayouthelp">'
        . '<a href="javascript:void(0);">'
        . '<img title="' . get_string('Help', 'view') . '" alt="' . get_string('Help', 'view') . '" src="' . $wwwroot . 'theme/raw/static/images/help.png">'
        . '</a></span>'
        . '</h3>';

// basic layout options
$output .= '<div id="basiclayoutoptions">';

foreach ($templatedata['basiclayoutoptions'] as $value => $data) {
    if (is_array($data)) {
        $text = $data['columns'];
        $description = (isset($data['description'])) ? $data['description'] : '';
    }
    else {
        $text = $data;
        $description = '';
    }
    $text = Pieform::hsc($text);
    $output .= '<div class="layoutoption">'
            . '<label class="accessible-hidden" for="radiolayout_' . Pieform::hsc($value) . '">' . $text . '</label>'
            . '<input type="radio" id="radiolayout_' . Pieform::hsc($value) . '" name="layoutselect"'
            . ' value="' . Pieform::hsc($value) . '"'
            . (($templatedata['currentlayout'] == $value) ? ' checked="checked"' : '') . '>'
            . ($description != '' ? '<div class="radio-description">' . $description . '</div>' : '')
            . '<div class="layoutthumb"><img src="' . $wwwroot . 'thumb.php?type=viewlayout&amp;vl=' . Pieform::hsc($value) . '" title="' . $text . '" alt="' . $text . '"></div>'
            . '</div>';
}

$output .= '</div>';
$output .= '</div></fieldset></div>';

// advanced layout options
$output .= '<div id="viewlayout_adv_container" class="advancedlayoutfieldset">';
$output .= '<fieldset class="pieform-fieldset advancedlayoutfieldset collapsible collapsed">'
        . '<legend><a href="javascript:void(0);">' . get_string('advancedoptions', 'view') . '</a></legend>';
$output .= '<div id="viewlayout_advancedlayoutselect_container" class="advancedlayoutselect">';
for ($row = 0; $row < $templatedata['maxrows']; $row++) {

    $output .= '<h3 class="title">' . ($row + 1) . ' ';

    if ($row + 1 > 1) {
        $output .= get_string('rows', 'view');
    }
    else {
        $output .= get_string('row', 'view');
    }
    $output .= '</h3>';
    $output .= '<div id="viewlayout_advancedlayoutselect_row' . ($row + 1) . '">';

    foreach ($templatedata['layoutoptions'] as $value => $data) {

        if ($data['rows'] == $row+1) {
            if (is_array($data)) {
                $text = $data['columns'];
                $description = (isset($data['description'])) ? $data['description'] : '';
            }
            else {
                $text = $data;
                $description = '';
            }
            $text = Pieform::hsc($text);
            $output .= '<div class="layoutoption">'
            . '<label class="accessible-hidden" for="advancedlayout_' . Pieform::hsc($value) . '">' . $text . '</label>'
            . '<input type="radio" name="advancedlayoutselect" id="advancedlayout_' .  Pieform::hsc($value) . '"'
            . ' value="' . Pieform::hsc($value) . '"'
            . (($templatedata['currentlayout'] == $value) ? ' checked="checked"' : '') . '>'
            . ($description != '' ? '<div class="radio-description">' . $description . '</div>' : '')
            . '<div class="layoutthumb"><img src="' . $wwwroot . 'thumb.php?type=viewlayout&amp;vl=' . Pieform::hsc($value) . '" title="' . $text . '" alt="' . $text . '"></div>'
            . '</div>';
        }
    }
    $output .= '</div>';
    $output .= '<hr class="cb" />';
}
$output .= '</div>';
$output .= '<div id="viewlayout_createcustomlayout_container" class="clearfix html">';
$output .= '<h3 id="createcustomlayouttitle" class="title"><a href="javascript:void(0);">' . get_string('createcustomlayout', 'view') . '</a></h3>';
$output .= '<span class="help" id="customlayouthelp">'
        . '<a href="javascript:void(0);">'
        . '<img title="' . get_string('Help', 'view') . '" alt="' . get_string('Help', 'view') . '" src="' . $wwwroot . 'theme/raw/static/images/help.png">'
        . '</a></span>';

// custom layout design options
if (!isset($templatedata['clnumcolumnsoptions']) || !is_array($templatedata['clnumcolumnsoptions']) || count($templatedata['clnumcolumnsoptions']) < 1 || !isset($templatedata['columnlayoutoptions']) || !is_array($templatedata['columnlayoutoptions']) || count($templatedata['columnlayoutoptions']) < 1) {
    throw new PieformException('Custom layouts need a set of possible layout options.');
}

$output .= '<div id="togglecustomlayoutoptions">';
$output .= '<div id="createcustomlayoutpane" class ="fl cb">'
        . '<div id="customrows">'
        . '<div id="customrow_1" class="customrow clearfix">'
        . '<div class="customrowtitle"><strong>' . get_string('Row', 'view') . ' 1</strong></div>'
        . '<div class="customrowoptions">'
        . '<label>' . get_string('numberofcolumns', 'view') . ' '
        . '<select name="selectnumcols" id="selectnumcolsrow_1" class="selectnumcols" onchange="CustomLayoutManager.customlayout_change_numcolumns(this)">';

foreach ($templatedata['clnumcolumnsoptions'] as $value => $data) {
    $output .= '<option value="' . $value . '" ' . (($templatedata['clnumcolumnsdefault'] == $value)? 'selected="selected"' : '') . '>' . $data . '</option>';
}

$output .= '</select>'
        . '</label>'
        . '<label>' . get_string('columnlayout', 'view') . ' '
        . '<select name="selectcollayout" id="selectcollayoutrow_1" class="selectcollayout" onchange="CustomLayoutManager.customlayout_change_column_layout()">';

foreach ($templatedata['columnlayoutoptions'] as $value => $data) {
    $numcols = count(explode('-', $data));
    $selectionstring = 'disabled';
    if ($value == $templatedata['customlayout']) {
        $selectionstring = 'selected="selected"';
    }
    else if ($numcols == $templatedata['clnumcolumnsdefault']) {
        $selectionstring = '';
    }
    $output .= '<option value="' . $value . '" ' . $selectionstring . '>' . $data . '</option>';
}

$output .= '</select>'
        . '</label>'
        . '</div>'
        . '</div>'
        . '</div>';

// 'Add row' button
$output .= '<div class="cb">'
        . '<input type="button" name="addrow" class="button" id="addrow" value="' . get_string('addarow', 'view') . '" onclick="CustomLayoutManager.customlayout_add_row()"/>'
        . '</div>';

// close 'createcustomlayoutpane'
$output .= '</div>';

// preview pane
$output .= '<div id="previewcustomlayoutpane" class="fr">'
        . '<div id="custompreviewtitle"><strong>' . get_string('layoutpreview', 'view') . '</strong></div>'
        . '<div id="custompreview">'
        . '<img src="' . $wwwroot . 'thumb.php?type=viewlayout&amp;vl=' . $templatedata['customlayout'] .'" title="' . $templatedata['clwidths'] . '" alt="' . $templatedata['clwidths'] . '">'
        . '</div>'
        . '<div class="cb">'
        . '<input type="button" name="submitlayout" id="addlayout" class="button" value="' . get_string('createnewlayout', 'view') . '"  onclick="CustomLayoutManager.customlayout_submit_layout()"/>'
        . '</div>'
        . '</div>';

// close togglecustomlayoutoptions
$output .= '<div class="cb"></div>'
        . '</div>';
// close 'createcustomlayoutpane' container
$output .= '</fieldset></div>';
echo $output;
echo '<div id="exportsubmitcontainer">';
echo $elements['submit']['html'];
echo '</div>';
echo $hidden_elements;
echo '</form>';