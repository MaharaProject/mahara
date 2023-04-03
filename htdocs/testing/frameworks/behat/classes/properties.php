<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Contains arrays of 'properties', or labelled css/xpath elements.
 *
 * This enables the test writer to use the more readable label (array key) in
 * feature files and not to have to find, or write, the css directly.
 *
 * Related properties are grouped into categories,
 * each of which is in an array constant.
 * The test writer can pass in the 'location'(array constant) as an
 * optional second parameter to specify the array to be searched.
 *
 * There is a parent array ('LOCATORS'), which is searched in order if the
 * location parameter is not included. The array constants should be given
 * in order of most to least used in the parent array, to cut down on search times.
 *
 */

define('LOCATORS', array(
    'ADMINISTRATION',
    'NAV',
    'BLOCKS',
    'HEADER',
    'DASHBOARD',
    'COMMON',
    'ACCOUNT',
    'VIEWS',
    'FILES',
    'FOOTER',
    'MODAL',
    'MISC',
    'GROUPS',
    'COMMENT',
    'TAGS',
    'PROFILE',
    'PEOPLE',
    'INSTITUTIONS',
    'SITEOPTIONS',
    'TINYMCE',
    'SMARTEVIDENCE',
    'REPORT',
    'PEERASSESSMENT',
    'LEGAL',
    'WEBSERVICES',
    'PAGINATION',
    'OUTCOMES'
));

/**
 * Arrays group related content together.
 * Order of arrays doesn't affect functionality.
 * For readability, they are in alphabetical order.
 *
 * Properties have descriptive keys, using labels or text from the page,
 * if possible.
 * The intention is that someone looking at the visible page (not the html)
 * would know which element is intended by that name.
 *
 */

// elements accessed via the account menu
define('ACCOUNT', array(
    'Preferences heading 1' => '#accountprefs h2:nth-of-type(1)',
    'Preferences heading 2' => '#accountprefs h2:nth-of-type(2)',
    'Friends control radio' => '#accountprefs_friendscontrol_container > div.radio-wrapper > div:first-child > input.radio',
    'Message from other people' => '#activityprefs_activity_usermessage',
    'System message' => '#activityprefs_activity_maharamessage',
));

define('ADMINISTRATION', array(
    'Menus' => '#rownew',
));

define('BLOCKS', array(
    'Content types' => '#placeholderlist',
    //in edit mode, the class has '-editor', so we use 'class^='
    //to capture both options
    'My portfolios' => '[class^=bt-myviews]',
    'Portfolios shared with me' => '[class^=bt-newviews]',
    'Inbox' => '[class^=bt-inbox]',
    'Resume field block' => '.bt-resumefield',
    'Pages I am watching' => '[class^=bt-watchlist]',
    'Online users block' => '#sb-onlineusers',
    'Block header' => '.block-header',
    'Tags block' => '#sb-tags',
    'Group info' => '.bt-groupinfo',
    'Add new block' => '#newblock',
    'Save block' => '[id^=instconf_action_configureblockinstance_id]',
));

define('COMMENT', array(
    'Comment preview' => '.commentreplyview',
    'Comment text' => '.comment-text',
    'Comment feedbacktable' => '.feedbacktable',
    'Comment button' => '#add_feedback_form',
    'Make comment public status' => '#add_feedback_form_ispublic_container',
));

define('COMMON', array(
    'Page heading' => '.section-heading',
    'H1 heading' => 'h1',
));

define('DASHBOARD', array(
    'Static pages' => "//a[contains(@href, 'site/pages')]/b",
    'Create'=> '',
    'Share'=>'',
    'Engage'=>'',
    'Edit dashboard'=>'',
));

define('FILES', array(
    'Select' => ".btn[title='Select']",//tick to select file button
    'File download heading 1' => 'li.filedownload-item:nth-of-type(1)>.list-group-item-heading',
    'File download heading 2' => 'li.filedownload-item:nth-of-type(2)>.list-group-item-heading',
    'Filelist table' => '.filelist',
));

define('FOOTER', array(
    'Footer' => '.footer',
    'Footer menu' => '.footer-nav',
    'Footer help' => '#footerhelp',
));

define('GROUPS', array(
    'Submissions to this group' => '#allsubmissionlist',
    'My groups box' => '#groups .list-group-item-text',
    'Group portfolios' => '#groupviewlist',
    'Members without a submission to the group' => '#nosubmissionslist',
    'Collections shared with this group' => '#sharedcollectionlist',
    'Pages shared with this group' => '#sharedviewlist',
    'Sorted by dropdown' => '#search_sortoption',
    'Groups results' => '#findgroups',
    'Search results heading row 1' => '#membersearchresults .list-group-item:nth-of-type(1) .list-group-item-heading',
    'Search results heading row 2' => '#membersearchresults .list-group-item:nth-of-type(2) .list-group-item-heading',
    'Search results heading row 3' => '#membersearchresults .list-group-item:nth-of-type(3) .list-group-item-heading',
    'Search results heading row 4' => '#membersearchresults .list-group-item:nth-of-type(4) .list-group-item-heading',
    'Search results heading row 6' => '#membersearchresults .list-group-item:nth-of-type(6) .list-group-item-heading',
    'Navigation' => '.nav-inpage',
));

define('HEADER', array(
    'Logo'    =>'div#logo-area',
    'Search'  =>'#usf_query',
    'Language' =>'.lang-toggle',
    'Inbox' => '#nav-inbox',
    'Profile' =>'.user-icon',
));

define ('INSTITUTIONS', array(
    'Expires column' => 'tbody tr td:nth-of-type(3)',
    'Submenu' => '.manageinstitutions ul',
    'Add' => '#main-column-container div.btn-group-top',
    'Authentication' => '#institution_authplugin_container',
));

define('LEGAL', array(
    //account/userprivacy.php
    'First Legal' => "//a[contains(@href, 'fs=privacy')]/strong",
    'Second Legal' => "//a[contains(@href, 'fs=termsandconditions')]/strong",
    'Terms and conditions Edit icon' => '#termsandconditions .btn-secondary',
    'Privacy statement Edit icon' => '#privacy .btn-group',
));

define('MODAL', array(
    'Upload dialog' => '.modal-filebrowser',
    'Options dialog' => '.modal-header',
    'Modal header' => '.modal-header',
    'Modal content' => '.modal-body',
    'Feedback modal content' => '.feedbacktable.modal',
    'Message' => 'div#modal_messages',
    'File download modal' => '#instconf_artefactfieldset_container',
    'Submission' => 'div[id^=instconf_action].submitcancel',
    'Sign-off page' => '#signoff-confirm-form .modal-body .btn-group',
    'Verify page' => '#verify-confirm-form .modal-body .btn-group',
));

define('MISC', array(
    'Inbox message icon' => '#activitylist .card-header a h2',
    'Inbox' => '#activitylist',
    'Videojs time remaining' => '.vjs-remaining-time-display',
    'Progressbar block' => '#sb-progressbar .card-header',
    'Progressbar' => '#progress_bar_fill.progress-bar',
    'Timeline Bar' => '.timeline-bar',
    //are these used?
    'Settings' => "//ul[#'userchildmenu-8']/?/?/a[@innertext='Settings']",
    'Legal' => "//ul[#'userchildmenu-8']/?/?/a[@innertext='Legal']",
    'Secret urls - table row 1' => '//table/tbody/tr[1]/td[4]/a',
    'File Size' => "//table[@id='files_filebrowser_filelist']/tbody/tr[1]/td[4]",
    'Multirecipientnotification' => "//li[@id='module.multirecipientnotification']",
    'Current skin' => '.col-md-3',
    'Dropdown' => '.dropdown-menu',
    'Share tabs' => '.nav.nav-tabs',
    'CSV submit' => '#uploadcsv_submit_container',
));

define('NAV', array(
    'Main menu' => '#main-nav',
    'Administration menu' => '#main-nav-admin',
    'Admin home sub-menu' => '.adminhome',
    'Arrow-bar nav' => '.arrow-bar',
    'Settings sub-menu' => "//span[@innertext='Settings']",
    'Top right button group' => '.btn-top-right.btn-group',
    'Manage sub-menu' => '.manage',
    'Admin Groups sub-menu' => '.managegroups ul',
    'Institutions sub-menu' => '.manageinstitutions ul',
    'Share sub-menu' => '.share',
    'Engage sub-menu' => '.engage',
    'Extensions sub-menu' => '.configextensions ul',
    'Configure site sub-menu' => '.configsite',
    'Users sub-menu' => '.configusers',
    'Create sub-menu' => '.create',
    'Web services sub-menu' => '.webservices',
    'Toolbar buttons' => '#toolbar-buttons',
    'Account menu' => '.icon-chevron-down.collapsed',
));

define('PAGINATION', array(
    'Group' => 'div#groupviews_pagination',
    'Shared' => 'div#sharedviews_pagination',
));

define('PEERASSESSMENT', array(
    'Signoff page' => '#signoff-confirm-form',
    'Verify page' => '#verify-confirm-form',
));

define('PEOPLE', array(
    'Filter by first name' => '#firstnamelist',
    'Find people results' => '#friendslist_pagination',
    'Pending since' => '.pendingfriend',
));

define('PROFILE', array(
    'Country mandatory field' => "//div[@id='pluginconfig_mandatory_container']/div[@class='checkboxes-option checkbox']/label[contains(text(),'Country')]",
    'Import First name' => '#profilefield-profile #profile-profile:nth-of-type(1)',
    'Import Last name' => '#profilefield-profile #profile-profile:nth-of-type(2)',
    'Import Student ID' => '#profilefield-profile #profile-profile:nth-of-type(3)',
    'Import Email address' => '#profilefield-contact #profile-contact:nth-of-type(1)',
));

//VIEWS is Portfolios, just shorter to write
define('VIEWS', array(
    'Create' => '#main-column-container div.btn-group-top',
    'Portfolios boxes' => '.grouppageswrap',
    'Collections text-box' => '.select2-selection__rendered',
    'Return to portfolios button' => '#view-return-controls .btn-secondary:nth-of-type(2)',
    //button group followed by individual buttons
    'Vertical button group' => '.btn-group-vertical',
    'Settings button' => '.editlayout',
    'Display page button' => '.displaycontent',
    'Share button' => '.editshare',
    'Return button' => '.returntolocation',
    'Page action buttons' => '.pageactions',
    'Page content' => '.user-page-content',
    'Last updated' => '.last-updated',
    'Main content' => 'div#column-container',
    'Signed off' => 'a#signoff',
));

define('REPORT', array(
    'Group views report tr1 tc1' => "//*[@id='groupviewsreport']/tbody/tr[1]/td[1]",
    'Group views report tr1 tc2' => "//*[@id='groupviewsreport']/tbody/tr[1]/td[2]",
    'Group views report tr1 tc3' => "//*[@id='groupviewsreport']/tbody/tr[1]/td[3]",
    'Group views report tr3 tc1' => "//*[@id='groupviewsreport']/tbody/tr[3]/td[1]",
    'Shared with this group report' => '#sharedviewsreport',
    'Account details row 1' => '#statistics_table tbody tr:nth-of-type(1)',
));

define('SITEOPTIONS', array(
    'Notification settings' => '#siteoptions_notificationsettings_open',
));

define('SMARTEVIDENCE', array(
    'Annotation' => '#activate_blocktype_annotation',
    'Smartevidence' => '#activate_module_framework',
    'Matrix table' => '#tablematrix',
    // Edit buttons on rows in the annotation modal.
    'Feedback annotation row 2' => "div[id^=annotation_feedbacktable] div.list-group-item:nth-child(2) div.comment-item-buttons",
    'Feedback annotation row 3' => "div[id^=annotation_feedbacktable] div.list-group-item:nth-child(3) div.comment-item-buttons",
    'Feedback annotation row 4' => "div[id^=annotation_feedbacktable] div.list-group-item:nth-child(4) div.comment-item-buttons",
));

define('TAGS', array(
    'Tags section' => '#edit_tags_container',
    'My tags list' => '.mytags',
    'Show more tags' => '.text-small .icon-ellipsis-h',
    'Tags block' => '#sb-tags',
    'Search results for all tags' => '#results_container',
));

define('TINYMCE', array(
    'Tinymce editor menu' => '.tox-toolbar',
    'Tinymce editor' => '.tox-edit-area',
));

define ('WEBSERVICES', array(
    'Manage service access tokens' => '#webservices_token_pseudofieldset',
));

define('OUTCOMES', [
    'Save level' => '[id$=level_submit]',
    'Activity pages' =>  '.progresstitle',
    'Progress form' => '.outcome-progress-form',
    'Complete outcome' => '#complete-confirm-form',
    'Sign off activity page' => '#signoff-confirm-form',
    'Un-sign off activity page' => '#unsignoff-confirm-form',
    'Activity complete icon' => '#activity_complete',
    'Incomplete outcome form' => '#incomplete-confirm-form',
    'Complete outcome form' => '#complete-confirm-form',
]);

/**
 * Looks for the css or xpath for the requested property.
 *
 * If a location (array constant) is passed in, checks that array directly,
 * otherwise searches all the array constants until a matching property key is found.
 *
 * Returns the css/xpath value, plus the selectortype (css_element/xpath_element), or null if none found. A check for null return
 * and throwing an ExpectationException will help the test writer debug.
 *
 * @param string $property name of page element to look up
 * @param string $location name of array constant property is in (optional)
 * @return array (selector, selectortype) or null if not found
 */
function get_property($property, $location = null) {
  $locator = array();
    if (!$location) {
        //we don't know which array to look in, so search all in order
        //stop the search once key is found
        log_debug($property);
        $stopsearch = false;
        while (!$stopsearch) {
            foreach (LOCATORS as $locate) {
                if (isset($locate[ucfirst($property)])) {
                    $locator[0] = $locate[ucfirst($property)];
                    $stopsearch = true;
                }
            }
            $stopsearch = true; //if key not found, stop looking!
        }
    }
    else {
        //tell PHP we mean the constant, not just some string with the same name
        $location = constant(strtoupper($location));
        if (isset($location[ucfirst($property)])) {
            $locator[0] = $location[ucfirst($property)];
        }
    }
    // if a property was found, set css or xpath and return
    if (isset($locator[0])) {
        $locator[1] = set_selector_type($locator[0]);
        return $locator;
    }
    else {
        return null;//no property found.
    }
}

/**
 * Avoid having to individually set the selector type by using regex
 * to determine the type.
 *
 * Note: There are other types available, as in BehatSelectors.php,
 * $allowedselectors. We don't currently use these, so they are not
 * included here.
 *
 * @TODO investigate this:
 * Mahara selector types are currently handled by some custom code
 * before being passed to behat as behat selector types.
 * This might be redundant functionality.
 *
 *  @param string $locator css or xpath returned by the LOCATORS arrays
*/
function set_selector_type($locator) {
    if (preg_match('/^\/\//', $locator)) {
        return 'xpath_element';
    }
    else {
        return 'css_element';
    }
}
