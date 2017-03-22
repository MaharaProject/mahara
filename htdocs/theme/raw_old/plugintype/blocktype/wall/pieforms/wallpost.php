<?php

// Note: this template isn't echoing the description or error keys for each
// element, but there's no validation or descriptions on this form currently

$html = $form_tag;

$html .= $form_tag;
$html .= '<div id="wall-wrap" class="panel-form">';
$html .= '<p class="metadata">' . $elements['postsizelimit']['html'] . '</p>';
$html .= '<div>' . $elements['text']['labelhtml'] . $elements['text']['html'] .'</div>';
if (isset($elements['text']['error'])) {
    $html .= '<div class="errmsg">' . $elements['text']['error'] . '</div>';
}
$html .= '<div class="makeprivate checkbox form-group">' . $elements['private']['labelhtml'] . ' ' . $elements['private']['html'] . '</div>';
$html .= '<div class="form-group">' . $elements['submit']['html'] . '</div>';
$html .= '</div>';
$html .= $hidden_elements;
$html .= '</form>';

echo $html;