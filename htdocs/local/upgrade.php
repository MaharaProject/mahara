<?php

/**
 * Upgrades for local customisations.
 */

defined('INTERNAL') || die();

function xmldb_local_upgrade($oldversion=0) {
    if ($oldversion < 2022052000) {
        if ($data = check_upgrades('module.beacon')) {
            upgrade_plugin($data);
        }
    }
}
