<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();


function smarty_core() {
    require_once 'dwoo/dwoo/dwooAutoload.php';
    require_once 'dwoo/mahara/Dwoo_Mahara.php';

    return new Dwoo_Mahara();
}


/**
 * This function creates a Smarty object and sets it up for use within our
 * podclass app, setting up some variables.
 *
 * The variables that it sets up are:
 *
 * - WWWROOT: The base url for the Mahara system
 * - USER: The user object
 * - JAVASCRIPT: A list of javascript files to include in the header.  This
 *   list is passed into this function (see below).
 * - HEADERS: An array of any further headers to set.  Each header is just
 *   straight HTML (see below).
 * - PUBLIC: Set true if this page is a public page
 * - MAINNAV: Array defining the main navigation
 *
 * @param $javascript A list of javascript includes.  Each include should be just
 *                    the name of a file, and reside in js/{filename}
 * @param $headers    A list of additional headers.  These are to be specified as
 *                    actual HTML.
 * @param $strings    A list of language strings required by the javascript code.
 * @return Smarty
 */

function smarty($javascript = array(), $headers = array(), $pagestrings = array(), $extraconfig = array()) {
    global $USER, $SESSION, $THEME;

    if (!is_array($headers)) {
        $headers = array();
    }
    if (!is_array($pagestrings)) {
        $pagestrings = array();
    }
    if (!is_array($extraconfig)) {
        $extraconfig = array();
    }

    $SIDEBLOCKS = array();

    $smarty = smarty_core();

    $wwwroot = get_config('wwwroot');
    // NOTE: not using jswwwroot - it seems to wreck image paths if you 
    // drag them around the wysiwyg editor
    $jswwwroot = json_encode($wwwroot);

    $theme_list = array();
    
    if (function_exists('pieform_get_headdata')) {
        $headers = array_merge($headers, pieform_get_headdata());
    }

    // Insert the appropriate javascript tags 
    $javascript_array = array();
    $jsroot = $wwwroot . 'js/';

    $langdirection = get_string('thisdirection', 'langconfig');

    // TinyMCE must be included first for some reason we're not sure about
    $checkarray = array(&$javascript, &$headers);
    $found_tinymce = false;
    foreach ($checkarray as &$check) {
        if (($key = array_search('tinymce', $check)) !== false || ($key = array_search('tinytinymce', $check)) !== false) {
            if (!$found_tinymce) {
                $found_tinymce = $check[$key];
                $javascript_array[] = $jsroot . 'tinymce/tiny_mce.js';
                $content_css = json_encode($THEME->get_url('style/tinymce.css'));
                $language = substr(current_language(), 0, 2);
                $extrasetup = isset($extraconfig['tinymcesetup']) ? $extraconfig['tinymcesetup'] : '';

                $adv_buttons = array(
                    "undo,redo,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,link,unlink,separator,code,fullscreen",
                    "bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,hr,emotions,image,spellchecker,cleanup,separator,link,unlink,separator,code",
                    "undo,redo,separator,bullist,numlist,separator,tablecontrols,separator,cut,copy,paste,pasteword",
                    "fontselect,separator,fontsizeselect,separator,formatselect",
                );

                // For right-to-left langs, reverse button order & align controls right.
                $tinymce_langdir = $langdirection == 'rtl' ? 'rtl' : 'ltr';
                $toolbar_align = 'left';

                if ($check[$key] == 'tinymce') {
                    $tinymce_config = <<<EOF
    mode: "none",
    theme: "advanced",
    plugins: "table,emotions,spellchecker,inlinepopups,paste",
    theme_advanced_buttons1 : "{$adv_buttons[1]}",
    theme_advanced_buttons2 : "{$adv_buttons[2]}",
    theme_advanced_buttons3 : "{$adv_buttons[3]}",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "{$toolbar_align}",
    //width: '512',
EOF;
                }
                else {
                    $tinymce_config = <<<EOF
    mode: "textareas",
    editor_selector: 'tinywysiwyg',
    theme: "advanced",
    plugins: "fullscreen,inlinepopups,autoresize",
    theme_advanced_buttons1 : "{$adv_buttons[0]}",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "{$toolbar_align}",
    fullscreen_new_window: true,
    fullscreen_settings: {
        theme: "advanced",
        plugins: "table,emotions,iespell,inlinepopups,paste",
        theme_advanced_buttons1 : "{$adv_buttons[1]}",
        theme_advanced_buttons2 : "{$adv_buttons[2]}",
        theme_advanced_buttons3 : "{$adv_buttons[3]}"
    },
EOF;
                }

                $headers[] = <<<EOF
<script type="text/javascript">
tinyMCE.init({
    button_tile_map: true,
    {$tinymce_config}
    extended_valid_elements : "object[width|height|classid|codebase],param[name|value],embed[src|type|width|height|flashvars|wmode],script[src,type,language],+ul[id|type|compact],iframe[src|width|height|align|title|class|type|frameborder|allowfullscreen]",
    urlconverter_callback : "custom_urlconvert",
    language: '{$language}',
    directionality: "{$tinymce_langdir}",
    content_css : {$content_css},
    //document_base_url: {$jswwwroot},
    remove_script_host: false,
    relative_urls: false,
    setup: function(ed) {
        ed.onInit.add(function(ed) {
            if (typeof(editor_to_focus) == 'string' && ed.editorId == editor_to_focus) {
                ed.focus();
            }
        });
        {$extrasetup}
    }
});
function custom_urlconvert (u, n, e) {
  // Don't convert the url on the skype status buttons.
  if (u.indexOf('skype:') == 0) {
      return u;
  }
  var t = tinyMCE.activeEditor, s = t.settings;

  // Don't convert link href since thats the CSS files that gets loaded into the editor also skip local file URLs
  if (!s.convert_urls || (e && e.nodeName == 'LINK') || u.indexOf('file:') === 0)
      return u;

  // Convert to relative
  if (s.relative_urls)
      return t.documentBaseURI.toRelative(u);

  // Convert to absolute
  u = t.documentBaseURI.toAbsolute(u, s.remove_script_host);

  return u;
}
</script>

EOF;
                unset($check[$key]);
            }
            else {
                if ($check[$key] != $found_tinymce) {
                    log_warn('Two differently configured tinyMCE instances have been asked for on this page! This is not possible');
                }
                unset($check[$key]);
            }
        }
        // Load jquery first, so that it doesn't break Mochikit
        if (($key = array_search('jquery', $check)) !== false) {
            $jquery = (get_config('developermode') & DEVMODE_UNPACKEDJS) ? 'jquery-1.5.2.js' : 'jquery-1.5.2.min.js';
            array_unshift($javascript_array, $jsroot . 'jquery/' . $jquery);
            // Make jQuery accessible with $j (Mochikit has $)
            $headers[] = '<script type="text/javascript">$j=jQuery;</script>';
            unset($check[$key]);
        }
    }

    if (get_config('developermode') & DEVMODE_UNPACKEDJS) {
        $javascript_array[] = $jsroot . 'MochiKit/MochiKit.js';
        $javascript_array[] = $jsroot . 'MochiKit/Position.js';
        $javascript_array[] = $jsroot . 'MochiKit/Color.js';
        $javascript_array[] = $jsroot . 'MochiKit/Visual.js';
        $javascript_array[] = $jsroot . 'MochiKit/DragAndDrop.js';
        $javascript_array[] = $jsroot . 'MochiKit/Format.js';
    }
    else {
        $javascript_array[] = $jsroot . 'MochiKit/Packed.js';
    }
    $javascript_array[] = $jsroot . 'keyboardNavigation.js';

    $strings = array();
    foreach ($pagestrings as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $tag) {
                $strings[$tag] = get_raw_string($tag, $k);
            }
        }
        else {
            $strings[$k] = get_raw_string($k, $v);
        }
    }

    $jsstrings = jsstrings();
    $themepaths = themepaths();

    foreach ($javascript as $jsfile) {
        // For now, if there's no path in the js file, assume it's in
        // $jsroot and append '.js' to the name.  Later we may want to
        // ensure all smarty() calls include the full path to the js
        // file, with the proper extension.
        if (strpos($jsfile, '/') === false) {
            $javascript_array[] = $jsroot . $jsfile . '.js';
            if (isset($jsstrings[$jsfile])) {
                foreach ($jsstrings[$jsfile] as $section => $tags) {
                    foreach ($tags as $tag) {
                        $strings[$tag] = get_raw_string($tag, $section);
                    }
                }
            }
            if (isset($themepaths[$jsfile])) {
                foreach ($themepaths[$jsfile] as $themepath) {
                    $theme_list[$themepath] = $THEME->get_url($themepath);
                }
            }
        }
        else if (strpos($jsfile, 'http://') === false) {
            // A local .js file with a fully specified path
            $javascript_array[] = $wwwroot . $jsfile;
            // If $jsfile is from a plugin (i.e. plugintype/pluginname/js/foo.js)
            // Then get js strings from static function jsstrings in plugintype/pluginname/lib.php 
            $bits = explode('/', $jsfile);
            if (count($bits) == 4) {
                safe_require($bits[0], $bits[1]);
                $pluginclass = generate_class_name($bits[0], $bits[1]);
                $name = substr($bits[3], 0, strpos($bits[3], '.js'));
                if (is_callable(array($pluginclass, 'jsstrings'))) {
                    $tempstrings = call_static_method($pluginclass, 'jsstrings', $name);
                    foreach ($tempstrings as $section => $tags) {
                        foreach ($tags as $tag) {
                            $strings[$tag] = get_raw_string($tag, $section);
                        }
                    }
                }
                if (is_callable(array($pluginclass, 'jshelp'))) {
                    $tempstrings = call_static_method($pluginclass, 'jshelp', $name);
                    foreach ($tempstrings as $section => $tags) {
                        foreach ($tags as $tag) {
                            $strings[$tag . '.help'] = get_help_icon($bits[0], $bits[1], null, null,
                                                                     null, $tag);
                        }
                    }
                }
                if (is_callable(array($pluginclass, 'themepaths'))) {
                    $tmpthemepaths = call_static_method($pluginclass, 'themepaths', $name);
                    foreach ($tmpthemepaths as $themepath) {
                        $theme_list[$themepath] = $THEME->get_url($themepath);
                    }
                }
            }
        }
        else {
            // A remote .js file
            $javascript_array[] = $jsfile;
        }
    }

    $javascript_array[] = $jsroot . 'mahara.js';
    if (get_config('developermode') & DEVMODE_DEBUGJS) {
        $javascript_array[] = $jsroot . 'debug.js';
    }

    foreach ($jsstrings['mahara'] as $section => $tags) {
        foreach ($tags as $tag) {
            $strings[$tag] = get_raw_string($tag, $section);
        }
    }
    if (isset($extraconfig['themepaths']) && is_array($extraconfig['themepaths'])) {
        foreach ($extraconfig['themepaths'] as $themepath) {
            $theme_list[$themepath] = $THEME->get_url($themepath);
        }
    }

    $stringjs = '<script type="text/javascript">';
    $stringjs .= 'var strings = ' . json_encode($strings) . ';';
    $stringjs .= '</script>';

    // stylesheet set up - if we're in a plugin also get its stylesheet
    $stylesheets = array_reverse(array_values($THEME->get_url('style/style.css', true)));
    if (defined('SECTION_PLUGINTYPE') && defined('SECTION_PLUGINNAME') && SECTION_PLUGINTYPE != 'core') {
        if ($pluginsheets = $THEME->get_url('style/style.css', true, SECTION_PLUGINTYPE . '/' . SECTION_PLUGINNAME)) {
            $stylesheets = array_merge($stylesheets, array_reverse($pluginsheets));
        }
    }
    if (defined('ADMIN') || defined('INSTITUTIONALADMIN')) {
        if ($adminsheets = $THEME->get_url('style/admin.css', true)) {
            $stylesheets = array_merge($stylesheets, array_reverse($adminsheets));
        }
    }
    if (get_config('developermode') & DEVMODE_DEBUGCSS) {
        $stylesheets[] = get_config('wwwroot') . 'theme/debug.css';
    }

    // look for extra stylesheets
    if (isset($extraconfig['stylesheets']) && is_array($extraconfig['stylesheets'])) {
        foreach ($extraconfig['stylesheets'] as $extrasheet) {
            if ($sheets = $THEME->get_url($extrasheet, true)) {
                $stylesheets = array_merge($stylesheets, array_reverse(array_values($sheets)));
            }
        }
    }

    // Include rtl.css for right-to-left langs
    if ($langdirection == 'rtl') {
        $smarty->assign('LANGDIRECTION', 'rtl');
        if ($rtlsheets = $THEME->get_url('style/rtl.css', true)) {
            $stylesheets = array_merge($stylesheets, array_reverse($rtlsheets));
        }
    }

    $smarty->assign('STRINGJS', $stringjs);

    $smarty->assign('STYLESHEETLIST', $stylesheets);
    if (!empty($theme_list)) {
        // this gets assigned in smarty_core, but do it again here if it's changed locally
        $smarty->assign('THEMELIST', json_encode(array_merge((array)json_decode($smarty->get_template_vars('THEMELIST')),  $theme_list))); 
    }


    $sitename = get_config('sitename');
    if (!$sitename) {
       $sitename = 'Mahara';
    }
    $smarty->assign('sitename', $sitename);

    if (defined('TITLE')) {
        $smarty->assign('PAGETITLE', TITLE . ' - ' . $sitename);
        $smarty->assign('heading', TITLE);
    }
    else {
        $smarty->assign('PAGETITLE', $sitename);
    }

    if (function_exists('local_header_top_content')) {
        $smarty->assign('SITETOP', local_header_top_content());
    }
    if (defined('PUBLIC')) {
        $smarty->assign('PUBLIC', true);
    }
    if (defined('ADMIN')) {
        $smarty->assign('ADMIN', true);
    }
    if (defined('INSTITUTIONALADMIN')) {
        $smarty->assign('INSTITUTIONALADMIN', true);
    }

    $smarty->assign('LOGGEDIN', $USER->is_logged_in());
    if ($USER->is_logged_in()) {
        global $SELECTEDSUBNAV; // It's evil, but rightnav & mainnav stuff are now in different templates.
        $smarty->assign('MAINNAV', main_nav());
        $smarty->assign('RIGHTNAV', right_nav());
        $smarty->assign('SELECTEDSUBNAV', $SELECTEDSUBNAV);
    }
    else {
        $smarty->assign('sitedefaultlang', get_string('sitedefault', 'admin') . ' (' . 
                        get_string_from_language(get_config('lang'), 'thislanguage') . ')');
        $smarty->assign('LANGUAGES', get_languages());
    }
    $smarty->assign('FOOTERMENU', footer_menu());

    $smarty->assign_by_ref('USER', $USER);
    $smarty->assign('SESSKEY', $USER->get('sesskey'));
    $smarty->assign_by_ref('JAVASCRIPT', $javascript_array);
    $smarty->assign_by_ref('HEADERS', $headers);
    $siteclosedforupgrade = get_config('siteclosed');
    if ($siteclosedforupgrade && get_config('disablelogin')) {
        $smarty->assign('SITECLOSED', 'logindisabled');
    }
    else if ($siteclosedforupgrade || get_config('siteclosedbyadmin')) {
        $smarty->assign('SITECLOSED', 'loginallowed');
    }

    if ((!isset($extraconfig['pagehelp']) || $extraconfig['pagehelp'] !== false)
        and $help = has_page_help()) {
        $smarty->assign('PAGEHELPNAME', $help[0]);
        $smarty->assign('PAGEHELPICON', $help[1]);
    }
    if (defined('GROUP')) {
        require_once('group.php');
        $group = group_current_group();
        $smarty->assign('GROUP', $group);
        if (!defined('NOGROUPMENU')) {
            $smarty->assign('SUBPAGENAV', group_get_menu_tabs());
            $smarty->assign('PAGEHEADING', $group->name);
        }
    }

    // ---------- sideblock stuff ----------
    $sidebars = !isset($extraconfig['sidebars']) || $extraconfig['sidebars'] !== false;
    if ($sidebars && !defined('INSTALLER') && (!defined('MENUITEM') || substr(MENUITEM, 0, 5) != 'admin')) {
        if (get_config('installed') && !defined('ADMIN') && !defined('INSTITUTIONALADMIN')) {
            $data = site_menu();
            if (!empty($data)) {
                $smarty->assign('SITEMENU', site_menu());
                $SIDEBLOCKS[] = array(
                    'name'   => 'linksandresources',
                    'weight' => 10,
                    'data'   => $data,
                );
            }
        }

        if ($USER->is_logged_in() && defined('MENUITEM') &&
            (substr(MENUITEM, 0, 11) == 'myportfolio' || substr(MENUITEM, 0, 7) == 'content')) {
            if (get_config('showselfsearchsideblock')) {
                $SIDEBLOCKS[] = array(
                    'name'   => 'selfsearch',
                    'weight' => 0,
                    'data'   => array(),
                );
            }
            if (get_config('showtagssideblock')) {
                $SIDEBLOCKS[] = array(
                    'name'   => 'tags',
                    'id'     => 'sb-tags',
                    'weight' => 0,
                    'data'   => tags_sideblock(),
                );
            }
        }

        if($USER->is_logged_in() && !defined('ADMIN') && !defined('INSTITUTIONALADMIN')) {
            $SIDEBLOCKS[] = array(
                'name'   => 'profile',
                'id'     => 'sb-profile',
                'weight' => -20,
                'data'   => profile_sideblock()
            );
            if (get_config('showonlineuserssideblock')) {
                $SIDEBLOCKS[] = array(
                    'name'   => 'onlineusers',
                    'id'     => 'sb-onlineusers',
                    'weight' => -10,
                    'data'   => onlineusers_sideblock(),
                );
            }
        }

        if(defined('GROUP')) {
            $SIDEBLOCKS[] = array(
                'name'   => 'group',
                'id'     => 'sb-groupnav',
                'weight' => -10,
                'data'   => group_sideblock()
            );
        }

        if (!$USER->is_logged_in() && !(get_config('siteclosed') && get_config('disablelogin'))) {
            $SIDEBLOCKS[] = array(
                'name'   => 'login',
                'weight' => -10,
                'id'     => 'sb-loginbox',
                'data'   => array(
                    'loginform' => auth_generate_login_form(),
                ),
            );
        }

        if (get_config('enablenetworking')) {
            require_once(get_config('docroot') .'api/xmlrpc/lib.php');
            if ($USER->is_logged_in() && $ssopeers = get_service_providers($USER->authinstance)) {
                $SIDEBLOCKS[] = array(
                    'name'   => 'ssopeers',
                    'weight' => 1,
                    'data'   => $ssopeers,
                );
            }
        }

        if (isset($extraconfig['sideblocks']) && is_array($extraconfig['sideblocks'])) {
            foreach ($extraconfig['sideblocks'] as $sideblock) {
                $SIDEBLOCKS[] = $sideblock;
            }
        }

        usort($SIDEBLOCKS, create_function('$a,$b', 'if ($a["weight"] == $b["weight"]) return 0; return ($a["weight"] < $b["weight"]) ? -1 : 1;'));

        // Place all sideblocks on the right. If this structure is munged 
        // appropriately, you can put blocks on the left. In future versions of 
        // Mahara, we'll make it easy to do this.
        $sidebars = $sidebars && !empty($SIDEBLOCKS);
        $SIDEBLOCKS = array('left' => array(), 'right' => $SIDEBLOCKS);

        $smarty->assign('userauthinstance', $SESSION->get('authinstance'));
        $smarty->assign('MNETUSER', $SESSION->get('mnetuser'));
        $smarty->assign('SIDEBLOCKS', $SIDEBLOCKS);
        $smarty->assign('SIDEBARS', $sidebars);

    }

    if ($USER->get('parentuser')) {
        $smarty->assign('USERMASQUERADING', true);
        $smarty->assign('masqueradedetails', get_string('youaremasqueradingas', 'mahara', display_name($USER)));
        $smarty->assign('becomeyouagain',
            ' <a href="' . hsc($wwwroot) . 'admin/users/changeuser.php?restore=1">'
            . get_string('becomeadminagain', 'admin', hsc($USER->get('parentuser')->name))
            . '</a>');
    }

    return $smarty;
}


/**
 * Manages theme configuration.
 *
 * Does its best to give the user _a_ theme, even if it's not the theme they 
 * want to use (e.g. the theme they want has been uninstalled)
 */
class Theme {

    /**
     * The base name of the theme (the name of the directory in which it lives)
     */
    public $basename = '';

    /**
     * A human-readable version of the theme name
     */
    public $displayname = '';

    /**
     * Which pieform renderer to use by default for all forms
     */
    public $formrenderer = '';

    /**
     * Directories where to look for templates by default
     */
    public $templatedirs = array();

    /**
     * Theme inheritance path from this theme to 'raw'
     */
    public $inheritance = array();

    /**
     * What unit the left/center/right column widths are in. 'pixels' or 'percent'
     */
    public $columnwidthunits    = '';

    /**
     * Width of the left column. Integer - see $columnwidthunits
     */
    public $leftcolumnwidth     = 256;

    /**
     * Background colour for the left column
     */
    public $leftcolumnbgcolor   = '#fff';

    /**
     * Background colour for the center column
     */
    public $centercolumnbgcolor = '#fff';

    /**
     * Width of the right column. Integer - see $columnwidthunits
     */
    public $rightcolumnwidth    = 256;

    /**
     * Background colour for the right column
     */
    public $rightcolumnbgcolor  = '#fff';


    /**
     * Initialises a theme object based on the theme 'hint' passed.
     *
     * If arg is a string, it's taken to be a theme name. If it's a user 
     * object, we ask it for a theme name. If it's an integer, we pretend 
     * that's a user ID and ask for the theme for that user.
     *
     * If the theme they want doesn't exist, the object is initialised for the 
     * default theme. This means you can initialise one of these for a user
     * and then use it without worrying if the theme exists.
     *
     * @param mixed $arg Theme name, user object or user ID
     */
    public function __construct($arg) {
        if (is_string($arg)) {
            $themename = $arg;
        }
        else if ($arg instanceof User) {
            $themename = $arg->get('theme');
        }
        else if (is_int($arg)) {
            $user = new User();
            $user->find_by_id($arg);
            $themename = $user->get('theme');
        }
        else {
            throw new SystemException("Argument to Theme::__construct was not a theme name, user object or user ID");
        }

        if (!$themename) {
            // Theme to show to when no theme has been suggested
            if (!$themename = get_config('theme')) {
                $themename = 'raw';
            }
        }

        // check the validity of the name
        if ($this->name_is_valid($themename)) {
            $this->init_theme($themename);
        } else {
            throw new SystemException("Theme name is in invalid form: '$themename'");
        }
    }

    /**
     * Given a theme name, check that it is valid
     */
    public static function name_is_valid($themename) {
        // preg_match returns 0 if invalid characters were found, 1 if not
        return (preg_match('/^[a-zA-Z0-9_-]+$/', $themename) == 1);
    }

    /**
     * Given a theme name, reads in all config and sets fields on this object
     */
    private function init_theme($themename) {
        $this->basename = $themename;

        $themeconfigfile = get_config('docroot') . 'theme/' . $this->basename . '/themeconfig.php';
        if (!is_readable($themeconfigfile)) {
            // We can safely assume that the default theme is installed, users 
            // should never be able to remove it
            $this->basename = 'default';
            $themeconfigfile = get_config('docroot') . 'theme/default/themeconfig.php';
        }

        require($themeconfigfile);

        foreach (get_object_vars($theme) as $key => $value) {
            $this->$key = $value;
        }

        if (!isset($this->displayname)) {
            $this->displayname = $this->basename;
        }
        if (!isset($theme->parent) || !$theme->parent) {
            $theme->parent = 'raw';
        }

        $this->templatedirs[] = get_config('docroot') . 'theme/' . $this->basename . '/templates/';
        $this->inheritance[]  = $this->basename;

        // Now go through the theme hierarchy assigning variables from the 
        // parent themes
        $currenttheme = $this->basename;
        while ($currenttheme != 'raw') {
            $currenttheme = isset($theme->parent) ? $theme->parent : 'raw';
            $parentconfigfile = get_config('docroot') . 'theme/' . $currenttheme . '/themeconfig.php';
            require($parentconfigfile);
            foreach (get_object_vars($theme) as $key => $value) {
                if (!isset($this->$key) || !$this->$key) {
                    $this->$key = $value;
                }
            }
            $this->templatedirs[] = get_config('docroot') . 'theme/' . $currenttheme . '/templates/';
            $this->inheritance[]  = $currenttheme;
        }
    }

    /**
     * stuff
     */
    public function get_url($filename, $all=false, $plugindirectory='') {
        return $this->_get_path($filename, $all, $plugindirectory, get_config('wwwroot'));
    }

    public function get_path($filename, $all=false, $plugindirectory='') {
        return $this->_get_path($filename, $all, $plugindirectory, get_config('docroot'));
    }

    private function _get_path($filename, $all, $plugindirectory, $returnprefix) {
        $list = array();
        $plugindirectory = ($plugindirectory && substr($plugindirectory, -1) != '/') ? $plugindirectory . '/' : $plugindirectory;

        foreach ($this->inheritance as $themedir) {
            if (is_readable(get_config('docroot') . $plugindirectory . 'theme/' . $themedir . '/static/' . $filename)) {
                if ($all) {
                    $list[$themedir] = $returnprefix . $plugindirectory . 'theme/' . $themedir . '/static/' . $filename;
                }
                else {
                    return $returnprefix . $plugindirectory . 'theme/' . $themedir . '/static/' . $filename;
                }
            }
        }
        if ($all) {
            return $list;
        }

        $extra = '';
        if ($plugindirectory) {
            $extra = ", plugindir $plugindirectory";
        }
        log_debug("Missing file in theme {$this->basename}{$extra}: $filename");
        return $returnprefix . $plugindirectory . 'theme/' . $themedir . '/static/' . $filename;
    }

}


/** 
 * Returns the lists of strings used in the .js files
 * @return array                   
 */

function jsstrings() {
    return array(
       'mahara' => array(                        // js file
            'mahara' => array(                   // section
                'namedfieldempty',               // string name
                'processing',
                'requiredfieldempty',
                'unknownerror',
                'loading',
                'showtags',
                'unreadmessages',
                'unreadmessage',
                'pendingfriend',
                'pendingfriends',
                'couldnotgethelp',
                'password',
                'username',
                'login',
                'sessiontimedout',
                'loginfailed',
                'home',
                'youhavenottaggedanythingyet',
            ),
        ),
        'tablerenderer' => array(
            'mahara' => array(
                'firstpage',
                'nextpage',
                'prevpage',
                'lastpage',
            )
        ),
        'friends' => array(
            'group' => array(
                'confirmremovefriend',
                'seeallviews',
                'noviewstosee',
                'sendmessage',
                'whymakemeyourfriend',
                'approverequest',
                'denyrequest',
                'pending',
                'removefromfriendslist',
                'views',
                'trysearchingforfriends',
                'nobodyawaitsfriendapproval',
                'sendfriendrequest',
                'addtomyfriends',
                'friendshiprequested',
                'userdoesntwantfriends',
                'existingfriend',
                'nosearchresultsfound',
                'reason',
                'requestfriendship',
                'cancel',
            ),
        ),
        'views' => array(
            'view' => array(
                'confirmdeleteblockinstance',
                'blocksinstructionajax',
                'Configure',
            ),
        ),
        'adminusersearch' => array(
            'admin' => array(
                'suspenduser',
                'suspensionreason',
            ),
            'mahara' => array(
                'cancel',
            ),
        ),
    );
}

function themepaths() {

    static $paths;
    if (empty($paths)) {
        $paths = array(
            'mahara' => array(
                'images/icon_close.gif',
                'images/edit.gif',
                'images/failure.gif',
                'images/loading.gif',
                'images/success.gif',
                'images/icon_problem.gif',
                'images/icon_help.gif',
                'style/js.css',
            ),
        );
    }
    return $paths;
}

/** 
 * Takes an array of string identifiers and returns an array of the
 * corresponding strings, quoted for use in inline javascript here
 * docs.
 */

function quotestrings($strings) {
    $qstrings = array();
    foreach ($strings as $section => $tags) {
        foreach ($tags as $tag) {
            $qstrings[$tag] = json_encode(get_string($tag, $section));
        }
    }
    return $qstrings;
}

/** 
 * This function sets up and caches info about the current selected theme
 * contains inheritance path (used for locating images) and template dirs
 * and potentially more stuff later ( like mime header to send (html vs xhtml))
 * @return object
 */
function theme_setup() {
    global $THEME;
    log_warn("theme_setup() is deprecated - please use the global \$THEME object instead");
    return $THEME;
}

/** 
 * This function returns the full url to an image
 * Always use it to get image urls
 * @param $imagelocation path to image relative to theme/$theme/static/
 * @param $pluginlocation path to plugin relative to docroot
 */
function theme_get_url($location, $pluginlocation='', $all = false) {
    global $THEME;
    log_warn("theme_get_url() is deprecated: Use \$THEME->get_url() instead");
    $plugintype = $pluginname = '';
    if ($pluginlocation) {
        list($plugintype, $pluginname) = explode('/', $pluginlocation);
        $pluginname = substr($pluginname, 0, -1);
    }
    return $THEME->get_url($location, $all, $plugintype, $pluginname);
}

/** 
 * This function returns the full path to an image
 * Always use it to get image paths
 * @param $imagelocation path to image relative to theme/$theme/static/
 * @param $pluginlocation path to plugin relative to docroot
 */
function theme_get_path($location, $pluginlocation='', $all=false) {
    global $THEME;
    log_warn("theme_get_path() is deprecated: Use \$THEME->get_path() instead");
    $plugintype = $pluginname = '';
    if ($pluginlocation) {
        list($plugintype, $pluginname) = explode('/', $pluginlocation);
        $pluginname = substr($pluginname, 0, -1);
    }
    return $THEME->get_path($location, $all, $plugintype, $pluginname);
}

/**
 * This function sends headers suitable for all JSON returning scripts.
 *
 */
function json_headers() {
    // @todo Catalyst IT Ltd
    // header('Content-type: text/x-json');
    header('Content-type: text/plain');
    header('Pragma: no-cache');
}

/**
 * This function sends a JSON message, and ends the script.
 *
 * Scripts receiving replies will recieve a JSON array with two fields:
 *
 *  - error: True or false depending on whether the request was successful
 *  - message: JSON data representing a message sent back from the script
 *
 * @param boolean $error   Whether the script ended in an error or not
 * @param string  $message A message to pass back to the user, can be an
 *                         array of JSON data
 */
function json_reply($error, $message, $returncode=0) {
    json_headers();
    echo json_encode(array('error' => $error, 'message' => $message, 'returnCode' => $returncode));
    perf_to_log();
    exit;
}

function _param_retrieve($name) {
    // prefer post
    if (isset($_POST[$name])) {
        $value = $_POST[$name];
    } 
    else if (isset($_GET[$name])) {
        $value = $_GET[$name];
    }
    else if (func_num_args() == 2) {
        $php_work_around = func_get_arg(1);
        return array($php_work_around, true);
    }
    else {
        throw new ParameterException("Missing parameter '$name' and no default supplied");
    }

    return array($value, false);
}

/**
 * This function returns a GET or POST parameter with optional default.  If the
 * default isn't specified and the parameter hasn't been sent, a
 * ParameterException exception is thrown
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_variable($name) {
    $args = func_get_args();
    list ($value) = call_user_func_array('_param_retrieve', $args);
    return $value;
}

/**
 * This function returns a GET or POST parameter as an integer with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid integer, a ParameterException exception is thrown
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return int The value of the parameter
 *
 */
function param_integer($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^\d+$/',$value)) {
        return (int)$value;
    }
    else if ($value == '' && isset($args[1])) {
        return $args[1];
    }

    throw new ParameterException("The '$name' parameter is not an integer");
}

/**
 * This function returns a GET or POST parameter as an integer with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid integer(allows signed integers), a ParameterException exception is thrown
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return int The value of the parameter
 *
 */
function param_signed_integer($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('#[+-]?[0-9]+#', $value)) {
        return (int)$value;
    }
    else if ($value == '' && isset($args[1])) {
        return $args[1];
    }

    throw new ParameterException("The '$name' parameter is not an integer");
}

/**
 * This function returns a GET or POST parameter as an alpha string with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid alpha string, a ParameterException exception is thrown
 *
 * Valid characters are a-z and A-Z
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_alpha($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^[a-zA-Z]+$/',$value)) {
        return $value;
    }

    throw new ParameterException("The '$name' parameter is not alphabetical only");
}

/**
 * This function returns a GET or POST parameter as an alphanumeric string with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid alpha string, a ParameterException exception is thrown
 *
 * Valid characters are a-z and A-Z and 0-9
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_alphanum($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^[a-zA-Z0-9]+$/',$value)) {
        return $value;
    }

    throw new ParameterException("The '$name' parameter is not alphanumeric only");
}

/**
 * This function returns a GET or POST parameter as an alphanumeric string with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid alpha string, a ParameterException exception is thrown
 *
 * Valid characters are a-z and A-Z and 0-9 and _ and - and .
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_alphanumext($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^[a-zA-Z0-9_.-]+$/',$value)) {
        return $value;
    }

    throw new ParameterException("The '$name' parameter contains invalid characters");
}

/**
 * This function returns a GET or POST parameter as an array of integers with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid integer list , a ParameterException exception is thrown.
 *
 * An integer list is integers separated by commas (with optional whitespace),
 * or just whitespace which indicates an empty list
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return array The value of the parameter
 *
 */
function param_integer_list($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if ($value == '') {
        return array();
    }

    if (preg_match('/^(\d+(,\d+)*)$/',$value)) {
        return array_map('intval', explode(',', $value));
    }

    throw new ParameterException("The '$name' parameter is not an integer list");
}

/**
 * This function returns a GET or POST parameter as a boolean.
 *
 * @param string The GET or POST parameter you want returned
 *
 * @return bool The value of the parameter
 *
 */
function param_boolean($name) {
    
    list ($value) = _param_retrieve($name, false);

    if (empty($value) || $value == 'off' || $value == 'no' || $value == 'false') {
        return false;
    }
    else {
        return true;
    }
}

/**
 * NOTE: this function is only meant to be used by get_imagesize_parameters(),
 * which you should use in your scripts.
 *
 * It expects the parameter to be a string, in the form /\d+x\d+/ - e.g. 
 * 200x150.
 *
 * @param string The GET or POST parameter you want checked
 * TODO: i18n for the error messages
 */
function param_imagesize($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (!preg_match('/\d+x\d+/', $value)) {
        throw new ParameterException('Invalid size for image specified');
    }

    return $value;
}

/**
 * Works out what size a requested image should be, based on request parameters
 *
 * The result of this function can be passed to get_dataroot_image_path to 
 * retrieve the filesystem path of the appropriate image
 */
function get_imagesize_parameters($sizeparam='size', $widthparam='width', $heightparam='height',
    $maxsizeparam='maxsize', $maxwidthparam='maxwidth', $maxheightparam='maxheight') {

    $size      = param_imagesize($sizeparam, '');
    $width     = param_integer($widthparam, 0);
    $height    = param_integer($heightparam, 0);
    $maxsize   = param_integer($maxsizeparam, 0);
    $maxwidth  = param_integer($maxwidthparam, 0);
    $maxheight = param_integer($maxheightparam, 0);

    return imagesize_data_to_internal_form($size, $width, $height, $maxsize, $maxwidth, $maxheight);
}

/**
 * Given sizing information, converts it to a form that get_dataroot_image_path 
 * can use.
 *
 * @param mixed $size    either an array with 'w' and 'h' keys, or a string 'WxH'. 
 *                       Image will be exactly this size
 * @param int $width     Width. Image will be scaled to be exactly this wide
 * @param int $height    Height. Image will be scaled to be exactly this high
 * @param int $maxsize   The longest side will be scaled to be this size
 * @param int $maxwidth  Use with maxheight - image dimensions will be made as 
 *                       large as possible but not exceed either one
 * @param int $maxheight Use with maxwidth - image dimensions will be made as 
 *                       large as possible but not exceed either one
 * @return mixed         A sizing parameter that can be used with get_dataroot_image_path()
 */
function imagesize_data_to_internal_form($size, $width, $height, $maxsize, $maxwidth, $maxheight) {
    $imagemaxwidth  = get_config('imagemaxwidth');
    $imagemaxheight = get_config('imagemaxheight');

    if ($size) {
        if (is_array($size)) {
            if (isset($size['w']) && isset($size['h'])) {
                $width  = $size['w'];
                $height = $size['h'];
            }
            else {
                throw new ParameterException('Size parameter is corrupt');
            }
        }
        else if (is_string($size)) {
            list($width, $height) = explode('x', $size);
        }
        else {
            throw new ParameterException('Size parameter is corrupt');
        }
        if ($width > get_config('imagemaxwidth') || $height > get_config('imagemaxheight')) {
            throw new ParameterException('Requested image size is too big');
        }
        if ($width < 16 || $height < 16) {
            throw new ParameterException('Requested image size is too small');
        }
        return array('w' => $width, 'h' => $height);
    }
    if ($maxsize) {
        if ($maxsize > $imagemaxwidth && $maxsize > $imagemaxheight) {
            throw new ParameterException('Requested image size is too big');
        }
        if ($maxsize < 16) {
            throw new ParameterException('Requested image size is too small');
        }
        return $maxsize;
    }
    if ($width) {
        if ($width > $imagemaxwidth) {
            throw new ParameterException('Requested image size is too big');
        }
        if ($width < 16) {
            throw new ParameterException('Requested image size is too small');
        }
        return array('w' => $width);
    }
    if ($height) {
        if ($height > $imagemaxheight) {
            throw new ParameterException('Requested image size is too big');
        }
        if ($height < 16) {
            throw new ParameterException('Requested image size is too small');
        }
        return array('h' => $height);
    }
    $max = array();
    if ($maxwidth) {
        if ($maxwidth > $imagemaxwidth) {
            throw new ParameterException('Requested image size is too big');
        }
        if ($maxwidth < 16) {
            throw new ParameterException('Requested image size is too small');
        }
        $max['maxw'] = $maxwidth;
    }
    if ($maxheight) {
        if ($maxheight > $imagemaxheight) {
            throw new ParameterException('Requested image size is too big');
        }
        if ($maxheight < 16) {
            throw new ParameterException('Requested image size is too small');
        }
        $max['maxh'] = $maxheight;
    }
    if (!empty($max)) {
        return $max;
    }
    return null;
}

/**
 * Gets a cookie, respecting the configured cookie prefix
 *
 * @param string $name The name of the cookie to get the value of
 * @return string      The value of the cookie, or null if the cookie does not
 *                     exist.
 */
function get_cookie($name) {
    $name = get_config('cookieprefix') . $name;
    return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : null;
}

function get_cookies($prefix) {
    static $prefixes = array();
    if (!isset($prefixes[$prefix])) {
        $prefixes[$prefix] = array();
        $cprefix = get_config('cookieprefix') . $prefix;
        foreach ($_COOKIE as $k => $v) {
            if (strpos($k, $cprefix) === 0) {
                $prefixes[$prefix][substr($k, strlen($cprefix))] = $v;
            }
        }
    }
    return $prefixes[$prefix];
}

/**
 * Sets a cookie, respecting the configured cookie prefix
 *
 * @param string $name    The name of the cookie
 * @param string $value   The value for the cookie
 * @param int    $expires The unix timestamp of the time the cookie should expire
 */
function set_cookie($name, $value='', $expires=0, $access=false) {
    $name = get_config('cookieprefix') . $name;
    $url = parse_url(get_config('wwwroot'));
    setcookie($name, $value, $expires, $url['path'], $url['host'], false, true);
    if ($access) {  // View access cookies may be needed on this request
        $_COOKIE[$name] = $value;
    }
}

/**
 * Returns an assoc array of countrys suitable for use with the "select" form
 * element
 *
 * @return array Associative array of countrycodes => countrynames
 */
function getoptions_country() {
    static $countries;
    if (!empty($countries)) {
        return $countries;
    }
    $codes = array(
        'af',
        'ax',
        'al',
        'dz',
        'as',
        'ad',
        'ao',
        'ai',
        'aq',
        'ag',
        'ar',
        'am',
        'aw',
        'au',
        'at',
        'az',
        'bs',
        'bh',
        'bd',
        'bb',
        'by',
        'be',
        'bz',
        'bj',
        'bm',
        'bt',
        'bo',
        'ba',
        'bw',
        'bv',
        'br',
        'io',
        'bn',
        'bg',
        'bf',
        'bi',
        'kh',
        'cm',
        'ca',
        'cv',
        'ky',
        'cf',
        'td',
        'cl',
        'cn',
        'cx',
        'cc',
        'co',
        'km',
        'cg',
        'cd',
        'ck',
        'cr',
        'ci',
        'hr',
        'cu',
        'cy',
        'cz',
        'dk',
        'dj',
        'dm',
        'do',
        'ec',
        'eg',
        'sv',
        'gq',
        'er',
        'ee',
        'et',
        'fk',
        'fo',
        'fj',
        'fi',
        'fr',
        'gf',
        'pf',
        'tf',
        'ga',
        'gm',
        'ge',
        'de',
        'gh',
        'gi',
        'gr',
        'gl',
        'gd',
        'gp',
        'gu',
        'gt',
        'gg',
        'gn',
        'gw',
        'gy',
        'ht',
        'hm',
        'va',
        'hn',
        'hk',
        'hu',
        'is',
        'in',
        'id',
        'ir',
        'iq',
        'ie',
        'im',
        'il',
        'it',
        'jm',
        'jp',
        'je',
        'jo',
        'kz',
        'ke',
        'ki',
        'kp',
        'kr',
        'kw',
        'kg',
        'la',
        'lv',
        'lb',
        'ls',
        'lr',
        'ly',
        'li',
        'lt',
        'lu',
        'mo',
        'mk',
        'mg',
        'mw',
        'my',
        'mv',
        'ml',
        'mt',
        'mh',
        'mq',
        'mr',
        'mu',
        'yt',
        'mx',
        'fm',
        'md',
        'mc',
        'mn',
        'ms',
        'ma',
        'mz',
        'mm',
        'na',
        'nr',
        'np',
        'nl',
        'an',
        'nc',
        'nz',
        'ni',
        'ne',
        'ng',
        'nu',
        'nf',
        'mp',
        'no',
        'om',
        'pk',
        'pw',
        'ps',
        'pa',
        'pg',
        'py',
        'pe',
        'ph',
        'pn',
        'pl',
        'pt',
        'pr',
        'qa',
        're',
        'ro',
        'ru',
        'rw',
        'sh',
        'kn',
        'lc',
        'pm',
        'vc',
        'ws',
        'sm',
        'st',
        'sa',
        'sn',
        'cs',
        'sc',
        'sl',
        'sg',
        'sk',
        'si',
        'sb',
        'so',
        'za',
        'gs',
        'es',
        'lk',
        'sd',
        'sr',
        'sj',
        'sz',
        'se',
        'ch',
        'sy',
        'tw',
        'tj',
        'tz',
        'th',
        'tl',
        'tg',
        'tk',
        'to',
        'tt',
        'tn',
        'tr',
        'tm',
        'tc',
        'tv',
        'ug',
        'ua',
        'ae',
        'gb',
        'us',
        'um',
        'uy',
        'uz',
        'vu',
        've',
        'vn',
        'vg',
        'vi',
        'wf',
        'eh',
        'ye',
        'zm',
        'zw',
    );

    foreach ($codes as $c) {
        $countries[$c] = get_string("country.{$c}");
    };
    uasort($countries, 'strcoll');
    return $countries;
}

/**
 * 
 */

function get_help_icon($plugintype, $pluginname, $form, $element, $page='', $section='') {
    global $THEME;
    // TODO: remove the hax for ie, I'm sure we can do this with a PNG file
    // I see no reason why IE has to drag the quality of the interwebs down with it

    $imageext = (isset($_SERVER['HTTP_USER_AGENT']) && false !== stripos($_SERVER['HTTP_USER_AGENT'], 'msie 6.0')) ? 'gif' : 'png';
    return ' <span class="help"><a href="" onclick="'. 
        hsc(
            'contextualHelp(' . json_encode($form) . ',' . 
            json_encode($element) . ',' . json_encode($plugintype) . ',' . 
            json_encode($pluginname) . ',' . json_encode($page) . ',' . 
            json_encode($section)
            . ',this); return false;'
        ) . '"><img src="' . $THEME->get_url('images/icon_help.' . $imageext) . '" alt="' . get_string('Help') . '" title="' . get_string('Help') . '"></a></span>';
}

function pieform_get_help(Pieform $form, $element) {
    return get_help_icon($form->get_property('plugintype'),
        $form->get_property('pluginname'),
        $form->get_name(), $element['name']);
}


/**
 * Returns the entries in the standard admin menu
 *
 * @return $adminnav a data structure containing the admin navigation
 */
function admin_nav() {
    $menu = array(
        array(
            'path'   => 'adminhome',
            'url'    => 'admin/',
            'title'  => get_string('adminhome', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configsite',
            'url'    => 'admin/site/options.php',
            'title'  => get_string('configsite', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'configsite/siteoptions',
            'url'    => 'admin/site/options.php',
            'title'  => get_string('siteoptions', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configsite/sitepages',
            'url'    => 'admin/site/pages.php',
            'title'  => get_string('editsitepages', 'admin'),
            'weight' => 20
        ),
        array(
            'path'   => 'configsite/sitemenu',
            'url'    => 'admin/site/menu.php',
            'title'  => get_string('menus', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configsite/networking',
            'url'    => 'admin/site/networking.php',
            'title'  => get_string('networking', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'configsite/siteviews',
            'url'    => 'admin/site/views.php',
            'title'  => get_string('Views', 'view'),
            'weight' => 50,
        ),
        array(
            'path'   => 'configsite/share',
            'url'    => 'admin/site/shareviews.php',
            'title'  => get_string('share', 'view'),
            'weight' => 60,
        ),
        array(
            'path'   => 'configsite/sitefiles',
            'url'    => 'artefact/file/sitefiles.php',
            'title'  => get_string('Files', 'artefact.file'),
            'weight' => 70,
        ),
        array(
            'path'   => 'configusers',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('users'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configusers/usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configusers/suspendedusers',
            'url'    => 'admin/users/suspended.php',
            'title'  => get_string('suspendedusers', 'admin'),
            'weight' => 15,
        ),
        array(
            'path'   => 'configusers/staffusers',
            'url'    => 'admin/users/staff.php',
            'title'  => get_string('sitestaff', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'configusers/adminusers',
            'url'    => 'admin/users/admins.php',
            'title'  => get_string('siteadmins', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configusers/adminnotifications',
            'url'    => 'admin/users/notifications.php',
            'title'  => get_string('adminnotifications', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'configusers/adduser',
            'url'    => 'admin/users/add.php',
            'title'  => get_string('adduser', 'admin'),
            'weight' => 50,
        ),
        array(
            'path'   => 'configusers/uploadcsv',
            'url'    => 'admin/users/uploadcsv.php',
            'title'  => get_string('uploadcsv', 'admin'),
            'weight' => 60,
        ),
        array(
            'path'   => 'managegroups',
            'url'    => 'admin/groups/groups.php',
            'title'  => get_string('groups', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'managegroups/groups',
            'url'    => 'admin/groups/groups.php',
            'title'  => get_string('administergroups', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'managegroups/categories',
            'url'    => 'admin/groups/groupcategories.php',
            'title'  => get_string('groupcategories', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'manageinstitutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('institutions', 'admin'),
            'weight' => 50,
        ),
        array(
            'path'   => 'manageinstitutions/institutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('institutions', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'manageinstitutions/institutionusers',
            'url'    => 'admin/users/institutionusers.php',
            'title'  => get_string('Members', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'manageinstitutions/institutionstaff',
            'url'    => 'admin/users/institutionstaff.php',
            'title'  => get_string('institutionstaff', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'manageinstitutions/institutionadmins',
            'url'    => 'admin/users/institutionadmins.php',
            'title'  => get_string('institutionadmins', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'manageinstitutions/institutionviews',
            'url'    => 'view/institutionviews.php',
            'title'  => get_string('Views', 'view'),
            'weight' => 50,
        ),
        array(
            'path'   => 'manageinstitutions/share',
            'url'    => 'view/institutionshare.php',
            'title'  => get_string('share', 'view'),
            'weight' => 60,
        ),
        array(
            'path'   => 'manageinstitutions/institutionfiles',
            'url'    => 'artefact/file/institutionfiles.php',
            'title'  => get_string('Files', 'artefact.file'),
            'weight' => 70,
        ),
        array(
            'path'   => 'configextensions',
            'url'    => 'admin/extensions/plugins.php',
            'title'  => get_string('Extensions', 'admin'),
            'weight' => 60,
        ),
        array(
            'path'   => 'configextensions/pluginadmin',
            'url'    => 'admin/extensions/plugins.php',
            'title'  => get_string('pluginadmin', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configextensions/filters',
            'url'    => 'admin/extensions/filter.php',
            'title'  => get_string('htmlfilters', 'admin'),
            'weight' => 20,
        ),
    );

    return $menu;
}

/**
 * Returns the entries in the standard institutional admin menu
 *
 * @return $adminnav a data structure containing the admin navigation
 */
function institutional_admin_nav() {

    return array(
        array(
            'path'   => 'configusers',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('configusers', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configusers/usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configusers/suspendedusers',
            'url'    => 'admin/users/suspended.php',
            'title'  => get_string('suspendedusers', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'configusers/adduser',
            'url'    => 'admin/users/add.php',
            'title'  => get_string('adduser', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configusers/uploadcsv',
            'url'    => 'admin/users/uploadcsv.php',
            'title'  => get_string('uploadcsv', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'manageinstitutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('manageinstitutions', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'manageinstitutions/institutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('settings'),
            'weight' => 10,
        ),
        array(
            'path'   => 'manageinstitutions/institutionusers',
            'url'    => 'admin/users/institutionusers.php',
            'title'  => get_string('Members', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'manageinstitutions/institutionstaff',
            'url'    => 'admin/users/institutionstaff.php',
            'title'  => get_string('institutionstaff', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'manageinstitutions/institutionadmins',
            'url'    => 'admin/users/institutionadmins.php',
            'title'  => get_string('institutionadmins', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'manageinstitutions/adminnotifications',
            'url'    => 'admin/users/notifications.php',
            'title'  => get_string('adminnotifications', 'admin'),
            'weight' => 50,
        ),
        array(
            'path'   => 'manageinstitutions/institutionviews',
            'url'    => 'view/institutionviews.php',
            'title'  => get_string('Views', 'view'),
            'weight' => 60,
        ),
        array(
            'path'   => 'manageinstitutions/share',
            'url'    => 'view/institutionshare.php',
            'title'  => get_string('share', 'view'),
            'weight' => 70,
        ),
        array(
            'path'   => 'manageinstitutions/institutionfiles',
            'url'    => 'artefact/file/institutionfiles.php',
            'title'  => get_string('Files', 'artefact.file'),
            'weight' => 80,
        ),
    );

}


/**
 * Returns the entries in the standard user menu
 *
 * @return $standardnav a data structure containing the standard navigation
 */
function mahara_standard_nav() {
    $exportenabled = plugins_installed('export');
    $menu = array(
        'home' => array(
            'path' => '',
            'url' => '',
            'title' => get_string('dashboard', 'view'),
            'weight' => 10,
            'accesskey' => 'h',
        ),
        'content' => array(
            'path' => 'content',
            'url'  => 'artefact/internal/', // @todo possibly do path aliasing and dispatch?
            'title' => get_string('Content'),
            'weight' => 20,
        ),
        'myportfolio' => array(
            'path' => 'myportfolio',
            'url' => 'view/',
            'title' => get_string('myportfolio'),
            'weight' => 30,
            'accesskey' => 'v',
        ),
        'myportfolio/views' => array(
            'path' => 'myportfolio/views',
            'url' => 'view/',
            'title' => get_string('Views', 'view'),
            'weight' => 10,
        ),
        'myportfolio/share' => array(
            'path' => 'myportfolio/share',
            'url' => 'view/share.php',
            'title' => get_string('share', 'view'),
            'weight' => 30,
        ),
        'myportfolio/export' => array(
            'path' => 'myportfolio/export',
            'url' => 'export/',
            'title' => get_string('Export', 'export'),
            'weight' => 70,
            'ignore' => !$exportenabled,
        ),
        'myportfolio/collection' => array(
            'path' => 'myportfolio/collection',
            'url' => 'collection/',
            'title' => get_string('Collections', 'collection'),
            'weight' => 20,
        ),
        'groups' => array(
            'path' => 'groups',
            'url' => 'group/mygroups.php',
            'title' => get_string('groups'),
            'weight' => 40,
            'accesskey' => 'g',
        ),
        'groups/mygroups' => array(
            'path' => 'groups/mygroups',
            'url' => 'group/mygroups.php',
            'title' => get_string('mygroups'),
            'weight' => 10,
        ),
        'groups/find' => array(
            'path' => 'groups/find',
            'url' => 'group/find.php',
            'title' => get_string('findgroups'),
            'weight' => 20,
        ),
        'groups/myfriends' => array(
            'path' => 'groups/myfriends',
            'url' => 'user/myfriends.php',
            'title' => get_string('myfriends'),
            'weight' => 30,
        ),
        'groups/findfriends' => array(
            'path' => 'groups/findfriends',
            'url' => 'user/find.php',
            'title' => get_string('findfriends'),
            'weight' => 40,
        ),
        'groups/views' => array(
            'path' => 'groups/views',
            'url' => 'view/sharedviews.php',
            'title' => get_string('sharedviews', 'view'),
            'weight' => 50,
        ),
    );

    $menu = array_filter($menu, create_function('$a', 'return empty($a["ignore"]);'));
    
    if ($plugins = plugins_installed('artefact')) {
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin->name);
            $plugin_menu = call_static_method(generate_class_name('artefact',$plugin->name), 'menu_items');
            $menu = array_merge($menu, $plugin_menu);
        }
    }

    if ($plugins = plugins_installed('interaction')) {
        foreach ($plugins as &$plugin) {
            safe_require('interaction', $plugin->name);
            $plugin_menu = call_static_method(generate_class_name('interaction',$plugin->name), 'menu_items');
            $menu = array_merge($menu, $plugin_menu);
        }
    }
    
    return $menu;
}

/**
 * Builds a data structure representing the menu for Mahara.
 */
function main_nav() {
    if (defined('ADMIN') || defined('INSTITUTIONALADMIN')) {
        global $USER;
        $menu = $USER->get('admin') ? admin_nav() : institutional_admin_nav();
    }
    else {
        // Build the menu structure for the site

        // The keys of each entry are as follows:
        //   path: Where the link sits in the menu. E.g. 'myporfolio/myplugin'
        //   url:  The URL to the page, relative to wwwroot. E.g. 'artefact/myplugin/'
        //   title: Translated text to use for the text of the link. E.g. get_string('myplugin', 'artefact.myplugin')
        //   weight: Where in the menu the item should be inserted. Larger number are to the right.
        $menu = mahara_standard_nav();
    }

    // local_main_nav_update allows sites to customise the menu by munging the $menu array.
    if (function_exists('local_main_nav_update')) {
        local_main_nav_update($menu);
    }
    $menu_structure = find_menu_children($menu, '');
    return $menu_structure;
}

function right_nav() {
    global $USER, $THEME;

    safe_require('notification', 'internal');
    $unread = call_static_method(generate_class_name('notification', 'internal'), 'unread_count', $USER->get('id'));

    $menu = array(
        array(
            'path' => 'settings',
            'url' => 'account/',
            'title' => get_string('settings'),
            'icon' => $THEME->get_url('images/settings.png'),
            'alt' => get_string('settings'),
            'weight' => 10,
        ),
        array(
            'path' => 'inbox',
            'url' => 'account/activity',
            'icon' => $THEME->get_url('images/email.gif'),
            'alt' => get_string('inbox'),
            'count' => $unread,
            'countclass' => 'unreadmessagecount',
            'weight' => 20,
        ),
        array(
            'path' => 'settings/account',
            'url' => 'account/',
            'title' => get_string('account'),
            'weight' => 10,
        ),
        array(
            'path' => 'settings/notifications',
            'url' => 'account/activity/preferences/',
            'title' => get_string('notifications'),
            'weight' => 30,
        ),
        array(
            'path' => 'settings/institutions',
            'url' => 'account/institutions.php',
            'title' => get_string('institutionmembership'),
            'weight' => 40,
        ),
    );
    $menu_structure = find_menu_children($menu, '');
    return $menu_structure;
}


function footer_menu($all=false) {
    $menu = array(
        'termsandconditions' => array(
            'url' => 'terms.php',
            'title' => get_string('termsandconditions'),
        ),
        'privacystatement' => array(
            'url' => 'privacy.php',
            'title' => get_string('privacystatement'),
        ),
        'about' => array(
            'url' => 'about.php',
            'title' => get_string('about'),
        ),
        'contactus' => array(
            'url' => 'contact.php',
            'title' => get_string('contactus'),
        ),
    );
    if ($all) {
        return $menu;
    }
    if ($enabled = get_config('footerlinks')) {
        $enabled = unserialize($enabled);
        foreach ($menu as $k => $v) {
            if (!in_array($k, $enabled)) {
                unset($menu[$k]);
            }
        }
    }
    return $menu;
}


/**
 * Given a menu structure and a path, returns a data structure representing all 
 * of the child menu items of the path, and removes those items from the menu 
 * structure
 *
 * Used by main_nav()
 */
function find_menu_children(&$menu, $path) {
    global $SELECTEDSUBNAV;
    $result = array();
    if (!$menu) {
        return array();
    }

    foreach ($menu as $key => $item) {
        $item['selected'] = defined('MENUITEM')
            && ($item['path'] == MENUITEM
                || ($item['path'] . '/' == substr(MENUITEM, 0, strlen($item['path'])+1)));
        if (
            ($path == '' && $item['path'] == '') ||
            ($item['path'] != '' && substr($item['path'], 0, strlen($path)) == $path && !preg_match('%/%', substr($item['path'], strlen($path) + 1)))) {
            $result[] = $item;
            unset($menu[$key]);
        }
    }

    if ($menu) {
        foreach ($result as &$item) {
            $item['submenu'] = find_menu_children($menu, $item['path']);
            if ($item['selected']) {
                $SELECTEDSUBNAV = $item['submenu'];
            }
        }
    }

    uasort($result, 'menu_sort_items');

    return $result;
}

/**
 * Comparison function for sorting menu items
 */
function menu_sort_items(&$a, &$b) {
    !isset($a['weight']) && $a['weight'] = 0;
    !isset($b['weight']) && $b['weight'] = 0;
    return $a['weight'] > $b['weight'];
}

/**
 * Site-level sidebar menu (list of links)
 * There is no admin files table yet so just get the urls.
 * @return $menu a data structure containing the site menu
 */
function site_menu() {
    global $USER;
    $menu = array();
    if ($menuitems = get_records_array('site_menu','public',(int) !$USER->is_logged_in(),'displayorder')) {
        foreach ($menuitems as $i) {
            if ($i->url) {
                $menu[] = array('name' => $i->title,
                                'link' => $i->url);
            }
            else if ($i->file) {
                $menu[] = array('name' => $i->title,
                                'link' => get_config('wwwroot') . 'artefact/file/download.php?file=' . $i->file);
            }
        }
    }
    return $menu;
}

/**
 * Returns the list of site content pages
 * @return array of names
 */
function site_content_pages() {
    return array('about', 'home', 'loggedouthome', 'privacy', 'termsandconditions');
}

function get_site_page_content($pagename) {
    if ($pagedata = get_record('site_content', 'name', $pagename)) {
        return $pagedata->content;
    }
    return get_string('sitecontentnotfound', 'mahara', get_string($pagename));
}



/** 
 * Redirects the browser to a new location. The path to redirect to can take
 * two forms:
 *  
 * - http[something]: will redirect the user to that exact URL
 * - /[something]: will redirect to WWWROOT/[something]
 *       
 * Any other form is illegal and will cause an error.
 *      
 * @param string $location The location to redirect the user to. Defaults to
 *                         the application home page.
 */     
function redirect($location='/') {
    $file = $line = null;
    if (headers_sent($file, $line)) {
        throw new SystemException("Headers already sent when redirect() was called (output started in $file on line $line");
    }

    if (substr($location, 0, 4) != 'http') {
        if (substr($location, 0, 1) != '/') {
            throw new SystemException('redirect() should be called with either'
                . ' /[something] for local redirects or http[something] for'
                . ' absolute redirects');
        }

        $location = get_config('wwwroot') . substr($location, 1);
    }

    header('HTTP/1.1 303 See Other');
    header('Location:' . $location);
    perf_to_log();
    exit;
}

/**
 * Returns a string, HTML escaped
 *
 * @param string $text The text to escape
 * @return string      The text, HTML escaped
 */
function hsc ($text) {
    return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
}

/**
 * Builds the pieform for the user search, normally found in the header of most 
 * themes
 */
function user_search_form() {
    require_once('pieforms/pieform.php');
    return pieform(array(
        'name'                => 'usf',
        'action'              => get_config('wwwroot') . 'user/find.php',
        'renderer'            => 'oneline',
        'autofocus'           => false,
        'validate'            => false,
        'presubmitcallback'   => '',
        'elements'            => array(
            'query' => array(
                'type'           => 'text',
                'defaultvalue'   => get_string('searchusers'),
                'class'          => 'emptyonfocus',
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('go'),
            )
        )
    ));
}


/**
 * Returns the name of the current script, WITH the querystring portion.
 * this function is necessary because PHP_SELF and REQUEST_URI and SCRIPT_NAME
 * return different things depending on a lot of things like your OS, Web
 * server, and the way PHP is compiled (ie. as a CGI, module, ISAPI, etc.)
 * <b>NOTE:</b> This function returns false if the global variables needed are not set.
 *
 * @return string
 */
function get_script_path() {

    if (!empty($_SERVER['REQUEST_URI'])) {
        return $_SERVER['REQUEST_URI'];

    } else if (!empty($_SERVER['PHP_SELF'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['PHP_SELF'];

    } else if (!empty($_SERVER['SCRIPT_NAME'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['SCRIPT_NAME'];

    } else if (!empty($_SERVER['URL'])) {     // May help IIS (not well tested)
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['URL'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['URL'];

    } else {
        log_warn('Warning: Could not find any of these web server variables: $REQUEST_URI, $PHP_SELF, $SCRIPT_NAME or $URL');
        return false;
    }
}

/**
 * Like {@link get_script_path()} but returns a full URL
 * @see get_script_path()
 * @return string
 */
function get_full_script_path() {

    global $CFG;

    if (!empty($CFG->wwwroot)) {
        $url = parse_url($CFG->wwwroot);
    }

    if (!empty($url['host'])) {
        $hostname = $url['host'];
    } else if (!empty($_SERVER['SERVER_NAME'])) {
        $hostname = $_SERVER['SERVER_NAME'];
    } else if (!empty($_ENV['SERVER_NAME'])) {
        $hostname = $_ENV['SERVER_NAME'];
    } else if (!empty($_SERVER['HTTP_HOST'])) {
        $hostname = $_SERVER['HTTP_HOST'];
    } else if (!empty($_ENV['HTTP_HOST'])) {
        $hostname = $_ENV['HTTP_HOST'];
    } else {
        log_warn('Warning: could not find the name of this server!');
        return false;
    }

    if (!empty($url['port'])) {
        $hostname .= ':'.$url['port'];
    } else if (!empty($_SERVER['SERVER_PORT'])) {
        if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $hostname .= ':'.$_SERVER['SERVER_PORT'];
        }
    }

    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
    } else if (isset($_SERVER['SERVER_PORT'])) { # Apache2 does not export $_SERVER['HTTPS']
        $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    } else {
        $protocol = 'http://';
    }

    $url_prefix = $protocol.$hostname;
    return $url_prefix . get_script_path();
}

/**
 * Like {@link get_script_path()} but returns a URI relative to WWWROOT
 * @see get_script_path()
 * @return string
 */
function get_relative_script_path() {
    $maharadir = get_mahara_install_subdirectory();
    // $maharadir always has a trailing '/'
    return substr(get_script_path(), strlen($maharadir) - 1);
}

/**
 * Remove query string from url
 *
 * Takes in a URL and returns it without the querystring portion
 *
 * @param string $url the url which may have a query string attached
 * @return string
 */
function strip_querystring($url) {

    if ($commapos = strpos($url, '?')) {
        return substr($url, 0, $commapos);
    } else {
        return $url;
    }
}

function has_page_help() {
    $pt = defined('SECTION_PLUGINTYPE') ? SECTION_PLUGINTYPE : null;
    $pn = defined('SECTION_PLUGINNAME') ? SECTION_PLUGINNAME : null;
    $sp = defined('SECTION_PAGE')       ? SECTION_PAGE       : null;

    if (empty($pt) || ($pt != 'core' && empty($pn))) {
        // we can't have a plugin type but no plugin name
        return false;
    }

    if (in_array($pt, plugin_types())) {
        $pagehelp = get_config('docroot') . $pt . '/' . $pn . '/lang/en.utf8/help/pages/' . $sp . '.html';
    }
    else {
        $pagehelp = get_config('docroot') . 'lang/en.utf8/help/pages/' . $pn . '/' . $sp . '.html';
    }

    if (is_readable($pagehelp)) {
        return array($sp, get_help_icon($pt, $pn, '', '', $sp));
    }
    return false;
}

//
// Cleaning/formatting functions
//

/**
 * Converts bbcodes in the given text to HTML. Also auto-links URLs.
 *
 * @param string $text The text to parse
 * @return string
 */
function parse_bbcode($text) {
    require_once('stringparser_bbcode/stringparser_bbcode.class.php');

    $bbcode = new StringParser_BBCode();
    $bbcode->setGlobalCaseSensitive(false);
    $bbcode->setRootParagraphHandling(true);

    // Convert all newlines to a common form
    $bbcode->addFilter(STRINGPARSER_FILTER_PRE, create_function('$a', 'return preg_replace("/\015\012|015\012/", "\n", $a);'));

    $bbcode->addParser(array('block', 'inline'), 'format_whitespace');
    $bbcode->addParser(array('block', 'inline'), 'autolink_text');

    // The bbcodes themselves
    $bbcode->addCode('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
                          'inline', array('listitem', 'block', 'inline', 'link'), array());
    $bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
                          'inline', array('listitem', 'block', 'inline', 'link'), array());
    $bbcode->addCode ('url', 'usecontent?', 'bbcode_url', array('usecontent_param' => 'default'),
                          'link', array('listitem', 'block', 'inline'), array('link'));
    $bbcode->addCode ('img', 'usecontent', 'bbcode_img', array(),
                      'image', array ('listitem', 'block', 'inline', 'link'), array());

    $text = $bbcode->parse($text);
    return $text;
}

/**
 * Given some plain text, adds the appropriate HTML to it to make it appear in 
 * an HTML document with the same formatting
 *
 * This includes escaping entities, replacing newlines etc. It is not 
 * particularly intelligent about paragraphs, it just adds <br> to every 
 * newline
 *
 * @param string $text The text to format
 * @return string
 */
function format_whitespace($text) {
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);
    $text = hsc($text);
    $text = str_replace('  ', '&nbsp; ', $text);
    $text = str_replace('  ', ' &nbsp;', $text);
    $text = str_replace("\n", "<br>\n", $text);
    return $text;
}

/**
 * Given raw html (eg typed in by a user), this function cleans it up
 * and removes any nasty tags that could mess up pages.
 *
 * @param string $text The text to be cleaned
 * @param boolean $xhtml HTML 4.01 will be used for all of mahara, except very special cases (eg leap2a exports)
 * @return string The cleaned up text
 */
function clean_html($text, $xhtml=false) {
    require_once('htmlpurifier/HTMLPurifier.auto.php');
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.SerializerPath', get_config('dataroot') . 'htmlpurifier');
    if (empty($xhtml)) {
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
    } else {
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
    }
    $config->set('AutoFormat.Linkify', true);

    if (get_config('disableexternalresources')) {
        $config->set('URI.DisableExternalResources', true);
    }

    // Permit embedding contents from other sites
    $config->set('HTML.SafeEmbed', true);
    $config->set('HTML.SafeObject', true);
    $config->set('Output.FlashCompat', true);

    // Allow namespaced IDs
    // see http://htmlpurifier.org/docs/enduser-id.html
    $config->set('Attr.EnableID', true);
    $config->set('Attr.IDPrefix', 'user_');

    $customfilters = array();
    if (get_config('filters')) {
        foreach (unserialize(get_config('filters')) as $filter) {
            // These filters are no longer necessary and have been removed
            $builtinfilters = array('YouTube', 'TeacherTube', 'SlideShare', 'SciVee', 'GoogleVideo');

            if (!in_array($filter->file, $builtinfilters)) {
                require_once(get_config('libroot') . 'htmlpurifiercustom/' . $filter->file . '.php');
                $classname = 'HTMLPurifier_Filter_' . $filter->file;
                $customfilters[] = new $classname();
            }
        }
        $config->set('Filter.Custom', $customfilters);
    }

    // These settings help identify the configuration definition. If the 
    // definition (the $def object below) is changed (e.g. new method calls 
    // made on it), the DefinitionRev needs to be increased. See
    // http://htmlpurifier.org/live/configdoc/plain.html#HTML.DefinitionID
    $config->set('HTML.DefinitionID', 'Mahara customisations to default config');
    $config->set('HTML.DefinitionRev', 1);

    if ($def = $config->maybeGetRawHTMLDefinition()) {
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
    }
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($text);
}

/**
 * Given HTML, converts and formats it as text
 *
 * @param string $html The html to be formatted
 * @return string The formatted text
 */
function html2text($html, $fragment=true) {
    require_once('htmltotext/htmltotext.php');
    if ($fragment) {
        $html = '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>' . $html;
    }
    $h2t = new HtmltoText($html, get_config('wwwroot'));
    return $h2t->text();
}

/**
 * Given some text, locates URLs in it and converts them to HTML
 *
 * @param string $text The text to locate URLs in
 * @return string
 *
 * {@internal{Note, it's perhaps unreasonably expected that the input to this 
 * function is HTML escaped already. Especially because it's expected that 
 * there are no <a href="...">s in there. This works for now because the bbcode 
 * parser breaks things out into tokens, but this function might need reworking 
 * to be more useful in other places.}}
 */
function autolink_text($text) {
    $text = preg_replace(
        '#(^|.)(https?://\S+)#me',
        "_autolink_text_helper('$2', '$1')",
        $text
    );
    return $text;
}

/**
 * Helps autolink_text by providing the HTML to link up URLs found.
 *
 * Intelligently decides what parts of the matched URL should be linked up, to 
 * get around issues where URLs are surrounded by brackets or have trailing 
 * punctuation on them
 *
 * @param string $potentialurl     The URL to check. It should already have been run through hsc()
 * @param string $leadingcharacter The character (if any) before the URL. Used 
 *                                 to check for URLs surrounded by brackets
 */
function _autolink_text_helper($potentialurl, $leadingcharacter) {
    static $brackets = array('(' => ')', '{' => '}', '[' => ']', "'" => "'");
    $trailingcharacter = substr($potentialurl, -1);
    $startofurl = substr($potentialurl, 0, -1);

    // Attempt to intelligently handle several annoyances that happen with URL 
    // auto linking. We don't want to link up brackets if the URL is enclosed 
    // in them. We also don't want to link up punctuation after URLs
    if (in_array($leadingcharacter, array_keys($brackets)) &&
        in_array($trailingcharacter, $brackets)) {
        // The URL was surrounded by brackets
        return $leadingcharacter . '<a href="' . $startofurl . '">' . $startofurl . '</a>' . $trailingcharacter; 
    }
    else {
        foreach($brackets as $opener => $closer) {
            if ($trailingcharacter == $closer &&
                false === strpos($startofurl, $opener)) {
                // The URL ended in a bracket and didn't contain one
                // Note that we can't just use this clause without using the clause 
                // about URLs surrounded by brackets, because otherwise we won't catch 
                // URLs with balanced brackets in them like http://url/?(foo)&bar=1
                return $leadingcharacter . '<a href="' . $startofurl . '">' . $startofurl . '</a>' . $trailingcharacter; 
            }
        }

        // Check for trailing punctuation
        if (in_array($trailingcharacter, array('.', ',', '!', '?'))) {
            return $leadingcharacter . '<a href="' . $startofurl . '">' . $startofurl . '</a>' . $trailingcharacter; 
        }
        else {
            return $leadingcharacter . '<a href="' . $potentialurl . '">' . $potentialurl . '</a>';
        }
    }

    // Execution should never get here
    return $potentialurl;
}

/**
 * Callback for StringParser_BBCode to handle [url] and [link] bbcode
 */
function bbcode_url($action, $attributes, $content, $params, $node_object) {
    if (!isset ($attributes['default'])) {
        $url = $content;
        $text = hsc($content);
    }
    else {
        $url = $attributes['default'];
        $text = $content;
    }
    if ($action == 'validate') {
        $valid_protos = array('http://', 'https://', 'ftp://');
        foreach ($valid_protos as $proto) {
            if (substr($url, 0, strlen($proto)) == $proto) {
                return true;
            }
        }
        return false;
    }
    return '<a href="' . hsc($url) . '">' . $text . '</a>';
}

/**
 * Callback for StringParser_BBCode to handle [img] bbcode
 */
function bbcode_img($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        $valid_protos = array('http://', 'https://');
        foreach ($valid_protos as $proto) {
            if (substr($content, 0, strlen($proto)) == $proto) {
                return true;
            }
        }
        return false;
    }
    return '<img src="' . hsc($content) . '" alt="">';
}

/**
 * Returns a message that can be used as help text for BBCode
 *
 * @return string
 */
function bbcode_format_post_message() {
    return get_string('formatpostbbcode', 'mahara', '<a href="" onclick="contextualHelp(\'\',\'\',\'core\',\'site\',null,\'bbcode\',this); return false;">', '</a>');
}


/**
 * Displays purified html on a page with an explanatory message.
 * 
 * @param string $html     The purified html.
 * @param string $filename The filename to serve the file as
 * @param array $params    Parameters previously passed to serve_file
 */
function display_cleaned_html($html, $filename, $params) {
    $smarty = smarty_core();
    $smarty->assign('params', $params);
    if ($params['owner']) {
        $smarty->assign('htmlremovedmessage', get_string('htmlremovedmessage', 'artefact.file', hsc($filename), get_config('wwwroot') . 'user/view.php?id=' . (int) $params['owner'], hsc(display_name($params['owner']))));
    } else {
        $smarty->assign('htmlremovedmessage', get_string('htmlremovedmessagenoowner', 'artefact.file', hsc($filename)));
    }
    $smarty->assign('content', $html);
    $smarty->display('cleanedhtml.tpl');
    exit;
}

/**
 * Takes a string and a length, and ensures that the string is no longer than
 * this length, by putting '...' in it somewhere.
 *
 * It also strips all tags except <br> and <p>.
 *
 * This version is appropriate for use on HTML. See str_shorten_text() for use 
 * on text strings.
 *
 * @param string $str    The string to shorten
 * @param int $maxlen    The maximum length the new string should be (default 100)
 * @param bool $truncate If true, cut the string at the end rather than in the middle (default false)
 * @param bool $newlines If false, cut off after the first newline (default true)
 * @return string
 */
function str_shorten_html($str, $maxlen=100, $truncate=false, $newlines=true) {
    if (empty($str)) {
        return $str;
    }
    if (!$newlines) {
        $nextbreak = strpos($str, '<p', 1);
        if ($nextbreak !== false) {
            $str = substr($str, 0, $nextbreak);
        }
        $nextbreak = strpos($str, '<br', 1);
        if ($nextbreak !== false) {
            $str = substr($str, 0, $nextbreak);
        }
    }
    // so newlines don't disappear but ignore the first <p>
    $str = $str[0] . str_replace('<p', "\n\n<p", substr($str, 1));
    $str = str_replace('<br', "\n<br", $str);

    $str = strip_tags($str);
    $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8'); // no things like &nbsp; only take up one character
    // take the first $length chars, then up to the first space (max length $length + $extra chars)

    if (function_exists('mb_substr')) {
        if ($truncate && mb_strlen($str, 'UTF-8') > $maxlen) {
            $str = mb_substr($str, 0, $maxlen-3, 'UTF-8') . '...';
        }
        if (mb_strlen($str, 'UTF-8') > $maxlen) {
            $str = mb_substr($str, 0, floor($maxlen / 2) - 1, 'UTF-8') . '...' . mb_substr($str, -(floor($maxlen / 2) - 2), mb_strlen($str, 'UTF-8'), 'UTF-8');
        }
    }
    else {
        if ($truncate && strlen($str) > $maxlen) {
            $str = substr($str, 0, $maxlen-3) . '...';
        }
        if (strlen($str) > $maxlen) {
            $str = substr($str, 0, floor($maxlen / 2) - 1) . '...' . substr($str, -(floor($maxlen / 2) - 2), strlen($str));
        }
    }
    $str = nl2br(hsc($str));
    // this should be ok, because the string gets checked before going into the database
    $str = str_replace('&amp;', '&', $str);
    return $str;
}

/**
 * Takes a string and a length, and ensures that the string is no longer than 
 * this length, by putting '...' in it somewhere.
 *
 * This version is appropriate for use on plain text. See str_shorten_html() 
 * for use on HTML strings.
 *
 * @param string $str    The string to shorten
 * @param int $maxlen    The maximum length the new string should be (default 100)
 * @param bool $truncate If true, cut the string at the end rather than in the middle (default false)
 * @return string
 */
function str_shorten_text($str, $maxlen=100, $truncate=false) {
    if (function_exists('mb_substr')) {
        if (mb_strlen($str, 'UTF-8') > $maxlen) {
            if ($truncate) {
                return mb_substr($str, 0, $maxlen - 3, 'UTF-8') . '...';
            }
            return mb_substr($str, 0, floor($maxlen / 2) - 1, 'UTF-8') . '...' . mb_substr($str, -(floor($maxlen / 2) - 2), mb_strlen($str, 'UTF-8'), 'UTF-8');
        }
        return $str;
    }
    if (strlen($str) > $maxlen) {
        if ($truncate) {
            return substr($str, 0, $maxlen - 3) . '...';
        }
        return substr($str, 0, floor($maxlen / 2) - 1) . '...' . substr($str, -(floor($maxlen / 2) - 2));
    }
    return $str;
}

/**
 * Builds pagination links for HTML display.
 *
 * The pagination is quite configurable, but at the same time gives a consistent 
 * look and feel to all pagination.
 *
 * This function takes one array that contains the options to configure the 
 * pagination. Required options include:
 *
 * - url: The base URL to use for all links
 * - count: The total number of results to paginate for
 * - limit: How many to show per page
 * - offset: At which result to start showing results
 *
 * Optional options include:
 *
 * - id: The ID of the div enclosing the pagination
 * - class: The class of the div enclosing the pagination
 * - offsetname: The name of the offset parameter in the url
 * - firsttext: The text to use for the 'first page' link
 * - previoustext: The text to use for the 'previous page' link
 * - nexttext: The text to use for the 'next page' link
 * - lasttext: The text to use for the 'last page' link
 * - numbersincludefirstlast: Whether the page numbering should include links 
 *   for the first and last pages
 * - resultcounttextsingular: The text to use for 'result'
 * - resultcounttextplural: The text to use for 'results'
 *
 * Optional options to support javascript pagination include:
 *
 * - datatable: The ID of the table whose TBODY's rows will be replaced with the 
 *   resulting rows
 * - jsonscript: The script to make a json request to in order to retrieve 
 *   both the new rows and the new pagination. See js/artefactchooser.json.php 
 *   for an example. Note that the paginator javascript library is NOT 
 *   automatically included just because you call this function, so make sure 
 *   that your smarty() call hooks it in.
 *
 * @param array $params Options for the pagination
 */
function build_pagination($params) {
    // Bail if the required attributes are not present
    $required = array('url', 'count', 'limit', 'offset');
    foreach ($required as $option) {
        if (!isset($params[$option])) {
            throw new ParameterException('You must supply option "' . $option . '" to build_pagination');
        }
    }

    // Work out default values for parameters
    if (!isset($params['id'])) {
        $params['id'] = substr(md5(microtime()), 0, 4);
    }

    $params['offsetname'] = (isset($params['offsetname'])) ? $params['offsetname'] : 'offset';
    if (isset($params['forceoffset']) && !is_null($params['forceoffset'])) {
        $params['offset'] = (int) $params['forceoffset'];
    }
    else if (!isset($params['offset'])) {
        $params['offset'] = param_integer($params['offsetname'], 0);
    }

    // Correct for odd offsets
    $params['offset'] -= $params['offset'] % $params['limit'];

    $params['firsttext'] = (isset($params['firsttext'])) ? $params['firsttext'] : get_string('first');
    $params['previoustext'] = (isset($params['previoustext'])) ? $params['previoustext'] : get_string('previous');
    $params['nexttext']  = (isset($params['nexttext']))  ? $params['nexttext'] : get_string('next');
    $params['lasttext']  = (isset($params['lasttext']))  ? $params['lasttext'] : get_string('last');
    $params['resultcounttextsingular'] = (isset($params['resultcounttextsingular'])) ? $params['resultcounttextsingular'] : get_string('result');
    $params['resultcounttextplural'] = (isset($params['resultcounttextplural'])) ? $params['resultcounttextplural'] : get_string('results');

    if (!isset($params['numbersincludefirstlast'])) {
        $params['numbersincludefirstlast'] = true;
    }
    if (!isset($params['numbersincludeprevnext'])) {
        $params['numbersincludeprevnext'] = true;
    }

    if (!isset($params['extradata'])) {
        $params['extradata'] = null;
    }

    // Begin building the output
    $output = '<div id="' . $params['id'] . '" class="pagination';
    if (isset($params['class'])) {
        $output .= ' ' . hsc($params['class']);
    }
    $output .= '">';

    if ($params['limit'] <= $params['count']) {
        $pages = ceil($params['count'] / $params['limit']);
        $page = $params['offset'] / $params['limit'];

        $last = $pages - 1;
        if (!empty($params['lastpage'])) {
            $page = $last;
        }
        $next = min($last, $page + 1);
        $prev = max(0, $page - 1);

        // Build a list of what pagenumbers will be put between the previous/next links
        $pagenumbers = array();
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = 0;
        }
        if ($params['numbersincludeprevnext']) {
            $pagenumbers[] = $prev;
        }
        $pagenumbers[] = $page;
        if ($params['numbersincludeprevnext']) {
            $pagenumbers[] = $next;
        }
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = $last;
        }
        $pagenumbers = array_unique($pagenumbers);

        // Build the first/previous links
        $isfirst = $page == 0;
        $output .= build_pagination_pagelink('first', $params['url'], 0, '&laquo; ' . $params['firsttext'], get_string('firstpage'), $isfirst, $params['offsetname']);
        $output .= build_pagination_pagelink('prev', $params['url'], $params['limit'] * $prev, 
            '&larr; ' . $params['previoustext'], get_string('prevpage'), $isfirst, $params['offsetname']);

        // Build the pagenumbers in the middle
        foreach ($pagenumbers as $k => $i) {
            if ($k != 0 && $prevpagenum < $i - 1) {
                $output .= '…';
            }
            if ($i == $page) {
                $output .= '<span class="selected">' . ($i + 1) . '</span>';
            }
            else {
                $output .= build_pagination_pagelink('', $params['url'],
                    $params['limit'] * $i, $i + 1, '', false, $params['offsetname']);
            }
            $prevpagenum = $i;
        }

        // Build the next/last links
        $islast = $page == $last;
        $output .= build_pagination_pagelink('next', $params['url'], $params['limit'] * $next,
            $params['nexttext'] . ' &rarr;', get_string('nextpage'), $islast, $params['offsetname']);
        $output .= build_pagination_pagelink('last', $params['url'], $params['limit'] * $last,
            $params['lasttext'] . ' &raquo;', get_string('lastpage'), $islast, $params['offsetname']);
    }

    // Work out what javascript we need for the paginator
    $js = '';
    if (isset($params['jsonscript']) && isset($params['datatable'])) {
        $paginator_js = hsc(get_config('wwwroot') . 'js/paginator.js');
        $id           = json_encode($params['id']);
        $datatable    = json_encode($params['datatable']);
        $jsonscript   = json_encode($params['jsonscript']);
        $extradata    = json_encode($params['extradata']);
        $js .= "new Paginator($id, $datatable, $jsonscript, $extradata);";
    }

    // Output the count of results
    $resultsstr = ($params['count'] == 1) ? $params['resultcounttextsingular'] : $params['resultcounttextplural'];
    $output .= '<div class="results">' . $params['count'] . ' ' . $resultsstr . '</div>';

    // Close the container div
    $output .= '</div>';

    return array('html' => $output, 'javascript' => $js);

}

/**
 * Used by build_pagination to build individual links. Shouldn't be used 
 * elsewhere.
 */
function build_pagination_pagelink($class, $url, $offset, $text, $title, $disabled=false, $offsetname='offset') {
    $return = '<span class="pagination';
    $return .= ($class) ? " $class" : '';

    $url = (false === strpos($url, '?')) ? $url . '?' : $url . '&amp;';
    $url .= "$offsetname=$offset";

    if ($disabled) {
        $return .= ' disabled">' . $text . '</span>';
    }
    else {
        $return .= '">'
            . '<a href="' . $url . '" title="' . $title
            . '">' . $text . '</a></span>';
    }

    return $return;
}

function mahara_http_request($config) {
    $ch = curl_init();

    // standard curl_setopt stuff; configs passed to the function can override these
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

    curl_setopt_array($ch, $config);

    if($proxy_address = get_config('proxyaddress')) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy_address);

        if($proxy_authmodel = get_config('proxyauthmodel') && $proxy_credentials = get_config('proxyauthcredentials')) {
            // todo: actually do something with $proxy_authmodel
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_credentials);
        }
    }

    if (strpos($config[CURLOPT_URL], 'https://') === 0) {
        if ($cainfo = get_config('cacertinfo')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
        }
    }

    $result = new StdClass();
    $result->data = curl_exec($ch);
    $result->info = curl_getinfo($ch);
    $result->error = curl_error($ch);
    $result->errno = curl_errno($ch);

    if ($result->errno) {
        log_warn('Curl error: ' . $result->errno . ': ' . $result->error);
    }

    curl_close($ch);

    return $result;
}
