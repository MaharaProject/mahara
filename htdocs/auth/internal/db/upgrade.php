<?php
function xmldb_auth_internal_upgrade($oldversion=0) {
    if ($oldversion < 2007062900) {

        $prefix = get_config('dbprefix');

        $auth_instance = new stdClass();
        $auth_instance->instancename='internal';
        $auth_instance->priority='1';
        $auth_instance->institution='mahara';
        $auth_instance->authname='internal';
        $auth_instance->id = insert_record('auth_instance',$auth_instance, 'id', true);

        if (!empty($auth_instance->id)) {
            return execute_sql("UPDATE {$prefix}usr set authinstance='{$auth_instance->id}'");
        }

        return false;
    }

    return true;
}
?>