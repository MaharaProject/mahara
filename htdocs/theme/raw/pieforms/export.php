<?php

function export_form_cell_html($element) {
    global $THEME;
    $strclicktopreview = get_string('clicktopreview', 'export');
    $strpreview = get_string('Preview');
    $element['description'] = clean_html($element['description']);
return <<<EOF
<div class="checkbox">
    {$element['html']}
    {$element['labelhtml']}
    <div class="text-small text-midtone with-label">
        {$element['description']}
        <a href="{$element['viewlink']}" class="viewlink text-small nojs-hidden-inline">{$strclicktopreview}</a>
    </div>
</div>
EOF;
}

function display_artefacts($array, $itemsinrow) {
    $grid = 12; // Bootstrap grid
    if ($grid % $itemsinrow == 0) {
        $colwidth = $grid/$itemsinrow;
    }
    foreach($array as $row) {
        echo '<div class="row">';
        $i = 0;
        foreach ($row as $col) {
            echo '<div class="col-lg-'.$colwidth.'">' . $col .'</div>'. "\n";
            $i++;
        }
        echo '</div>';
    }
}

echo $form_tag;
echo '<h2 class="heading">' . get_string('chooseanexportformat', 'export') . '</h2>';
echo '<div class="element form-group" id="exportformat-buttons">';
echo '<div>' . $elements['format']['html'] . '</div>';
echo '</div>';
echo '<h2 class="heading">' . get_string('whatdoyouwanttoexport', 'export') . '</h2>';
echo '<div class="element form-group" id="whattoexport-buttons">';
echo '<div>'. $elements['what']['html'] . '</div>';
echo '</div>';

echo '<div id="whatviews" class="js-hidden exportable-artefacts"><div class="exportable-artefact-container"><h3 class="heading-exportable-pages">' . get_string('viewstoexport', 'export') . "</h3>";
$body = array();
$row = $col = 0;
// Number of items in a row, this should be 1, 2, 3, 4, 6 or 12
$itemsinrow = 3;
foreach ($elements as $key => $element) {
    if (substr($key, 0, 5) == 'view_') {
        $body[$row][$col] = export_form_cell_html($element);
        $col++;
        if ($col % $itemsinrow == 0) {
            $row++;
            $col = 0;
        }
    }
}

if ($body) {
    echo '<div id="whatviewsselection" class="d-none btn-group"><a href="" id="selection_all" class="btn btn-secondary btn-sm">'
        . get_string('selectall') . '</a><a href="" id="selection_reverse" class="btn btn-secondary btn-sm">'
        . get_string('reverseselection', 'export') . '</a></div>';
    echo display_artefacts($body, $itemsinrow);
}
echo '</div></div>';

$body = array();
$row = $col = 0;
// Number of items in a row, this should be 1, 2, 3, 4, 6 or 12
$itemsinrow = 3;
foreach ($elements as $key => $element) {
    if (substr($key, 0, 11) == 'collection_') {
        $body[$row][$col] = '<div class="checkbox">' . $element['html'] . $element['labelhtml'] . '<p class="with-label text-small text-midtone labeldescriptpreview">' . hsc($element['description']) . '</p></div>';
        $col++;
        if ($col % $itemsinrow == 0) {
            $row++;
            $col = 0;
        }
    }
}

if ($body) {
    echo '<div id="whatcollections" class="js-hidden exportable-artefacts"><div class="exportable-artefact-container"><h3 class="heading-exportable-pages">' . get_string('collectionstoexport', 'export') . "</h3>";
    echo '<div id="whatcollectionsselection" class="d-none btn-group"><a href="" id="selection_all_collections" class="btn btn-secondary btn-sm">'
        . get_string('selectall') . '</a><a href="" id="selection_reverse_collections" class="btn btn-secondary btn-sm">'
        . get_string('reverseselection', 'export') . '</a></div>';
    echo display_artefacts($body, $itemsinrow);
    echo '</div></div>';
}

echo '<div id="includefeedback" class="form-group switchbox last">';
echo $elements['includefeedback']['labelhtml'] . $elements['includefeedback']['html'];
echo '<div class="description">' . $elements['includefeedback']['description'] . '</div>';
echo '</div>';
echo '<div id="export_submit_container" class="form-group last">';
echo $elements['submit']['html'];
echo '</div>';
echo $hidden_elements;
echo '</form>';
