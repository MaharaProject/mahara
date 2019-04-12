<?php

/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Provides
 *  - radio button input field to select from all page layouts`
 *  - custom layout creation field
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_layout(Pieform $form, $element) {

  $output = '<div id="' . $form->get_property('name') . '_advancedlayoutselect_container" class="advancedlayoutselect">';
  for ($row = 0; $row < $element['maxrows']; $row++) {

      $output .= '<h4 class="title">' . ($row + 1) . ' ';
      $output .= get_string('row', 'view');
      $output .= '</h4>';

      $output .= '<div class="layoutoptions-container" id="' . $form->get_property('name') . '_advancedlayoutselect_row' . ($row + 1) . '">';

      foreach ($element['options'] as $value => $data) {

          if ($data['rows'] == $row+1) {
              $description = (isset($data['description'])) ? $data['description'] : '';
              $text = Pieform::hsc($data['columns']);
              $output .= '<div class="layoutoption"><div class="thumbnail text-center">'
              . '<label class="accessible-hidden sr-only" for="advancedlayout_' . Pieform::hsc($value) . '">' . $text . '</label>'

              . ($description != '' ? '<div class="radio-description">' . $description . '</div>' : '')
              . '<div class="layoutthumb">' . $data['layout'] . '</div>'
              . '<input type="radio" name="advancedlayoutselect" id="advancedlayout_' .  Pieform::hsc($value) . '"'
              . ' value="' . Pieform::hsc($value) . '"'
              . (($element['currentlayout'] == $value) ? ' checked="checked"' : '') . '>'
              . '</div></div>';
          }
      }
      $output .= '</div>';
  }
  $output .= '</div>';


  //********     Custom layout
  $output .= '<div id="' . $form->get_property('name') . '_createcustomlayout_container" class="createcustomlayout">';

  // custom layout design options
  if (!isset($element['clnumcolumnsoptions']) || !is_array($element['clnumcolumnsoptions']) || count($element['clnumcolumnsoptions']) < 1 || !isset($element['columnlayoutoptions']) || !is_array($element['columnlayoutoptions']) || count($element['columnlayoutoptions']) < 1) {
      throw new PieformException('Custom layouts need a set of possible layout options.');
  }

  $output .= '<h4 class="title">';
  $output .= get_string('createcustomlayout', 'view');
  $output .= '</h4>';

  $output .= '<div id="createcustomlayoutpane" class="row col-static">'

          . '<div class="col-xs-12 col-sm-2">'
          . '<div class="layoutthumb preview"><div id="custompreview">' . $element['customlayout'] . '</div><p class="metadata text-center">' . get_string('layoutpreview', 'view') .'</p></div>'
          . '</div>'
          . '<div id="customrows" class="col-xs-12 col-sm-10">'
              . '<div id="customrow_1" class="customrow form-group five-across multi-label clearfix" style="border-bottom: 0px !important;">'
              . '<div class="customrowtitle float-left field"><strong>' . get_string('Row', 'view') . ' 1</strong></div>'
              . '<div class="float-left field field-selectnumcols">'
                  . '<label for="selectnumcolsrow_1"><span class="sr-only">' . get_string('Row', 'view') . ' 1: </span>' . get_string('numberofcolumns', 'view') . '</label>'
                  . '<select name="selectnumcols" id="selectnumcolsrow_1" class="selectnumcols input-sm" onchange="CustomLayoutManager.customlayout_change_numcolumns(\'' . $form->get_property('name') . '\', this)">';
                      foreach ($element['clnumcolumnsoptions'] as $value => $data) {
                          $output .= '<option value="' . $value . '" ' . (($element['clnumcolumnsdefault'] == $value)? 'selected="selected"' : '') . '>' . $data . '</option>';
                      }
      $output .= '</select></div>'

              . '<div class="float-left field">'
                  . '<label for="selectcollayoutrow_1"><span class="sr-only">' . get_string('Row', 'view') . ' 1: </span>' . get_string('columnlayout', 'view') . '</label>'
                  . '<select name="selectcollayout" id="selectcollayoutrow_1" class="selectcollayout input-sm" onchange="CustomLayoutManager.customlayout_change_column_layout(\'' . $form->get_property('name') . '\')">';
                      foreach ($element['columnlayoutoptions'] as $value => $data) {
                          $numcols = count(explode('-', $data));
                          $selectionstring = 'disabled';
                          if ($value == $element['customlayoutid']) {
                              $selectionstring = 'selected="selected"';
                          }
                          else if ($numcols == $element['clnumcolumnsdefault']) {
                              $selectionstring = '';
                          }
                          $output .= '<option value="' . $value . '" ' . $selectionstring . '>' . $data . '</option>';
                      }
      $output .= '</select>'
              . '</div>'
              . '</div>'; //closing customrow_1

      // 'Add row' button
      $output .='<button type="button" name="addrow" class="btn btn-sm btn-secondary" id="addrow" onclick="CustomLayoutManager.customlayout_add_row(\'' . $form->get_property('name') . '\')">'
              .'<span class="icon icon-lg icon-plus-circle left" role="presentation" aria-hidden="true"></span>'
              . get_string('addarow', 'view')
              .'</button>'
      . '</div>';// closing customrows

  // close 'createcustomlayoutpane'
  $output .= '</div>';

  // preview pane
  $output .= '<div id="previewcustomlayoutpane">'

          . '<button type="button" name="submitlayout" id="addlayout" class="btn btn-secondary" onclick="CustomLayoutManager.customlayout_submit_layout(\'' . $form->get_property('name') . '\')">'
          . '<span class="icon icon-lg icon-check left" role="presentation" aria-hidden="true"></span>'
          . get_string('createnewlayout', 'view')
          . '</button>'
          . '</div>';

  // close 'createcustomlayout_container' container
  $output .= '</div>';

  $output .= '<script>
      $( window ).on( "load", function() {
          init(\'' . $form->get_property('name') . '\');
      });</script>';

  return $output;
}

function pieform_element_layout_get_headdata($element) {
    $result = '<script src="' . get_config('wwwroot') . 'js/customlayout.js?v=' . get_config('cacheversion', 0) . '"></script>';
    return array($result);
}
