<?php
$string['dbconnfailed'] = 'Failed to connect to database, error message was %s';
// @todo<nigel>: most likely need much better descriptions here for these environment issues
$string['registerglobals'] = 'You have dangerous PHP settings, register_globals is on. Mahara is trying to work around this, but you should really fix it';
$string['magicquotesgpc'] = 'You have dangerous PHP settings, magic_quotes_gpc is on. Mahara is trying to work around this, but you should really fix it';
$string['datarootnotwritable'] = 'Your defined data root directory, %s, is not writable. This means Mahara is not going to be fully functional.';
$string['magicquotesruntime'] = 'You have dangerous PHP settings, magic_quotes_runtime is on. Mahara is trying to work around this, but you should really fix it';
$string['magicquotessybase'] = 'You have dangerous PHP settings, magic_quotes_sybase is on. Mahara is trying to work around this, but you should really fix it';

$string['configsanityexception'] = '<p>It appears that your server\'s PHP configuration contains a setting that will prevent $projectname from working.'
    . ' More details follow:</p><div id="reason">%s</div><p>Once you have made the appropriate changes, reload this page.</p>';
$string['safemodeon'] = '<p>Your server appears to be running safe mode. $projectname does not support running in safe mode. You must turn this off in either the php.ini file, or in your apache config for the site.</p><p>If you are on shared hosting, it is likely that there is little you can do to get safe_mode turned off, other than ask your hosting provider. Perhaps you could consider moving to a different host.</p>';
?>
