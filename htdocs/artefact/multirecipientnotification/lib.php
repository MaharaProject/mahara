<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/group.php');
require_once(dirname(__FILE__) . '/lib/multirecipientnotification.php');
require_once(get_config('docroot') . '/artefact/multirecipientnotification/lib/activityextend.php');


/**
 * artefact plugin class. Used for registering the plugin and his functions.
 */
class PluginArtefactMultirecipientnotification extends PluginArtefact {
    /**
     * the maximum length of a userstring in the list of senders/recipients
     * in the inbox or outbox view
     */
    const MAX_USERNAME_IN_LIST_LENGTH = 30;
    /**
     * Is the plugin activated or not?
     *
     * @return boolean true, if the plugin is activated, otherwise false
     */
    public static function is_active() {
        $active = false;
        if (get_field('artefact_installed', 'active', 'name', 'multirecipientnotification')) {
            $active = true;
        }
        return $active;
    }

    /**
     * API-Function get the provided artefacttypes. Here is it 'Multirecipientnotification'.
     *
     * @return array array with the provided artefacttypes
     */
    public static function get_artefact_types() {
        return array('Multirecipientnotification');
    }

    /**
     * API-Function get the provided blocktypes. Here None.
     *
     * @return array array with the provided blocktypes
     */
    public static function get_block_types() {
        return array();
    }

    /**
     * API-Function get the Plugin ShortName
     *
     * @return string ShortName of the plugin
     */
    public static function get_plugin_name() {
        return 'multirecipientnotification';
    }

    /**
     * API-Function to allow the artefact to be counted in progress bar
     * @return  bool
     */
    public static function has_progressbar_options() {
        return false;
    }

    /**
     * API-Function get the provided Menus. It is possible to overwrite existing menuentries
     * by redefining them with the same path, title and weight.
     *
     * @return array fully descripted new menuitems with menupath, title, url, etc.
     */
    public static function menu_items() {
        $menuExtensions = array();
        // Templates
        if (PluginArtefactMultirecipientnotification::is_active()) {
            // search for path
            $searchFor = 'lib/activity.php';
            if (!(strpos($_SERVER['REQUEST_URI'], $searchFor) === false)) {
                // set new path
                $redirTarget = get_config('wwwroot') . 'artefact/multirecipientnotification/lib/activity.php';
                if (strlen($_SERVER['QUERY_STRING'])>0) {
                    // change path
                    $redirTarget .= '?' . $_SERVER['QUERY_STRING'];
                }
                redirect($redirTarget);
                exit;
            }
            // search for path
             $searchFor = '/account\/activity\/($|index.php)/';
            if ((preg_match($searchFor, $_SERVER['REQUEST_URI'])) == 1) {
                // set new path
                $redirTarget = get_config('wwwroot') . 'artefact/multirecipientnotification/inbox.php';
                if (strlen($_SERVER['QUERY_STRING'])>0) {
                    // change path
                    $redirTarget .='?' . $_SERVER['QUERY_STRING'];
                }
                redirect($redirTarget);
                exit;
            }
            // search for path
            $searchFor = '/user\/sendmessage.php/';
            if ((preg_match($searchFor, $_SERVER['REQUEST_URI'])) == 1) {
                // set new path
                $redirTarget = get_config('wwwroot') . 'artefact/multirecipientnotification/sendmessage.php';
                if (strlen($_SERVER['QUERY_STRING']) > 0) {
                    // change path
                    $redirTarget .= '?' . $_SERVER['QUERY_STRING'];
                }
                redirect($redirTarget);
                exit;
            }
        }
        return $menuExtensions;
    }

    public static function postinst($prevversion) {
        if ($prevversion < 20131010) {
            // Add triggers to update user unread message count when updating
            // artefact_multirecipient_userrelation
            db_create_trigger(
                'update_unread_insert2',
                'AFTER', 'INSERT', 'artefact_multirecipient_userrelation', '
                IF NEW.role = \'recipient\' AND NEW.read = \'0\' THEN
                    UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
                END IF;'
            );
            db_create_trigger(
                'update_unread_update2',
                'AFTER', 'UPDATE', 'artefact_multirecipient_userrelation', '
                IF OLD.read = \'0\' AND NEW.read = \'1\' AND NEW.role = \'recipient\' THEN
                    UPDATE {usr} SET unread = unread - 1 WHERE id = NEW.usr;
                ELSEIF OLD.read = \'1\' AND NEW.read = \'0\' AND NEW.role = \'recipient\' THEN
                    UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
                END IF;'
            );
            db_create_trigger(
                'update_unread_delete2',
                'AFTER', 'DELETE', 'artefact_multirecipient_userrelation', '
                IF OLD.read = \'0\' AND OLD.role = \'recipient\' THEN
                    UPDATE {usr} SET unread = unread - 1 WHERE id = OLD.usr;
                END IF;'
            );
        }
    }

    /**
     * hooks the eventlistener_save_on_commit-method into the event-listener
     * is called upon installation or update
     *
     * @return array
     */
    public static function get_event_subscriptions() {
        return array(
            (object) array(
                'plugin'        => 'multirecipientnotification',
                'event'         => 'deleteuser',
                'callfunction'  => 'eventlistener_on_deleteuser',
            ),
        );
    }

    /**
     * deletes a users messages, when a user is deleted
     *
     * @param type $event
     * @param type $user
     */
    public static function eventlistener_on_deleteuser($event, $user) {
        if ('deleteuser' !== $event) {
            return;
        }
        $userid = $user['id'];

        db_begin();
        $recievedmessageids = get_message_ids_mr($userid, 'recipient', null, null, null);
        if (count($recievedmessageids) > 0) {
            delete_messages_mr($recievedmessageids, $userid);
        }

        $sentmessageids = get_message_ids_mr($userid, 'sender', null, null, 100);
        if (count($sentmessageids) > 0) {
            delete_messages_mr($sentmessageids, $userid);
        }
        db_commit();
    }

} // PluginArtefactNotficationmultirecipientnotification

// Class ArtefactTypeNotficationmultirecipientnotification
require('artefacttypemultirecipientnotification.php');