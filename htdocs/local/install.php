<?php

/**
 * Pre- and post-install hooks for local database customisations.
 */

function localpreinst() {
}

function localpostinst() {
    global $CFG;

    // Run the local upgrade script after installation
    require_once($CFG->docroot . '/local/upgrade.php');
    xmldb_local_upgrade(0);
}
