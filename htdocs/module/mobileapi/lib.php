<?php
/**
 *
 * @package    mahara
 * @subpackage module.mobileapi
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * This plugin is mostly a placeholder for the servicegroup and functions
 * required by the Mahara Mobile app.
 */
class PluginModuleMobileapi extends PluginModule {
    public static function postinst($fromversion) {
        require_once(get_config('docroot') . 'webservice/lib.php');
        external_reload_component('module/mobileapi', false);
    }

    public static function has_config() {
        return true;
    }

    /**
     * Check the status of each configuration element needed for the Mobile API
     * webservice to run.
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

        // Check all the configs needed for the mobileapi to work.
        $statuslist = array();
        $statuslist[] = array(
            'name' => get_string('webserviceproviderenabled', 'module.mobileapi'),
            'status' => (bool) get_config('webservice_provider_enabled')
        );
        $statuslist[] = array(
            'name' => get_string('restprotocolenabled', 'module.mobileapi'),
            'status' => webservice_protocol_is_enabled('rest')
        );
        $servicerec = get_record('external_services', 'shortname', 'maharamobile', 'component', 'module/mobileapi', null, null, 'enabled, restrictedusers, tokenusers');
        $statuslist[] = array(
            'name' => get_string('mobileapiserviceexists', 'module.mobileapi'),
            'status' => (bool) $servicerec
        );
        $statuslist[] = array(
            'name' => get_string('mobileapiserviceconfigured', 'module.mobileapi', get_string('restrictedusers', 'auth.webservice'), get_string('fortokenusers', 'auth.webservice')),
            'status' => ($servicerec && $servicerec->enabled && !$servicerec->restrictedusers && $servicerec->tokenusers),
        );

        return $statuslist;
    }

    /**
     * Determine whether the mobileapi webservice, as a whole, is fully configured
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
            $smarty->assign('notice', get_string('noticeenabled', 'module.mobileapi'));
        }
        else {
            $smarty->assign('notice', get_string('noticenotenabled', 'module.mobileapi'));
        }
        $statushtml = $smarty->fetch('module:mobileapi:statustable.tpl');
        unset($smarty);

        $elements = array();
        $elements['statustable'] = array(
            'type' => 'html',
            'value' => $statushtml
        );

        if (!$ready) {
            $elements['activate'] = array(
                'type' => 'switchbox',
                'title' => get_string('autoconfiguretitle', 'module.mobileapi'),
                'description' => get_string('autoconfiguredesc', 'module.mobileapi'),
                'switchtext' => 'yesno',
            );
        }

        $elements['manualtokens'] = array(
            'type' => 'switchbox',
            'title' => get_string('manualtokenstitle', 'module.mobileapi'),
            'description' => get_string('manualtokensdesc', 'module.mobileapi'),
            'defaultvalue' => (bool) get_config_plugin('module', 'mobileapi', 'manualtokens')
        );
        $form = array('elements' => $elements);

        if (!$ready) {
            // HACK: Reload the page after form submission, so that the status
            // table gets updated.
            $form['jssuccesscallback'] = 'module_mobileapi_reload_page';
        }

        return $form;
    }

    public static function save_config_options(Pieform $form, $values) {

        set_config_plugin('module', 'mobileapi', 'manualtokens', $values['manualtokens']);

        if (!empty($values['activate'])) {
            set_config('webservice_provider_enabled', true);
            set_config('webservice_provider_rest_enabled', true);

            require_once(get_config('docroot') . 'webservice/lib.php');
            external_reload_component('module/mobileapi', false);
            set_field('external_services', 'enabled', 1, 'shortname', 'maharamobile', 'component', 'module/mobileapi');
            set_field('external_services', 'restrictedusers', 0, 'shortname', 'maharamobile', 'component', 'module/mobileapi');
            set_field('external_services', 'tokenusers', 1, 'shortname', 'maharamobile', 'component', 'module/mobileapi');
        }
        return true;
    }

    public static function right_nav_menu_items() {
        if (PluginModuleMobileapi::is_service_ready()) {
            return array(
                'settings/webservice' => array(
                    'path' => 'settings/webservice',
                    'url' => 'module/mobileapi/apps.php',
                    'title' => get_string('mytokensmenutitle', 'module.mobileapi'),
                    'weight' => 50,
                    'iconclass' => 'flag'
                ),
            );
        }
        else {
            return array();
        }
    }
}

