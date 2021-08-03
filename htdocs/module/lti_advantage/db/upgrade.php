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

defined('INTERNAL') || die();

function xmldb_module_lti_advantage_upgrade($oldversion=0) {

  // Convert deployment_id to a varchar for MySQL key/index compatibility.
  if ($oldversion < 2021072910) {
    log_debug('Upgrade "lti_advantage_deployment" field from text to char.');

    // See if we need to add the id column.
    $table = new XMLDBTable('lti_advantage_deployment');
    if (table_exists($table)) {
      $field = new XMLDBField('deployment_id');
      if (field_exists($table, $field)) {
        // Drop the current primary key as we'll want to rebuild it.
        $key = new XMLDBKey('primary');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('registration_id', 'deployment_id'));
        if (!drop_key($table, $key)) {
          log_debug('Upgrade "lti_advantage_deployment": failed to drop key.');
          return false;
        }

        // Alter the field.
        $field->setAttributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        if (!change_field_type($table, $field)) {
          log_debug('Upgrade "lti_advantage_deployment": failed to change field type.');
          return false;
        }

        if (!add_key($table, $key)) {
          log_debug('Upgrade "lti_advantage_deployment": failed to add key.');
          return false;
        }
      }
    }
  }

  if ($oldversion < 2021081014) {
    // We need to track which deployment is for which deployment ID.
    log_debug('Add deployment_key field.');
    $table = new XMLDBTable('lti_advantage_deployment');
    if (table_exists($table)) {
      $field = new XMLDBField('deployment_key');
      if (!field_exists($table, $field)) {
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        if (!add_field($table, $field)) {
          log_debug('Could not add deployment_key to lti_advantage_deployment');
          return false;
        }
      }
    }
  }

  if ($oldversion < 2021081015) {
    log_debug('Add display_name field.');
    $table = new XMLDBTable('lti_advantage_registration');
    if (table_exists($table)) {
      $field = new XMLDBField('display_name');
      if (!field_exists($table, $field)) {
        $field->setAttributes(XMLDB_TYPE_CHAR, 32, null, XMLDB_NOTNULL);
        if (!add_field($table, $field)) {
          log_debug('Could not add display_name to lti_advantage_registration');
          return false;
        }
      }
    }
  }

  if ($oldversion < 2021081114) {
    // @see https://www.imsglobal.org/spec/lti/v1p3/#examplelinkrequest
    log_debug('Add platform_vendor_key field.');
    $table = new XMLDBTable('lti_advantage_registration');
    if (table_exists($table)) {
      $field = new XMLDBField('platform_vendor_key');
      if (!field_exists($table, $field)) {
        $field->setAttributes(XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        if (!add_field($table, $field)) {
          log_debug('Could not add platform_vendor_key to lti_advantage_registration');
          return false;
        }
      }
    }

  }

  return true;
}