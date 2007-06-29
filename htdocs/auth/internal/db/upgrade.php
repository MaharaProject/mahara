<?php
function xmldb_auth_internal_upgrade($oldversion=0) {
    if ($oldversion < 2007062900) {
        $record = new stdClass();
        $record->instancename='internal';
        $record->priority='1';
        $record->institution='mahara';
        $record->authname='internal';
        return insert_record('auth_instance',$record);
    }

    return true;
}
?>