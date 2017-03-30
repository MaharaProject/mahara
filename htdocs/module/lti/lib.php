<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * This plugin supports the webservices provided by the LTI module
 */
class PluginModuleLti extends PluginModule {

    private static $default_config = array(
        'autocreateusers'   => false,
    );

    public static function postinst($fromversion) {
        require_once(get_config('docroot') . 'webservice/lib.php');
        external_reload_component('module/lti', false);
    }

    public static function has_config() {
        return true;
    }

    public static function has_oauth_service_config() {
        return true;
    }

    /**
     * Check the status of each configuration element needed for the LTI API
     *
     * @param boolean $clearcache Whether to clear the cached results of the check
     * @return array Information about the status of each config step needed.
     */
    public static function check_service_status($clearcache = false) {
        static $statuslist = null;
        if (!$clearcache && $statuslist !== null) {
            return $statuslist;
        }

        require_once(get_config('docroot') . 'webservice/lib.php');

        // Check all the configs needed for the LTI API to work.
        $statuslist = array();
        $statuslist[] = array(
            'name' => get_string('webserviceproviderenabled', 'module.lti'),
            'status' => (bool) get_config('webservice_provider_enabled')
        );
        $statuslist[] = array(
            'name' => get_string('oauthprotocolenabled', 'module.lti'),
            'status' => webservice_protocol_is_enabled('oauth')
        );
        $statuslist[] = array(
            'name' => get_string('restprotocolenabled', 'module.lti'),
            'status' => webservice_protocol_is_enabled('rest')
        );

        $servicerec = get_record('external_services', 'shortname', 'maharalti', 'component', 'module/lti', null, null, 'enabled, restrictedusers, tokenusers');
        $statuslist[] = array(
            'name' => get_string('ltiserviceexists', 'module.lti'),
            'status' => (bool) $servicerec
        );

        return $statuslist;
    }

    /**
     * Determine whether the LTI webservice, as a whole, is fully configured
     * @param boolean $clearcache Whether to clear cached results from a previous check
     * @return boolean
     */
    public static function is_service_ready($clearcache = false) {
        return array_reduce(
            static::check_service_status($clearcache),
            function($carry, $item) {
                return $carry && $item['status'];
            },
            true
        );
    }

    public static function get_config_options() {

        $statuslist = static::check_service_status(true);
        $ready = static::is_service_ready();

        $smarty = smarty_core();
        $smarty->assign('statuslist', $statuslist);
        if ($ready) {
            $smarty->assign('notice', get_string('noticeenabled', 'module.lti'));
        }
        else {
            $smarty->assign('notice', get_string('noticenotenabled', 'module.lti'));
        }
        $statushtml = $smarty->fetch('module:lti:statustable.tpl');
        unset($smarty);

        $elements = array();
        $elements['statustable'] = array(
            'type' => 'html',
            'value' => $statushtml
        );

        if (!$ready) {
            $elements['activate'] = array(
                'type' => 'switchbox',
                'title' => get_string('autoconfiguretitle', 'module.lti'),
                'description' => get_string('autoconfiguredesc', 'module.lti'),
                'switchtext' => 'yesno',
            );
        }

        $form = array('elements' => $elements);

        if (!$ready) {
            // HACK: Reload the page after form submission, so that the status
            // table gets updated.
            $form['jssuccesscallback'] = 'module_lti_reload_page';
        }

        return $form;
    }

    public static function save_config_options(Pieform $form, $values) {

        if (!empty($values['activate'])) {
            set_config('webservice_provider_enabled', true);
            set_config('webservice_provider_oauth_enabled', true);
            set_config('webservice_provider_rest_enabled', true);

            require_once(get_config('docroot') . 'webservice/lib.php');
            external_reload_component('module/lti', false);
            set_field('external_services', 'enabled', 1, 'shortname', 'lti', 'component', 'module/lti');
        }
        return true;
    }

    public static function get_oauth_service_config_options($serverid) {
        $dbconfig = get_records_assoc('oauth_server_config', 'oauthserverregistryid', $serverid, '', 'field, value');

        $elements = array(
            'autocreateusers' => array(
                'type'  => 'switchbox',
                'title' => get_string('autocreateusers', 'module.lti'),
                'defaultvalue' => isset($dbconfig['autocreateusers']->value) ? $dbconfig['autocreateusers']->value : self::$default_config['autocreateusers'],
            ),
        );

        return $elements;

    }

    public static function save_oauth_service_config_options($serverid, $values) {
        return update_oauth_server_config($serverid, 'autocreateusers', (int)$values['autocreateusers']);
    }

    // Disable form fields that are not needed by this plugin
    // @return array of fields not needed with key the field name
    public static function disable_webservice_fields() {
        $fields = array (
            'application_uri' => 1,
            'callback_uri' => 1
        );
        return $fields;
    }
}
