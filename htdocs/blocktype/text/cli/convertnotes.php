<?php
define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require(get_config('libroot') . 'cli.php');
safe_require('blocktype', 'text', 'lib.php');
safe_require('artefact', 'internal', 'lib.php');

// Set $numtoprocess to a number to process notes in a smaller batch size.
$numtoprocess = null;
try {
    log_info("Preparing to process notes");
    PluginBlocktypeText::convert_notes_to_text_blocks($numtoprocess);
}
catch (Exception $e) {
    cli::cli_exit($e->getMessage(), true);
}

log_info("Done!");