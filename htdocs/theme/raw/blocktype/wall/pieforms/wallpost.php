<?php

// Note: this template isn't echoing the description or error keys for each
// element, but there's no validation or descriptions on this form currently

echo $form_tag;
echo '<div id="wall"><div class="description">' . $elements['postsizelimit']['html'] . '</div>';
echo '<div>' . $elements['text']['labelhtml'] . $elements['text']['html'] .'</div>';
if (isset($elements['text']['error'])) {
    echo '<div class="errmsg">' . $elements['text']['error'] . '</div>';
}
echo '<div class="makeprivate">' . $elements['private']['labelhtml'] . ' ' . $elements['private']['html'] . '</div>';
echo '<div>' . $elements['submit']['html'] . '</div></div>';
echo $hidden_elements;
echo '</form>';
