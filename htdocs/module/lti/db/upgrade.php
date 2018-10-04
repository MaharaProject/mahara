<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function xmldb_module_lti_upgrade($oldversion=0) {

    $status = true;

    /**
     * Ensure that all the Web Services tables have been created - even if we
     * are transitioning from artefact/webservice to webservice
     */
    if ($oldversion < 2018071009) {
        log_debug('Adding "lti_assessment" table');

        $table = new XMLDBTable('lti_assessment');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('oauthserver', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('resourcelinkid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('contextid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('lisoutcomeserviceurl', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('contexttitle', XMLDB_TYPE_TEXT);
        $table->addFieldInfo('resourcelinktitle', XMLDB_TYPE_TEXT);
        $table->addFieldInfo('group', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('emailnotifications', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('lock', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        $table->addFieldInfo('archive', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('timeconfigured', XMLDB_TYPE_DATETIME);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('oauthserverregistryfk', XMLDB_KEY_FOREIGN, array('oauthserver'), 'oauth_server_registry', array('id'));
        $table->addKeyInfo('groupfk', XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));

        create_table($table);

        log_debug('Adding "lti_assessment_submission" table');

        $table = new XMLDBTable('lti_assessment_submission');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('ltiassessment', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('lisresultsourceid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('timesubmitted', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        $table->addFieldInfo('grade', XMLDB_TYPE_INTEGER, 4);
        $table->addFieldInfo('timegraded', XMLDB_TYPE_DATETIME);
        $table->addFieldInfo('gradedbyusr', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('collectionid', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('viewid', XMLDB_TYPE_INTEGER, 10);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('userfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('collectionidfk', XMLDB_KEY_FOREIGN, array('collectionid'), 'collection', array('id'));
        $table->addKeyInfo('ltiassessmentfk', XMLDB_KEY_FOREIGN, array('ltiassessment'), 'lti_assessment', array('id'));
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('viewid'), 'view', array('id'));

        create_table($table);

    }

    return true;
}