<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from mahara Behat, 2013 David Monllaó
 *
 */
require_once(__DIR__ . '/BehatBase.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Steps definitions for administration section
 *
 */
class BehatAdmin extends BehatBase {

    /**
     * Sets the specified site settings.
     * A table with | Setting label | value | is expected.
     *
     * @Given /^the following site settings are set:$/
     * @param TableNode $table
     * @throws SystemException
     */
    public function site_settings_set(TableNode $table) {

        $settings = array();
        foreach ($table->getHash() as $sitesetting) {
            $settings[$sitesetting['field']] = $sitesetting['value'];
        }

        // Validate the settings
        $allowsettings = array(
            // Site settings
                'sitename',
                'lang',
                'country',
                'theme',
                'dropdownmenu',
                'homepageinfo',
            // Users settings
                'userscanchooseviewthemes',
                'remoteavatars',
                'userscanhiderealnames',
                'searchusernames',
                'searchuserspublic',
                'anonymouscomments',
                'loggedinprofileviewaccess',
                'staffreports',
                'staffstats',
                'userscandisabledevicedetection',
                'masqueradingreasonrequired',
                'masqueradingnotified',
                'showprogressbar',
                'exporttoqueue',
                'defaultmultipleblogs',
            // Search settings
                'searchplugin',
            // Group settings
                'creategroups',
                'createpublicgroups',
                'allowgroupcategories',
            // Institution settings
                'institutionexpirynotification',
                'institutionautosuspend',
                'requireregistrationconfirm',
                'isolatedinstitutions',
            // Account settings
            // Security settings
            // Proxy settings
            // Email settings
            // Notification settings
            // General settings
                'allowpublicviews',
                'allowpublicprofiles',
                'allowanonymouspages',
                'generatesitemap',
                'showselfsearchsideblock',
                'showtagssideblock',
                'tagssideblockmaxtags',
                'showonlineuserssideblock',
                'onlineuserssideblockmaxusers',
                'licensemetadata',
                'licenseallowcustom',
                'wysiwyg',
                'sitefilesaccess',
                'watchlistnotification_delay',
            // Logging settings
            // Experiment settings
                'skins',
        );
        // if public views are disabled, sitemap generation must also be disabled.
        if (empty($settings['allowpublicviews'])) {
            $settings['generatesitemap'] = false;
        }
        else {
            // Ensure allowpublicprofiles is set as well
            $settings['allowpublicprofiles'] = 1;
        }
        foreach ($settings as $key => $setting) {
            if (!array_search($key, $allowsettings)) {
                throw new SystemException("The option \"$key\" is not a valid setting");
            }
        }

        // Update site settings
        $oldsearchplugin = get_config('searchplugin');
        $oldlanguage = get_config('lang');
        $oldtheme = get_config('theme');
        foreach ($allowsettings as $setting) {
            if (isset($settings[$setting]) && !set_config($setting, $settings[$setting])) {
                throw new SystemException("Can not set the option \"$setting\" to \"$settings[$setting]\"");
            }
        }
        if (isset($settings['lang']) && $oldlanguage != $settings['lang']) {
            safe_require('artefact', 'file');
            ArtefactTypeFolder::change_public_folder_name($oldlanguage, $settings['lang']);
        }

        if (isset($settings['searchplugin']) && $oldsearchplugin != $settings['searchplugin']) {
            // Call the old search plugin's sitewide cleanup method
            safe_require('search', $oldsearchplugin);
            call_static_method(generate_class_name('search', $oldsearchplugin), 'cleanup_sitewide');
            // Call the new search plugin's sitewide initialize method
            safe_require('search', $settings['searchplugin']);
            $initialize = call_static_method(generate_class_name('search', $settings['searchplugin']), 'initialize_sitewide');
            if (!$initialize) {
                throw new SystemException(get_string('searchconfigerror1', 'admin', $settings['searchplugin']));
            }
            // Call the new search plugin's can connect
            safe_require('search', $settings['searchplugin']);
            $connect = call_static_method(generate_class_name('search', $settings['searchplugin']), 'can_connect');
            if (!$connect) {
                throw new SystemException(get_string('searchconfigerror1', 'admin', $settings['searchplugin']));
            }
        }
    }

    /**
     * Sets the specified plugin settings.
     * A table with | Plugintype | Plugin | value | is expected.
     *
     * @Given /^the following plugins are set:$/
     * @param TableNode $table
     * @throws SystemException
     */
    public function plugin_activation_set(TableNode $table) {

        $settings = array();
        foreach ($table->getHash() as $pluginsetting) {
            $settings[$pluginsetting['plugintype']][$pluginsetting['plugin']] = $pluginsetting['value'];
        }

        // Validate the settings
        $allowsettings = array(
            'blocktype' => array (
                'annotation',
                'blog',
                'comment',
            ),
            'artefact' => array (
                'blog',
                'plans',
                'resume',
            ),
            'grouptype' => array(
                'course',
            ),
            'module' => array(
                'smartevidence',
                'lti',
                'mobileapi',
            ),
        );
        // Update plugin settings
        foreach ($settings as $plugintype => $plugins) {
            if (!isset($allowsettings[$plugintype])) {
                throw new SystemException("Not a valid plugintype \"$plugintype\"");
            }
            else {
                foreach ($plugins as $plugin => $value) {
                    if (!in_array($plugin, $allowsettings[$plugintype])) {
                        throw new SystemException("\"$plugin\" is not a valid plugin for plugintype \"$plugintype\"");
                    }
                    else {
                        if ($plugintype == 'blocktype') {
                            // Don't enable blocktypes unless the artefact plugin that provides them is also enabled
                            $artefact = get_field('blocktype_installed', 'artefactplugin', 'name', $plugin);
                            if (!empty($value) && !empty($artefact)) {
                                set_field('artefact_installed', 'active', 1, 'name', $artefact);
                            }
                        }
                        else if ($plugintype == 'artefact' && empty($value)) {
                            // Disable all the artefact's blocktypes too
                            set_field('blocktype_installed', 'active', 0, 'artefactplugin', $plugin);
                        }
                        if (!set_field($plugintype . '_installed', 'active', $value, 'name', $plugin)) {
                            throw new SystemException("Can not activate / deactivate the \"$plugintype\" \"$plugin\"");
                        }
                    }
                }
            }
        }
    }

    /**
     * Sets the specified plugin settings.
     * A table with | Plugintype | Plugin | Setting label | value | is expected.
     *
     * @Given /^the following plugin settings are set:$/
     * @param TableNode $table
     * @throws SystemException
     */
    public function plugin_settings_set(TableNode $table) {

        $settings = array();
        foreach ($table->getHash() as $pluginsetting) {
            $settings[$pluginsetting['plugintype']][$pluginsetting['plugin']][$pluginsetting['field']] = $pluginsetting['value'];
        }

        // Validate the settings
        $allowsettings = array(
            // Artefact internal settings
            'artefact' => array (
                'internal' => array(
                    'profilemandatory' => array(
                         'firstname',
                         'lastname',
                         'studentid',
                         'preferredname',
                         'introduction',
                         'email',
                         'socialprofile',
                         // more to come ...
                    ),
                    'profilepublic' => array(
                         'firstname',
                         'lastname',
                         'studentid',
                         'preferredname',
                         'email',
                    ),
                ),
            ),
        );
        // if artefact internal profilemandatory is set we need to make sure that firstname/lastname/email are included.
        if (!empty($settings['artefact']['internal']['profilemandatory'])) {
            $values = explode(',', $settings['artefact']['internal']['profilemandatory']);
            $mandatory = array('firstname', 'lastname', 'email');
            $values = array_merge($mandatory, $values);
            $settings['artefact']['internal']['profilemandatory'] = implode(',', $values);
        }
        // if artefact internal profilepublic is set we need to make sure that firstname/lastname/preferredname are included.
        if (!empty($settings['artefact']['internal']['profilepublic'])) {
            $values = explode(',', $settings['artefact']['internal']['profilepublic']);
            $mandatory = array('firstname', 'lastname', 'email');
            $values = array_merge($mandatory, $values);
            $settings['artefact']['internal']['profilepublic'] = implode(',', $values);
        }

        // Update plugin settings
        foreach ($allowsettings as $plugintype => $plugins) {
            foreach ($plugins as $plugin => $fields) {
                foreach ($fields as $field => $values) {
                    if (isset($settings[$plugintype][$plugin][$field]) && !set_config_plugin($plugintype, $plugin, $field, $settings[$plugintype][$plugin][$field])) {
                        throw new SystemException("Can not set the \"$plugintype\" \"$plugin\" option \"$field\" to \"$settings[$plugintype][$plugin][$field]\"");
                    }
                }
            }
        }
    }
}
