<?php
/**
 *
 * @package    mahara
 * @subpackage lib
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// Every function which outputs to a page outside of a template should be in this file
// so that it's easier to review for security purposes

function print_export_head($stylesheets) {
?>
<html>
    <head>
        <title></title>
<?php foreach ($stylesheets as $stylesheet) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo hsc($stylesheet); ?>">
<?php } ?>
        <style type="text/css">
            html, body {
                margin: 0;
                padding: 0;
                background-color: #d4d4d4;
            }
        </style>
    </head>
    <body>
    <div style="width: 100%; background-color: #d4d4d4;" class="progress-bar"></div>
    <p class="progress-text" style="color: #000000;"><?php echo get_string('Starting', 'export'); ?></p>
<?php
}

function print_export_iframe_die($message, $link=null) {
    $message = hsc($message);
    if (isset($link)) {
        $message .= ' <a target="_top" href="' . hsc($link) . '">' . get_string('continue', 'admin') . '</a>';
    }
    echo '<div class="progress-bar" style="width: 100%;"><p>' . $message . '</p></div></body></html>';
}

function print_iframe_progress_handler($percent, $status) {
    // "Erase" the current output with a new background div
    echo '<div style="width: 100%; background-color: #d4d4d4;" class="progress-bar"></div>';
    // The progress bar itself
    echo '<div class="progress-bar-progress" style="width: ' . intval($percent) . '%; background-color: #cff253;"></div>' . "\n";
    // The status text
    echo '<p class="progress-text" style="color: #000000;">' . hsc($status) . "</p>\n";
}

function print_export_footer($strexportgenerated, $continueurl, $continueurljs, $jsmessages=array(), $newlocation) {
?>
        <script type="text/javascript">
            document.write('<div class="progress-bar" style="width: 100%;"><p><?php echo $strexportgenerated . ' <a href="' . $continueurljs . '" target="_top">' . get_string('continue', 'export') . '</a>'; ?></p></div>');
            if (!window.opera) {
                // Opera can't handle this for some reason - it vomits out the
                // download inline in the iframe
                document.location = '<?php echo $newlocation; ?>';
            }
            var messages = <?php echo json_encode($jsmessages); ?>;
            if (messages) {
                for (var i = 0; i < messages.length; i++) {
                    parent.displayMessage(messages[i].msg, messages[i].type, false);
                }
            }
        </script>
        <div class="progress-bar" style="width: 100%;">
            <p><?php echo $strexportgenerated . ' <a href="' . $continueurl . '" target="_top">' . get_string('clickheretodownload', 'export') . '</a>'; ?></p>
        </div>
    </body>
</html>
<?php
}

function print_extractprogress_head($stylesheets, $artefacts) {
?>
<html>
    <head>
        <title></title>
<?php foreach ($stylesheets as $stylesheet) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo hsc($stylesheet); ?>">
<?php } ?>
        <style type="text/css">
            html, body {
                margin: 0;
                padding: 0;
                background-color: #d4d4d4;
            }
        </style>
    </head>
    <body>
    <div style="width: 100%; background-color: #d4d4d4;" class="progress-bar"></div>
    <p class="progress-text" style="color: #000000;"><?php echo get_string('unzipprogress', 'artefact.file', '0/' . $artefacts); ?></p>
<?php
}

function print_extractprogress_footer($message, $next) {
?>
        <div class="progress-bar" style="width: 100%;">
        <p><?php echo $message; ?> <a href="<?php echo $next; ?>" target="_top"><?php echo get_string('Continue', 'artefact.file'); ?></a></p>
        </div>
    </body>
</html>
<?php
}

function execute_javascript_and_close($js='') {
    echo '<html>
    <head>
        <title>You may close this window</title>
        <script language="Javascript">
            function closeMe() {
                '.$js.'
                window.close();
            }
        </script>
    </head>
    <body onLoad="closeMe();" style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; text-align: center;">This window should close automatically</body>'.
    "\n</html>";
    exit;
}

function print_meta_redirect($url, $title = 'Mahara Redirect') {
    print '<html><head><meta http-equiv="Refresh" content="0; url=' . $url . '">';
    print "<title>$title</title>";
    print '</head><body><p>Please follow <a href="'.$url.'">link</a>!</p></body></html>';
}

function print_auth_frame() {
    $frame = '<html><head></head><body onload="parent.show_login_form(\'ajaxlogin_iframe\')"></body></html>';
    echo $frame;
}
