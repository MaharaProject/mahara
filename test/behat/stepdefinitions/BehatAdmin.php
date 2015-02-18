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
require_once(dirname(dirname(dirname(__DIR__))) . '/htdocs/testing/frameworks/behat/classes/BehatBase.php');

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
                'viewmicroheaders',
                'showonlineuserssideblock',
                'onlineuserssideblockmaxusers',
                'licensemetadata',
                'licenseallowcustom',
                'allowmobileuploads',
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

    }
}
