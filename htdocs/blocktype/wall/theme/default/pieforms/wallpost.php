<?php

// Note: this template isn't echoing the description or error keys for each 
// element, but there's no validation or descriptions on this form currently

echo $form_tag;
echo '<div>' . $elements['text']['html'] .'</div>';
echo '<div>You can format your post using BBCode. <a href="" onclick="contextualHelp(\'\',\'\',\'core\',\'site\',null,\'bbcode\',this); return false;">Learn more</a></div>';
echo '<div>' . $elements['private']['labelhtml'] . ' ' . $elements['private']['html'] . '</div>';
echo '<div>' . $elements['submit']['html'] . '</div>';
echo $hidden_elements;
echo '</form>';

?>
