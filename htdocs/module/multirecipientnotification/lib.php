<?php
/**
 *
 * @package    mahara
 * @subpackage module-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/group.php');
require_once(dirname(__FILE__) . '/lib/multirecipientnotification.php');
require_once(get_config('docroot') . '/module/multirecipientnotification/lib/activityextend.php');


/**
 * module plugin class. Used for registering the plugin and his functions.
 */
class PluginModuleMultirecipientnotification extends PluginModule {
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
        if (get_field('module_installed', 'active', 'name', 'multirecipientnotification')) {
            $active = true;
        }
        return $active;
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
     * API-Function get the provided Menus. It is possible to overwrite existing menuentries
     * by redefining them with the same path, title and weight.
     *
     * @return array fully descripted new menuitems with menupath, title, url, etc.
     */
    public static function messages_menu_items() {
    global $USER;
    global $THEME;
    safe_require('notification', 'internal');
    $unread = $USER->get('unread');

        $menuExtensions = array(
        'inbox' => array(
            'path' => 'inbox',
            'url' => 'module/multirecipientnotification/inbox.php',
            'alt' => get_string('inbox'),
            'title' => get_string('inbox'),
            'count' => $unread,
            'unread' => get_string('unread', 'mahara', $unread),
            'countclass' => 'unreadmessagecount',
            'countclasssr' => 'unreadmessagecount-sr',
            'linkid' => 'mail',
            'weight' => 20,
            'iconclass' => 'envelope'
        ),);
        // Templates
        if (PluginModuleMultirecipientnotification::is_active()) {
            // search for path
            $searchFor = '/user\/sendmessage.php/';
            if ((preg_match($searchFor, $_SERVER['REQUEST_URI'])) == 1) {
                // set new path
                $redirTarget = get_config('wwwroot') . 'module/multirecipientnotification/sendmessage.php';
                if (!empty($_SERVER['QUERY_STRING'])) {
                    // change path
                    $redirTarget .= '?' . $_SERVER['QUERY_STRING'];
                }
                redirect($redirTarget);
                exit;
            }
        }
        return $menuExtensions;
    }

    /**
     * API-Function get the provided submenu tabs.
     *
     * @return array fully described new SUBPAGENAV tab items with title, url, etc.
     */
    public static function submenu_items() {
        $tabs = array(
            'subnav' => array(
                'class' => 'notifications'
            ),
            'inbox' => array(
                'iconclass' => 'icon icon-inbox icon-lg',
                'url' => 'module/multirecipientnotification/inbox.php',
                'title' => get_string('labelinbox', 'module.multirecipientnotification'),
                'tooltip' => get_string('inboxdesc1', 'module.multirecipientnotification'),
            ),
            'outbox' => array(
                'iconclass' => 'icon icon-paper-plane icon-lg',
                'url' => 'module/multirecipientnotification/outbox.php',
                'title' => get_string('labeloutbox1', 'module.multirecipientnotification'),
                'tooltip' => get_string('outboxdesc', 'module.multirecipientnotification'),
            )
        );
        if (defined('NOTIFICATION_SUBPAGE') && isset($tabs[NOTIFICATION_SUBPAGE])) {
            $tabs[NOTIFICATION_SUBPAGE]['selected'] = true;
        }
        return $tabs;
    }

    public static function postinst($prevversion) {
        if ($prevversion < 20131010) {
            // Add triggers to update user unread message count when updating
            // module_multirecipient_userrelation
            db_create_trigger(
                'update_unread_insert2',
                'AFTER', 'INSERT', 'module_multirecipient_userrelation', '
                IF NEW.role = \'recipient\' AND NEW.read = \'0\' THEN
                    UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
                END IF;'
            );
            db_create_trigger(
                'update_unread_update2',
                'AFTER', 'UPDATE', 'module_multirecipient_userrelation', '
                IF OLD.read = \'0\' AND NEW.read = \'1\' AND NEW.role = \'recipient\' THEN
                    UPDATE {usr} SET unread = unread - 1 WHERE id = NEW.usr;
                ELSEIF OLD.read = \'1\' AND NEW.read = \'0\' AND NEW.role = \'recipient\' THEN
                    UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
                END IF;'
            );
            db_create_trigger(
                'update_unread_delete2',
                'AFTER', 'DELETE', 'module_multirecipient_userrelation', '
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

    /**
     * Don't install the module for multirecipientNotification if the
     * ArtefactPlugin is already installed. In that case, the artefact plugin
     * installation should be converted into the module plugin on mahara system
     * upgrade
     *
     * @return type
     */
    public static function sanity_check() {
        try {
            $installed = get_field('artefact_installed', 'name', 'name', self::get_plugin_name());
            if (false != $installed) {
                throw new InstallationException("The artefact plugin multiRecipientArtefact is "
                        . "installed which prevents the installation of this module, "
                        . "that offers the identical functionality");
            }
        }
        catch(Exception $exc) {
            // if the system is installed from scratch (i.e. there is no table
            // artefact_installed) just skip the test.
        }
    }

    /**
     * We want this module to be the default notification module so we
     * will prevent it being disabled.
     */
    public static function can_be_disabled() {
        return false;
    }
}
