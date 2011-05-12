<?php

function export_form_cell_html($element) {
    global $THEME;
    $strclicktopreview = get_string('clicktopreview', 'export');
    $previewimg = $THEME->get_url('images/icon-display.gif');
    $strpreview = get_string('Preview');
    $element['description'] = clean_html($element['description']);
return <<<EOF
<td>
{$element['html']} {$element['labelhtml']}
<div>{$element['description']}</div>
<div><a href="{$element['viewlink']}" class="viewlink nojs-hidden-inline" target="_blank"><img src="{$previewimg}" alt=""> {$strclicktopreview}</a></div>
</td>
EOF;
}

echo $form_tag;
echo '<h3>' . get_string('chooseanexportformat', 'export') . '</h3>';
echo '<div class="element">';
echo '<div>' . $elements['format']['html'] . '</div>';
echo '</div>';
echo '<h3>' . get_string('whatdoyouwanttoexport', 'export') . '</h3>';
echo '<div class="element" id="whattoexport-buttons">';
echo '<div>'. $elements['what']['html'] . '</div>';
echo '</div>';

echo '<div id="whatviews" class="js-hidden"><fieldset><legend>' . get_string('viewstoexport', 'export') . "</legend>\n";
$body = array();
$row = $col = 0;
foreach ($elements as $key => $element) {
    if (substr($key, 0, 5) == 'view_') {
        $body[$row][$col] = export_form_cell_html($element);
        $col++;
        if ($col % 3 == 0) {
            $row++;
            $col = 0;
        }
    }
}

if ($body) {
    echo '<div id="whatviewsselection" class="hidden"><a href="" id="selection_all">'
        . get_string('selectall', 'export') . '</a> | <a href="" id="selection_reverse">'
        . get_string('reverseselection', 'export') . '</a></div>';
    echo "<table>\n";
    foreach ($body as $rownum => $row) {
        if ($rownum == 0) {
            switch (count($row)) {
            case 2:
                echo '<colgroup><col width="50%"><col width="50%"></colgroup>' . "\n";
                break;
            case 3:
                echo '<colgroup><col width="33%"><col width="33%"><col width="33%"></colgroup>' . "\n";
                break;
            }
            echo "    <tbody>\n";
        }
        echo '    <tr class="r' . $rownum % 2 . "\">\n";
        $i = 0;
        foreach ($row as $col) {
            echo $col . "\n";
            $i++;
        }
        for (; $i < 3; $i++) {
            echo "<td></td>\n";
        }
        echo "    </tr>\n";
    }
    echo "    </tbody>\n";
    echo "</table>\n";
}

echo '</fieldset></div>';

$body = array();
$row = $col = 0;
foreach ($elements as $key => $element) {
    if (substr($key, 0, 11) == 'collection_') {
        $body[$row][$col] = "<td>{$element['html']} {$element['labelhtml']}"
            . '<div>' . hsc($element['description']) . '</div></td>';
        $col++;
        if ($col % 3 == 0) {
            $row++;
            $col = 0;
        }
    }
}

if ($body) {
    echo '<div id="whatcollections" class="js-hidden"><fieldset><legend>' . get_string('collectionstoexport', 'export') . "</legend>\n";
    echo "<table>\n";
    foreach ($body as $rownum => $row) {
        if ($rownum == 0) {
            switch (count($row)) {
            case 2:
                echo '<colgroup><col width="50%"><col width="50%"></colgroup>' . "\n";
                break;
            case 3:
                echo '<colgroup><col width="33%"><col width="33%"><col width="33%"></colgroup>' . "\n";
                break;
            }
            echo "    <tbody>\n";
        }
        echo '    <tr class="r' . $rownum % 2 . "\">\n";
        $i = 0;
        foreach ($row as $col) {
            echo $col . "\n";
            $i++;
        }
        for (; $i < 3; $i++) {
            echo "<td></td>\n";
        }
        echo "    </tr>\n";
    }
    echo "    </tbody>\n";
    echo "</table>\n";
    echo '</fieldset></div>';
}

echo '<div id="export_submit_container">';
echo $elements['submit']['html'];
echo '</div>';
echo $hidden_elements;
echo '</form>';
