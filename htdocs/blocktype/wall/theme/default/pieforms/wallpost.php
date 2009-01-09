<?php

// Note: this template isn't echoing the description or error keys for each 
// element, but there's no validation or descriptions on this form currently

echo $form_tag;
echo '<div>' . $elements['text']['html'] .'</div>';
echo '<div class="description">' . $elements['text']['description'] . '</div>';
echo '<div>' . $elements['private']['labelhtml'] . ' ' . $elements['private']['html'] . '</div>';
echo '<div>' . $elements['submit']['html'] . '</div>';
echo $hidden_elements;
echo '</form>';

?>
