<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

use Mahara\Dwoo_Mahara as Dwoo_Mahara;
defined('INTERNAL') || die();


function smarty_core() {
    require_once(__DIR__ . '/dwoo/vendor/autoload.php');
    require_once(__DIR__ . '/dwoo/mahara/Dwoo_Mahara.php');

    return new Dwoo_Mahara();
}



/**
 * Function to set an optional page icon. Mahara uses fontawesome for icons by default,
 * (http://fortawesome.github.io/Font-Awesome/icons/) but this can be overridden at the theme
 * level by supplying a different icon font + css.
 *
 * @param Smarty | an initialized smarty object
 * @param String | the name of the icon to include (eg "icon-university")
 */
function setpageicon($smarty, $icon) {
    $smarty->assign('pageicon', 'icon ' . $icon);
}



/**
 * Helper function (called by smarty()) to determine what stylesheets to include
 * on the page (based on constants, global variables, and $extraconfig)
 *
 * @param $stylesheets Stylesheets we already know we're going to need
 * @param $extraconfig Extra configuration passed to smarty()
 * @return array
 */

function get_stylesheets_for_current_page($stylesheets, $extraconfig) {

    global $USER, $SESSION, $THEME, $HEADDATA, $langselectform;

    // stylesheet set up - if we're in a plugin also get its stylesheet
    $allstylesheets = $THEME->get_url('style/style.css', true);

    // determine if we want to include the parent css
    if (isset($THEME->overrideparentcss) && $THEME->overrideparentcss && $THEME->parent) {
        unset($allstylesheets[$THEME->parent]);
    }

    $stylesheets = array_merge($stylesheets, array_reverse(array_values($allstylesheets)));

    if (defined('SECTION_PLUGINTYPE') && defined('SECTION_PLUGINNAME') && SECTION_PLUGINTYPE != 'core') {
        if ($pluginsheets = $THEME->get_url('style/style.css', true, SECTION_PLUGINTYPE . '/' . SECTION_PLUGINNAME)) {
            $stylesheets = array_merge($stylesheets, array_reverse($pluginsheets));
        }
    }

    if ($adminsection = in_admin_section()) {
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

    // Only add additional stylesheets when configurable theme is set.
    if ($THEME->basename == 'custom') {
        $sheets = $THEME->additional_stylesheets();
        $stylesheets = array_merge($stylesheets, $sheets);
    }

    // Give the skin a chance to affect the page
    if (!empty($extraconfig['skin'])) {
        require_once(get_config('docroot').'/lib/skin.php');
        $skinobj = new Skin($extraconfig['skin']['skinid']);
        $viewid = isset($extraconfig['skin']['viewid']) ? $extraconfig['skin']['viewid'] : null;
        $stylesheets = array_merge($stylesheets, $skinobj->get_stylesheets($viewid));
    }

    $langdirection = get_string('thisdirection', 'langconfig');

    // Include rtl.css for right-to-left langs
    if ($langdirection == 'rtl') {
        $smarty->assign('LANGDIRECTION', 'rtl');
        if ($rtlsheets = $THEME->get_url('style/rtl.css', true)) {
            $stylesheets = array_merge($stylesheets, array_reverse($rtlsheets));
        }
    }

    $stylesheets = append_version_number($stylesheets);

    return $stylesheets;
}

/**
* True if we are not in admin, institution or admin section
*/
function user_personal_section() {
    $usersection = !defined('ADMIN') && !defined('STAFF') && !defined('INSTITUTIONALADMIN') &&
        !defined('INSTITUTIONALSTAFF') && !defined('GROUP') && !defined('CREATEGROUP');

    return $usersection ? 1 : 0;
}

/**
 * This function creates a Smarty object and sets it up for use within our
 * podclass app, setting up some variables.
 *
 * WARNING: If you are using pieforms, set them up BEFORE calling this function.
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
 * @return Dwoo_Mahara
 */



function smarty($javascript = array(), $headers = array(), $pagestrings = array(), $extraconfig = array()) {
    global $USER, $SESSION, $THEME, $HEADDATA, $langselectform, $CFG, $viewid;

    if (!is_array($headers)) {
        $headers = array();
    }
    if (!is_array($pagestrings)) {
        $pagestrings = array();
    }
    if (!is_array($extraconfig)) {
        $extraconfig = array();
    }

    $sideblocks = array();
    // Some things like die_info() will try and create a smarty() call when we are already in one, which causes
    // language_select_form() to throw headdata error as it is called twice.
    if (!isset($langselectform)) {
        $langselectform = language_select_form();
    }
    $sideblock_menu = array();
    if (get_config('installed')) {
        // Fetch all the core side blocks now to avoid any 'set headdata' before smarty_core() problems
        $authgenerateloginform = auth_generate_login_form();
        $isloginblockvisible = !$USER->is_logged_in() && !get_config('siteclosedforupgrade') && get_config('showloginsideblock');
        $loginsideblock = array(
            'name'   => 'login',
            'weight' => -10,
            'id'     => 'sb-loginbox',
            'data'   => array('loginform' => $authgenerateloginform),
            'visible' => $isloginblockvisible,
            'template' => 'sideblocks/login.tpl',
            'smarty' => array('SHOWLOGINBLOCK' => $isloginblockvisible),
        );
        sideblock_template($loginsideblock, $sideblock_menu);
        sideblock_template(site_menu(), $sideblock_menu);
        sideblock_template(tags_sideblock(), $sideblock_menu);
        sideblock_template(selfsearch_sideblock(), $sideblock_menu);
        sideblock_template(profile_sideblock(), $sideblock_menu);
        sideblock_template(onlineusers_sideblock(), $sideblock_menu);
        sideblock_template(progressbar_sideblock(), $sideblock_menu);
        sideblock_template(ssopeer_sideblock(), $sideblock_menu);
        sideblock_template(quota_sideblock(), $sideblock_menu);
        if (isset($extraconfig['sideblocks']) && is_array($extraconfig['sideblocks'])) {
            foreach ($extraconfig['sideblocks'] as $sideblock) {
                sideblock_template($sideblock, $sideblock_menu);
            }
        }
        // local_sideblocks_update allows sites to customise the sideblocks by munging the $sideblock_menu array.
        if (function_exists('local_sideblocks_update')) {
            local_sideblocks_update($sideblock_menu);
        }
        // Remove those that are not visible before displaying
        foreach ($sideblock_menu as $sbk => $sbv) {
            if (empty($sbv['visible'])) {
                unset($sideblock_menu[$sbk]);
            }
        }
        usort($sideblock_menu, "sort_menu_by_weight");
    }

    $smarty = smarty_core();

    $wwwroot = get_config('wwwroot');
    // NOTE: not using jswwwroot - it seems to wreck image paths if you
    // drag them around the wysiwyg editor
    $jswwwroot = json_encode($wwwroot);

    // Workaround for $cfg->cleanurlusersubdomains.
    // When cleanurlusersubdomains is on, ajax requests might come from somewhere other than
    // the wwwroot.  To avoid cross-domain requests, set a js variable when this page is on a
    // different subdomain, and let the ajax wrapper function sendjsonrequest rewrite its url
    // if necessary.
    if (get_config('cleanurls') && get_config('cleanurlusersubdomains')) {
        if ($requesthost = get_requested_host_name()) {
            $wwwrootparts = parse_url($wwwroot);
            if ($wwwrootparts['host'] != $requesthost) {
                $fakewwwroot = $wwwrootparts['scheme'] . '://' . $requesthost . '/';
                $headers[] = '<script>var fakewwwroot = ' . json_encode($fakewwwroot) . ';</script>';
            }
        }
    }

    $theme_list = array();
    $adminsection = in_admin_section();

    if (function_exists('pieform_get_headdata')) {
        $headers = array_merge($headers, pieform_get_headdata());
        if (!defined('PIEFORM_GOT_HEADDATA')) {
          define('PIEFORM_GOT_HEADDATA', 1);
        }
    }

    // Define the stylesheets array early so that javascript modules can add extras
    $stylesheets = array();

    // Insert the appropriate javascript tags
    $javascript_array = array();
    $jsroot = $wwwroot . 'js/';

    $langdirection = get_string('thisdirection', 'langconfig');

    // Make jQuery accessible with $j
    $javascript_array[] = $jsroot . 'jquery/jquery.js';
    $headers[] = '<script>$j=jQuery;</script>';

    // If necessary, load MathJax configuration
    if (get_config('mathjax')) {
        $headers[] = '<script>'.get_config('mathjaxconfig').'</script>';
    }

    // TinyMCE must be included first for some reason we're not sure about
    //
    // Note: we do not display tinyMCE for mobile devices
    // as it doesn't work on some of them and can
    // disable the editing of a textarea field
    if (is_html_editor_enabled()) {
        $checkarray = array(&$javascript, &$headers);
        $found_tinymce = false;
        $tinymceviewid = 'null';
        if ($inpersonalarea = user_personal_section()) {
            if (defined('SECTION_PAGE') && (SECTION_PAGE == 'view' || SECTION_PAGE == 'blocks' || SECTION_PAGE == 'editlayout')) {
                if (isset($viewid) && $viewid > 0) {
                    $tinymceviewid = $viewid;
                }
            }
        }

        foreach ($checkarray as &$check) {
            if (($key = array_search('tinymce', $check)) !== false || ($key = array_search('tinytinymce', $check)) !== false) {
                if (!$found_tinymce) {
                    $found_tinymce = $check[$key];
                    $javascript_array[] = $wwwroot . 'artefact/file/js/filebrowser.js';
                    $javascript_array[] = $jsroot . 'switchbox.js';
                    $javascript_array[] = $jsroot . 'tinymce/tinymce.js';
                    $stylesheets = array_merge($stylesheets, array_reverse(array_values($THEME->get_url('style/tinymceskin.css', true))));
                    $content_css = json_encode($THEME->get_url('style/tinymce.css'));
                    $language = current_language();
                    $language = substr($language, 0, ((substr_count($language, '_') > 0) ? 5 : 2));
                    if ($language != 'en' && !file_exists(get_config('docroot') . 'js/tinymce/langs/' . $language . '.js')) {
                        // In case the language file exists as a string with both lower and upper case, eg fr_FR we test for this
                        $language = substr($language, 0, 2) . '_' . strtoupper(substr($language, 0, 2));
                        if (!file_exists(get_config('docroot') . 'js/tinymce/langs/' . $language . '.js')) {
                            // In case we fail to find a language of 5 chars, eg pt_BR (Portugese, Brazil) we try the 'parent' pt (Portugese)
                            $language = substr($language, 0, 2);
                            if ($language != 'en' && !file_exists(get_config('docroot') . 'js/tinymce/langs/' . $language . '.js')) {
                                $language = 'en';
                            }
                        }
                    }
                    $extrasetup = isset($extraconfig['tinymcesetup']) ? $extraconfig['tinymcesetup'] : '';
                    $extramceconfig = isset($extraconfig['tinymceconfig']) ? $extraconfig['tinymceconfig'] : '';

                    // Check whether to make the spellchecker available
                    if (get_config('tinymcespellcheckerengine')) {
                        $spellchecker = ',spellchecker';
                        $spellchecker_toolbar = '| spellchecker';
                        $spellchecker_config = "gecko_spellcheck : false, spellchecker_rpc_url : \"{$jsroot}tinymce/plugins/spellchecker/spellchecker.php\",";
                    }
                    else {
                        $spellchecker = $spellchecker_toolbar = '';
                        $spellchecker_config = 'gecko_spellcheck : true,';
                    }
                    $mathslate = (get_config('mathjax')) ? 'mathslate' : '';
                    $mathslateplugin = !empty($mathslate) ? ',' . $mathslate : '';
                    $toolbar = array(
                        null,
                        '"toolbar_toggle | formatselect | bold italic | bullist numlist | link unlink | imagebrowser | undo redo"',
                        '"underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | outdent indent | forecolor backcolor | ltr rtl | fullscreen"',
                        '"fontselect | fontsizeselect | emoticons nonbreaking charmap ' . $mathslate . ' ' . $spellchecker_toolbar . ' | table | removeformat pastetext | anchor | code"',
                    );

                    // For right-to-left langs, reverse button order & align controls right.
                    $tinymce_langdir = $langdirection == 'rtl' ? 'rtl' : 'ltr';
                    $toolbar_align = 'left';

                    // Language strings required for TinyMCE
                    $pagestrings['mahara'] = isset($pagestrings['mahara']) ? $pagestrings['mahara'] : array();
                    $pagestrings['mahara'][] = 'attachedimage';

                    $tinymceinitbehatsetup = '';
                    $tinymcebehatsetup = '';
                    if (defined('BEHAT_TEST')) {
                        $tinymceinitbehatsetup = 'window.isEditorInitializing = false;';
                        $tinymcebehatsetup = <<<EOF
        ed.on('PreInit', function(ed) {
            window.isEditorInitializing = true;
        });
EOF;
                    }

                    if ($check[$key] == 'tinymce') {
                        $tinymceconfig = <<<EOF
    theme: "modern",
    plugins: "tooltoggle,textcolor,visualblocks,wordcount,link,lists,imagebrowser,table,emoticons{$spellchecker},paste,code,fullscreen,directionality,searchreplace,nonbreaking,charmap{$mathslateplugin},anchor",
    skin: 'light',
    toolbar1: {$toolbar[1]},
    toolbar2: {$toolbar[2]},
    toolbar3: {$toolbar[3]},
    menubar: false,
    fix_list_elements: true,
    image_advtab: true,
    table_style_by_css: true,
    {$spellchecker_config}
EOF;
                    }
                    else {
                        $tinymceconfig = <<<EOF
    selector: "textarea.tinywysiwyg",
    theme: "modern",
    skin: 'light',
    plugins: "fullscreen,autoresize",
    toolbar: {$toolbar[0]},
EOF;
                    }
$samepage = get_string('samepage', 'mahara');
                    $headers[] = <<<EOF
<script>
tinyMCE.init({
    {$tinymceconfig}
    schema: 'html4',
    extended_valid_elements:
        "object[width|height|classid|codebase]"
        + ",param[name|value]"
        + ",embed[src|type|width|height|flashvars|wmode]"
        + ",script[src,type,language]"
        + ",ul[id|type|compact]"
        + ",iframe[src|width|height|name|scrolling|frameborder|allowfullscreen|webkitallowfullscreen|mozallowfullscreen|longdesc|marginheight|marginwidth|align|title|class|type]"
        + ",a[id|class|title|href|name|target]"
        + ",button[id|class|title]"
    ,urlconverter_callback : "custom_urlconvert",
    language: '{$language}',
    directionality: "{$tinymce_langdir}",
    content_css : {$content_css},
    font_formats: 'Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Open Sans=Open Sans;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats;',
    remove_script_host: false,
    relative_urls: false,
    target_list: [
        {title: 'None', value: ''},
        {title: "{$samepage}", value: '_self'}, // This one is not translated in tinymce lang files
        {title: 'New window', value: '_blank'}
    ],
    link_list: function(success) {
        // Only show the list of links in the normal user section
        if ({$inpersonalarea}) {
            var params = {
                'viewid': {$tinymceviewid}
            }
            sendjsonrequest(config['wwwroot'] + 'json/tinymceviewlist.json.php',  params, 'POST', function(data) {
                if (data.count > 0) {
                    success(JSON.parse(data.data));
                }
                else {
                    success(''); // stop showing list with only option being 'none'
                }
            });
        }
        else {
            success(''); // stop showing list with only option being 'none'
        }
    },
    'branding': false,
    cache_suffix: '?v={$CFG->cacheversion}',
    {$extramceconfig}
    setup: function(ed) {
        {$tinymcebehatsetup}
        ed.on('init', function(ed) {
        {$tinymceinitbehatsetup}
            if (typeof(editor_to_focus) == 'string' && ed.editorId == editor_to_focus) {
                ed.trigger("focus");
            }
        });
        ed.on('keyup change', function (e) {
            checkTextareaMaxLength(ed.settings.id);
        });
        ed.on('LoadContent', function(e) {
            // Hide all the 2nd/3rd row menu buttons
            jQuery('.mce-toolbar.mce-first').siblings().addClass('d-none');
            // The tinymce fullscreen mode does not work properly in a transformed container div
            // such as div.vertcentre
            // and IE doesn't like a preset z-index
            // This work-around will remove/add classes: .vertcenter .configure .blockinstane
            // of the configure block div
            // when toggling fullscreen
            jQuery('div[aria-label="Fullscreen"]').on('click', function(e) {
                jQuery('div#configureblock').toggleClass('vertcentre');
                jQuery('div#configureblock').toggleClass('blockinstance');
                jQuery('div#configureblock').toggleClass('configure');
            });
        });
        {$extrasetup}
    }
});

function imageBrowserConfigSuccess(form, data) {
    // handle updates to file browser
    // final form submission handled by tinymce plugin
    if (data.formelementsuccess) {
        eval(data.formelementsuccess + '(form, data)');
        return;
    }
}

function imageBrowserConfigError(form, data) {
    if (data.formelementerror) {
        eval(data.formelementerror + '(form, data)');
        return;
    }
}

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

            // If any page adds jquery explicitly, remove it from the list
            if (($key = array_search('jquery', $check)) !== false) {
                unset($check[$key]);
            }
        }
    }
    else {
        if (($key = array_search('tinymce', $javascript)) !== false || ($key = array_search('tinytinymce', $javascript)) !== false) {
            unset($javascript[$key]);
        }
        if (($key = array_search('tinymce', $headers)) !== false || ($key = array_search('tinytinymce', $headers)) !== false) {
            unset($headers[$key]);
        }
    }
    $javascript_array[] = $jsroot . 'keyboardNavigation.js';

    //If necessary, load MathJax path
    if (get_config('mathjax')) {
        $javascript_array[] = get_config('mathjaxpath');
    }

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
        else if (stripos($jsfile, 'http://') === false && stripos($jsfile, 'https://') === false) {
            // A local .js file with a fully specified path
            $javascript_array[] = $wwwroot . $jsfile;
            // If $jsfile is from a plugin or plugin's block, i.e.:
            // - plugintype/pluginname/js/foo.js
            // - plugintype/pluginname/blocktype/pluginname/js/foo.js
            // Then get js strings from static function jsstrings in:
            // - plugintype/pluginname/lib.php, or
            // - plugintype/pluginname/blocktype/pluginname/lib.php
            $bits = explode('/', $jsfile);
            $pluginname = false;
            $plugintype = false;
            $jsfilename = false;
            if (count($bits) == 4 && $bits[2] == 'js' && in_array($bits[0], plugin_types())) {
                $plugintype = $bits[0];
                $pluginname = $bits[1];
                $jsfilename = $bits[3];
            }
            if (count($bits) == 6 && $bits[0] == 'artefact' && $bits[2] == 'blocktype' && $bits[4] == 'js') {
                $plugintype = 'blocktype';
                $pluginname = $bits[3];
                $jsfilename = $bits[5];
            }
            if ($pluginname) {
                safe_require($plugintype, $pluginname);
                $pluginclass = generate_class_name($plugintype, $pluginname);
                $name = substr($jsfilename, 0, strpos($jsfilename, '.js'));
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
                            $strings[$tag . '.help'] = get_help_icon($plugintype, $pluginname, null, null,
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
    $javascript_array[] = $jsroot . 'formchangechecker.js';
    $javascript_array[] = $jsroot . 'textareamaxlengthchecker.js';

    // Load some event handler functions for checking if all AJAX requests have completed
    // when running behat tests
    if (defined('BEHAT_TEST')) {
        $javascript_array[] = get_config('wwwroot') . 'testing/frameworks/behat/page_status.js';
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

    $stringjs = '<script>';
    $stringjs .= 'var strings = ' . json_encode($strings) . ';';
    $stringjs .= "\nfunction plural(n) { return " . get_raw_string('pluralrule', 'langconfig') . "; }\n";
    $stringjs .= '</script>';


    // Allow us to set the HTML lang attribute
    $smarty->assign('LANGUAGE', substr(current_language(), 0, 2));

    $smarty->assign('STRINGJS', $stringjs);

    $stylesheets = get_stylesheets_for_current_page($stylesheets, $extraconfig);

    // Disable CSS transforms, transitions, and animations when running behat tests
    if (defined('BEHAT_TEST')) {
        $stylesheets[] = get_config('wwwroot') . 'testing/frameworks/behat/no_transitions.css';
    }
    $smarty->assign('STYLESHEETLIST', $stylesheets);
    if (!empty($theme_list)) {
        // this gets assigned in smarty_core, but do it again here if it's changed locally
        $smarty->assign('THEMELIST', json_encode(array_merge((array)json_decode($smarty->get_template_vars('THEMELIST')),  $theme_list)));
    }

    $dropdownmenu = get_config('dropdownmenu');
    // disable drop-downs if overridden at institution level
    $sitethemeprefs = get_config('sitethemeprefs');
    $institutions = $USER->institutions;
    if (!empty($institutions)) {
        if (count($institutions) == 1) {
            $i = reset($institutions);
            if ($i->theme == $THEME->basename && $USER->institutiontheme->institutionname == $i->institution) {
                $dropdownmenu = $i->dropdownmenu;
            }
        }
        else {
            foreach ($institutions as $i) {
                if (!empty($sitethemeprefs)) {
                    if (!empty($USER->accountprefs['theme']) && $USER->accountprefs['theme'] == $THEME->basename . '/' . $i->institution) {
                        $dropdownmenu = $i->dropdownmenu;
                        break;
                    }
                }
                else {
                    if ((!empty($USER->accountprefs['theme']) && $USER->accountprefs['theme'] == $THEME->basename . '/' . $i->institution)
                        || (empty($USER->accountprefs) && $i->theme == $THEME->basename && $USER->institutiontheme->institutionname == $i->institution)) {
                        $dropdownmenu = $i->dropdownmenu;
                        break;
                    }
                }
            }
        }
    }

    // and/or disable drop-downs if a handheld device detected
    $dropdownmenu = $SESSION->get('handheld_device') ? false : $dropdownmenu && get_config('dropdownmenuenabled');

    if ($dropdownmenu) {
        $smarty->assign('DROPDOWNMENU', $dropdownmenu);
        $javascript_array[] = $jsroot . 'dropdown-nav.js';
    }

    $smarty->assign('MOBILE', $SESSION->get('mobile'));
    $smarty->assign('HANDHELD_DEVICE', $SESSION->get('handheld_device'));
    if (defined('FILEBROWSERS') ||
        (defined('SECTION_PAGE') && SECTION_PAGE == 'blocks')) {
        // Need to add the headers for select2 here so filebrowser has correct language
        require_once(get_config('libroot') . 'form/elements/autocomplete.php');
        $select2lang = pieform_element_autocomplete_language();
        $select2headdata = pieform_element_autocomplete_get_headdata();
        $headers = array_merge($headers, $select2headdata);
        $smarty->assign('select2_language', $select2lang);
    }
    $maxuploadsize = get_max_upload_size(false);
    $smarty->assign('maxuploadsize', $maxuploadsize);
    $smarty->assign('maxuploadsizepretty', display_size($maxuploadsize));

    $sitename = get_config('sitename');
    if (!$sitename) {
       $sitename = 'Mahara';
    }
    $smarty->assign('sitename', $sitename);

    $sitelogocustom = false;
    if (get_config('installed')) {
        $sitelogocustom = (int) (get_field('institution', 'logo', 'name', 'mahara') || $THEME->headerlogo);
    }
    $smarty->assign('sitelogocustom', $sitelogocustom);
    $sitelogo = $THEME->header_logo();
    $sitelogo = append_version_number($sitelogo);
    $sitelogocustomsmall = $THEME->header_logo_small_custom();
    $sitelogocustomsmall = ($sitelogocustomsmall ? append_version_number($sitelogocustomsmall) : null);
    $smarty->assign('sitelogo', $sitelogo);
    $smarty->assign('sitelogosmall', $THEME->header_logo_small());
    $smarty->assign('sitelogocustomsmall', $sitelogocustomsmall);
    $smarty->assign('sitelogo4facebook', $THEME->facebook_logo());
    $smarty->assign('sitedescription4facebook', get_string('facebookdescription', 'mahara'));

    if (defined('TITLE')) {
        $smarty->assign('PAGETITLE', TITLE . ' - ' . $sitename);
    }
    else {
        $smarty->assign('PAGETITLE', $sitename);
    }
    if (defined('PAGEHEADING')) {
        $smarty->assign('PAGEHEADING', PAGEHEADING);
    }
    else {
        if (defined('TITLE')) {
            $smarty->assign('PAGEHEADING', TITLE);
        }
    }

    if (defined('SUBSECTIONHEADING')) {
        $smarty->assign('SUBSECTIONHEADING', SUBSECTIONHEADING);
    }

    $smarty->assign('PRODUCTIONMODE', get_config('productionmode'));
    if (defined('SITEOUTOFSYNC')) {
        $smarty->assign('SITEOUTOFSYNC', SITEOUTOFSYNC);
    }
    if (function_exists('local_header_top_content')) {
        $sitetop = (isset($sitetop) ? $sitetop : '') . local_header_top_content();
    }
    if (isset($sitetop)) {
        $smarty->assign('SITETOP', $sitetop);
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
    if (defined('STAFF')) {
        $smarty->assign('STAFF', true);
    }
    if (defined('INSTITUTIONALSTAFF')) {
        $smarty->assign('INSTITUTIONALSTAFF', true);
    }

    $smarty->assign('LOGGEDIN', $USER->is_logged_in());
    $smarty->assign('loggedout', !$USER->is_logged_in());
    $publicsearchallowed = false;
    $searchplugin = get_config('searchplugin');
    if ($searchplugin) {
        safe_require('search', $searchplugin);
        $publicsearchallowed = (call_static_method(generate_class_name('search', $searchplugin), 'publicform_allowed') && get_config('publicsearchallowed'));
    }
    $smarty->assign('publicsearchallowed', $publicsearchallowed);
    if ($USER->is_logged_in()) {
        global $SELECTEDSUBNAV; // It's evil, but rightnav & mainnav stuff are now in different templates.
        $smarty->assign('MAINNAV', main_nav());
        $is_admin = $USER->get('admin') || $USER->is_institutional_admin() || $USER->get('staff') || $USER->is_institutional_staff();
        if ($is_admin) {
            $smarty->assign('MAINNAVADMIN', main_nav('adminnav'));
        }
        $mainnavsubnav = $SELECTEDSUBNAV;
        $smarty->assign('RIGHTNAV', right_nav());
        $smarty->assign('MESSAGEBOX', message_nav());
        if (!$mainnavsubnav && $dropdownmenu) {
            // In drop-down navigation, the submenu is only usable if its parent is one of the top-level menu
            // items.  But if the submenu comes from something in right_nav (settings), it's unreachable.
            // Turning the submenu into SUBPAGENAV group-style tabs makes it usable.
            $smarty->assign('SUBPAGENAV', $SELECTEDSUBNAV);
        }
        else {
            $smarty->assign('SELECTEDSUBNAV', $SELECTEDSUBNAV);
        }
    }
    else {
        $smarty->assign('languageform', $langselectform);
    }
    $smarty->assign('FOOTERMENU', footer_menu());

    $smarty->assign('USER', $USER);
    $smarty->assign('SESSKEY', $USER->get('sesskey'));
    $smarty->assign('CC_ENABLED', get_config('cookieconsent_enabled'));
    $javascript_array = append_version_number($javascript_array);
    $smarty->assign('JAVASCRIPT', $javascript_array);
    $smarty->assign('RELEASE', get_config('release'));
    $smarty->assign('SERIES', get_config('series'));
    $smarty->assign('CACHEVERSION', get_config('cacheversion', 0));
    if (get_config('siteclosedforupgrade')) {
        $smarty->assign('SITECLOSED', 'logindisabled');
    }
    else if (get_config('siteclosedbyadmin')) {
        $smarty->assign('SITECLOSED', 'loginallowed');
    }

    if ((!isset($extraconfig['pagehelp']) || $extraconfig['pagehelp'] !== false)
        and $help = has_page_help()) {
        $smarty->assign('PAGEHELPNAME', $help[0]);
        $smarty->assign('PAGEHELPICON', $help[1]);
    }
    if (defined('GROUP')) {
        require_once('group.php');
        if ($group = group_current_group()) {
            $smarty->assign('GROUP', $group);
            if (!defined('NOGROUPMENU')) {
                $smarty->assign('SUBPAGENAV', group_get_menu_tabs());
                $smarty->assign('PAGEHEADING', $group->name);
            }
        }
    }

    if (defined('APPS')) {
       if (!defined('NOAPPSMENU')) {
            $smarty->assign('SUBPAGENAV', apps_get_menu_tabs());
        }
    }

    // ---------- sideblock smarty stuff ----------
    $sidebars = !isset($extraconfig['sidebars']) || $extraconfig['sidebars'] !== false;
    if ($sidebars && !defined('INSTALLER')) {
        foreach ($sideblock_menu as $sideblock) {
            if (!empty($sideblock['visible']) && !empty($sideblock['smarty'])) {
                foreach ($sideblock['smarty'] as $ks => $vs) {
                    $smarty->assign($ks, $vs);
                }
            }
        }

        // Place all sideblocks on the right. If this structure is munged
        // appropriately, you can put blocks on the left. In future versions of
        // Mahara, we'll make it easy to do this.
        $sidebars = $sidebars && !empty($sideblock_menu);
        $sideblocks = array('left' => array(), 'right' => $sideblock_menu);

        $smarty->assign('userauthinstance', $SESSION->get('authinstance'));
        $smarty->assign('MNETUSER', $SESSION->get('mnetuser'));
        $smarty->assign('SIDEBLOCKS', $sideblocks);
        $smarty->assign('SIDEBARS', $sidebars);

    }

    if (is_array($HEADDATA) && !empty($HEADDATA)) {
        $headers = array_merge($HEADDATA, $headers);
    }
    $smarty->assign('HEADERS', $headers);

    if ($USER->get('parentuser')) {
        $smarty->assign('USERMASQUERADING', true);
        $smarty->assign('masqueradedetails', get_string('youaremasqueradingas', 'mahara', display_name($USER)));
        $smarty->assign('becomeyoulink', hsc($wwwroot) . 'admin/users/changeuser.php?restore=1');
        $smarty->assign('becomeyouagain', get_string('becomeadminagain', 'admin', $USER->get('parentuser')->name));
    }

    // Define additional html content
    if (get_config('installed')) {
        $additionalhtmlitems = array(
            'ADDITIONALHTMLHEAD'      => get_config('additionalhtmlhead'),
            'ADDITIONALHTMLTOPOFBODY' => get_config('additionalhtmltopofbody'),
            'ADDITIONALHTMLFOOTER'    => get_config('additionalhtmlfooter')
        );
        if ($additionalhtmlitems) {
            foreach ($additionalhtmlitems as $name=>$content) {
                $smarty->assign($name, $content);
            }
        }
    }

    // If Cookie Consent is enabled, than define conent
    if (get_config('cookieconsent_enabled')) {
        require_once('cookieconsent.php');
        $smarty->assign('COOKIECONSENTCODE', get_cookieconsent_code());
    }
    // Render the session messages
    $messages = array();
    $messages = array_merge($messages, insert_messages('loginbox'));
    $messages = array_merge($messages, insert_messages('messages'));
    $smarty->assign('messages', $messages);
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
     * A user may have had the header logo overridden by an institution
     */
    public $headerlogo;

    /**
     * A user may have had the small header logo added by an institution
     */
    public $headerlogosmall;

    /**
     * Additional stylesheets to display after the basename theme's stylesheets
     */
    public $addedstylesheets;

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
            $themedata = null;
        }
        else if ($arg instanceof User) {
            $themedata = $arg->get_themedata();
        }
        else if ($arg instanceof View) {
            $themename = $arg->get('theme');
            $themedata = null;
            $userid = $arg->get('owner');
            if ($userid) {
                $user = new User();
                $user->find_by_id($userid);
                $themedata = $user->get_themedata();
                $themedata->viewbasename = $themedata->basename;
                unset($themedata->basename);
            }
        }
        else if (is_int($arg)) {
            $user = new User();
            $user->find_by_id($arg);
            $themedata = $user->get_themedata();
        }
        else {
            throw new SystemException("Argument to Theme::__construct was not a theme name, user object or user ID");
        }

        if (isset($themedata) && isset($themedata->basename)) {
            $themename = $themedata->basename;
        }

        if (empty($themename)) {
            // Theme to show to when no theme has been suggested
            if (!$themename = get_config('theme')) {
                $themename = 'raw';
            }
        }

        // check the validity of the name
        if (!$this->name_is_valid($themename)) {
            throw new SystemException("Theme name is in invalid form: '$themename'");
        }

        $this->init_theme($themename, $themedata);
    }

    /**
     * Given a theme name, check that it is valid
     */
    public static function name_is_valid($themename) {
        // preg_match returns 0 if invalid characters were found, 1 if not
        return (preg_match('/^[a-zA-Z0-9_-]+$/', $themename) == 1);
    }

    /**
     * Given a theme name, retrieves the $theme variable in the themeconfig.php file
     */
    public static function get_theme_config($themename) {
        $themeconfigfile = get_config('docroot') . 'theme/' . $themename . '/themeconfig.php';
        if (is_readable($themeconfigfile)) {
            require( get_config('docroot') . 'theme/' . $themename . '/themeconfig.php' );
            return $theme;
        }
        else {
            return false;
        }
    }

    /**
     * Given a theme name, reads in all config and sets fields on this object
     */
    private function init_theme($themename, $themedata) {

        $themeconfig = self::get_theme_config($themename);

        if (!$themeconfig) {
            // We can check if we have been given a viewbasename
            if (!empty($themedata->viewbasename)) {
                $themename = $themedata->viewbasename;
                $themeconfig = self::get_theme_config($themename);
            }
            if (!$themeconfig) {
                // We can safely assume that the default theme is installed, users
                // should never be able to remove it
                $themename = 'default';
                $themeconfig = self::get_theme_config($themename);
            }
        }

        $this->basename = $themename;

        foreach (get_object_vars($themeconfig) as $key => $value) {
            $this->$key = $value;
        }

        if (!isset($this->displayname)) {
            $this->displayname = $this->basename;
        }

        // Local theme overrides come first
        $this->templatedirs[] = get_config('docroot') . 'local/theme/templates/';

        // Then the current theme
        $this->templatedirs[] = get_config('docroot') . 'theme/' . $this->basename . '/templates/';
        $this->inheritance[]  = $this->basename;

        // 'raw' is the default parent theme
        // (If a theme has no parent, it should set $themeconfig->parent = false)
        if (!isset($themeconfig->parent)) {
            $themeconfig->parent = 'raw';
        }
        $currentthemename = $this->basename;
        while ($themeconfig->parent !== false) {
            // Now go through the theme hierarchy assigning variables from the
            // parent themes
            $parentthemename = $themeconfig->parent;
            $parentthemeconfig = self::get_theme_config($parentthemename);

            // If the parent theme is missing, short-circuit to the "raw" theme
            if (!$parentthemeconfig) {
                log_info(get_string('missingparent', 'mahara', $currentthemename, $parentthemename));
                $parentthemename = 'raw';
                $parentthemeconfig = self::get_theme_config($parentthemename);
            }
            $currentthemename = $parentthemename;
            $themeconfig = $parentthemeconfig;

            foreach (get_object_vars($themeconfig) as $key => $value) {
                if (!isset($this->$key) || !$this->$key) {
                    $this->$key = $value;
                }
            }
            $this->templatedirs[] = get_config('docroot') . 'theme/' . $currentthemename . '/templates/';
            $this->inheritance[]  = $currentthemename;
            if (!isset($themeconfig->parent)) {
                $themeconfig->parent = 'raw';
            }
        }

        if (!empty($themedata->headerlogo)) {
            $this->headerlogo = $themedata->headerlogo;
        }
        if (!empty($themedata->headerlogosmall)) {
            $this->headerlogosmall = $themedata->headerlogosmall;
        }
        if (!empty($themedata->stylesheets)) {
            $this->addedstylesheets = $themedata->stylesheets;
        }
    }

    /**
     * Get the URL of a particular theme asset (i.e. an image or CSS file). Checks first for a copy
     * in /local/theme/static, then in the current theme, then this theme's parent, grandparent, etc.
     *
     * @param string $filename Relative path of the asset, e.g. 'images/newmail.png'
     * @param boolean $all Whether to return the first found copy of the asset, or all copies of it from all themes
     * in the hierarchy.
     * @param string $plugindirectory For if it's a plugin theme asset, e.g. 'artefact/file'
     * @return string|array The URL of the first match, or all matching ones, depending on $all
     */
    public function get_url($filename, $all=false, $plugindirectory='') {
        return $this->_get_path($filename, $all, $plugindirectory, get_config('wwwroot'));
    }

    /**
     * Get the full filesystem path of a particular theme asset (i.e. an image or CSS file). Checks first for a copy
     * in /local/theme/static, then in the current theme, then this theme's parent, grandparent, etc.
     *
     * @param string $filename Relative path of the asset, e.g. 'images/newmail.png'
     * @param boolean $all Whether to return the first found copy of the asset, or all copies it from all
     * themes in the hierarchy
     * @param string $plugindirectory For if it's a plugin theme asset, e.g. 'artefact/file'
     * @return string|array The full filesystem path of the first match, or of all matches, depending on $all
     */
    public function get_path($filename, $all=false, $plugindirectory='') {
        return $this->_get_path($filename, $all, $plugindirectory, get_config('docroot'));
    }

    /**
     * Internal function to return the path or URL of a particular theme asset. Relies on the fact that the URL
     * and the filesystem path are the same, except that one is prefaced by docroot and the other by wwwroot.
     *
     * @param string $filename Relative path of the asset, e.g. 'images/newmail.png'
     * @param boolean $all Whether to return the first found copy of the asset, or all copies it from all
     * themes in the hierarchy
     * @param string $plugindirectory For if it's a plugin theme asset, e.g. 'artefact/file'
     * @param string $returnprefix The part to put before the Mahara-relative path of the file. (i.e. docroot or wwwroot)
     * @param boolean $debug If a debug message is added to log.
     * @return string|array The first match, or of all matches, depending on $all
     */
    private function _get_path($filename, $all, $plugindirectory, $returnprefix, $debug=true) {
        $list = array();
        if ($plugindirectory) {
            // If they provided a plugindirectory, make sure it ends with a slash
            // (this will save us some if-thens down the road)
            $basepluginpath = $plugindirectory;
            if (substr($basepluginpath, -1) != '/') {
                // $basepluginpath is the relative path of the plugin, i.e. blocktype/creativecommons
                $basepluginpath = $basepluginpath . '/';
            }
            // $pluginpath is the path to the plugin in a theme context, i.e. with "plugintype" in front
            $pluginpath = "plugintype/{$basepluginpath}";
        }
        else {
            $basepluginpath = $pluginpath = '';
        }

        // Local theme overrides come first
        $searchpaths = array(
                'local' => array(
                        "local/theme/{$pluginpath}{$filename}",
                        "local/theme/{$basepluginpath}static/{$filename}"
                ),
        );

        // Then check each theme
        foreach ($this->inheritance as $themedir) {
            $searchloc = array();
            // Check in the /theme directory
            $searchloc[] = "theme/{$themedir}/{$pluginpath}{$filename}";
            $searchloc[] = "theme/{$themedir}/{$basepluginpath}static/{$filename}";
            if ($basepluginpath) {
                // Then check in the plugin's own directory
                $searchloc[] = "{$basepluginpath}theme/{$themedir}/{$filename}";
                $searchloc[] = "{$basepluginpath}theme/{$themedir}/static/{$filename}";
            }
            $searchpaths[$themedir] = $searchloc;
        }

        // Check for the file in each searchpath
        foreach ($searchpaths as $theme => $searchloc) {
            foreach ($searchloc as $loc) {
                if (is_readable(get_config('docroot') . $loc)) {
                    if ($all) {
                        $list[$theme] = $returnprefix . $loc;
                    }
                    else {
                        return $returnprefix . $loc;
                    }
                }
            }
        }
        if ($all) {
            return $list;
        }

        if ($debug) {
            $this->log_debug_missing_file($filename, $plugindirectory);
        }

        return $returnprefix . $basepluginpath . 'theme/' . $themedir . '/' . $filename;
    }

    /**
     * Log debug when a file is missing.
     *
     * @param string $filename Relative path of the asset, e.g. 'images/newmail.png'
     * @param string $plugindirectory For if it's a plugin theme asset, e.g. 'artefact/file'
     * @param string $message The message prefix of the log debug.
     */
    private function log_debug_missing_file($filename, $plugindirectory='', $message='Missing file in theme') {
        $extra = '';
        if (!empty($plugindirectory)) {
            $extra = ", plugindir $plugindirectory";
        }
        log_debug("$message {$this->basename}{$extra}: $filename");
    }

    /**
     * Displaying of the header logo of an institution
     * If $name is specified the site-logo-[$name].png will be returned
     * The site logo will be returned if no institution logo is found and $name is not specified
     */
    public function header_logo($name = false) {
        if (!empty($this->headerlogo)) {
            return get_config('wwwroot') . 'thumb.php?type=logobyid&id=' . $this->headerlogo;
        }
        else if ($name) {
            return $this->get_image_url('site-logo-' . $name);
        }
        else {
            try {
                $sitelogoid = get_field('institution', 'logo', 'name', 'mahara');
                if ($sitelogoid) {
                    return get_config('wwwroot') . 'thumb.php?type=logobyid&id=' . $sitelogoid;
                }
            }
            catch (SQLException $e) {
                // Probably the site hasn't been installed or upgraded yet.
            }
        }
        return $this->get_image_url('site-logo');
    }

    /* Set the default theme's small logo */
    public function header_logo_small() {
        return $this->get_image_url('site-logo-small');
    }

    /**
     * Displaying of the small header logo of an institution
     * false will be returned if no small logo for the institution or site small logo is found
     */
    public function header_logo_small_custom() {
        if (!empty($this->headerlogosmall)) {
            return get_config('wwwroot') . 'thumb.php?type=logobyid&id=' . $this->headerlogosmall;
        }
        else {
            require_once('ddl.php');
            $table = new XMLDBTable('institution');
            $field = new XMLDBField('logoxs');
            if (field_exists($table, $field) && $sitelogocustomsmallid = get_field('institution', 'logoxs', 'name', 'mahara')) {
                return get_config('wwwroot') . 'thumb.php?type=logobyid&id=' . $sitelogocustomsmallid;
            }
        }
        return false;
    }

    public function facebook_logo() {
        return $this->get_image_url('site-logo-facebook');
    }

    public function additional_stylesheets() {
        return $this->addedstylesheets;
    }

    /**
     * Adds the URL of an image by trying differents extensions.
     * Searching for svg, png, gif and jpg in last.
     *
     * @param string $filename The name of the file without the extension and the images folder.
     * @param string $plugindirectory The plugin directory.
     * @return string The image URL with the correct file extension.
     */
    public function get_image_url($filename, $plugindirectory = '') {

        $loc = '';
        $extensions = array('svg', 'png', 'gif', 'jpg');

        // Check for all images extension in the correct order.
        $temps = array();
        foreach ($extensions as $ext) {
            $temploc = $this->_get_path("images/$filename.$ext", false, $plugindirectory, '', false);
            $temps["images/$filename.$ext"] = $temploc;
        }
        // Now check for which image exists by theme order
        $inheritance = $this->inheritance;
        array_unshift($inheritance, 'local'); // Add local dir to be checked first
        foreach ($inheritance as $theme) {
            foreach ($temps as $key => $temploc) {
                $pluginpath = $plugindirectory ? 'plugintype/' . $plugindirectory . '/' : '';
                $tplfile = 'theme/' . $theme . '/' . $pluginpath . $key;
                if ($temploc == $tplfile && is_readable(get_config('docroot') . $temploc)) {
                    $loc = $temploc;
                    break;
                }
            }
            if ($loc != '') {
                // we've found a valid file in the theme
                break;
            }
        }

        // If no image found, log the debug message and return the last non-existing image format.
        if (empty($loc)) {
            $this->log_debug_missing_file($filename, $plugindirectory, 'Missing image file in theme');
            $loc = $temploc;
        }
        return get_config('wwwroot') . $loc;

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
                'unknownerror',
                'loading',
                'showtags',
                'couldnotgethelp',
                'password',
                'deleteitem',
                'moveitemup',
                'moveitemdown',
                'username',
                'login',
                'sessiontimedout',
                'loginfailed',
                'home',
                'youhavenottaggedanythingyet',
                'wanttoleavewithoutsaving?',
                'Help',
                'Close',
                'closehelp',
                'tabs',
                'toggletoolbarson',
                'toggletoolbarsoff',
                'imagexofy',
                'remove',
                'errorprocessingform',
            ),
            'pieforms' => array(
                'element.calendar.opendatepicker',
                'rule.maxlength.maxlength',
                'rule.required.required',
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
        'views' => array(
            'view' => array(
                'confirmdeleteblockinstance',
                'blocksinstructionajaxlive',
            ),
        ),
    );
}

function sideblock_template($sideblock, &$sideblock_menu) {
    if ($sideblock === null) {
        // No side block to add - possibly due to user permissions or config settings so we ignore
        return;
    }
    // If we want to override an already available sideblock, eg quota -> groupqouta
    if (!empty($sideblock['override']) && array_key_exists($sideblock['name'], $sideblock_menu)) {
        $sideblock_menu[$sideblock['name']] = array_merge($sideblock_menu[$sideblock['name']], $sideblock);
    }
    // Make sure we don't have 2 sideblocks with same name
    if (empty($sideblock['name']) || (array_key_exists($sideblock['name'], $sideblock_menu) && empty($sideblock['override']))) {
        throw new MaharaException(get_string('sideblockmenuclash', 'error', $sideblock['name']));
    }
    // A sideblock menu can contain the following
    $defaultsideblock = array(
        'name' => null,        // Only required option
        'title' => '',
        'weight' => 0,
        'id' => null,
        'data' => array(),
        'class' => '',
        'smarty' => array(),   // If we need to set a smarty variable to a value
        'template' => null,    // Use a custom template
        'visible' => false,    // Controls whether the sideblock is visible.
                               // Examples:
                               //  to display when logged in:    'visible' => $USER->is_logged_in(),
                               //  to display for certain pages: 'visible' => (defined('MENUITEM') && in_array(MENUITEM, array('create/view'))),
    );
    $sideblock = array_merge($defaultsideblock, $sideblock);
    $sideblock_menu[$sideblock['name']] = $sideblock;
}

function themepaths() {

    static $paths;
    if (empty($paths)) {
        $paths = array(
            'mahara' => array(

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
 * @param $imagelocation path to image relative to theme/$theme/
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
 * @param $imagelocation path to image relative to theme/$theme/
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
    header('Content-type: application/json');
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

function param_exists($name) {
    return isset($_POST[$name]) || isset($_GET[$name]);
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
    return fix_utf8($value);
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

    $value = trim($value);

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

    $value = trim($value);

    if (preg_match('/^[+-]?[0-9]+$/', $value)) {
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

    $value = trim($value);

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

    $value = trim($value);

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

    $value = trim($value);

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

    $value = trim($value);

    if ($value == '') {
        return array();
    }

    if (preg_match('/^(\d+(\s*,\s*\d+)*)$/',$value)) {
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

    if (!is_null($value)) {
        $value = trim($value);
    }

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

    $value = trim($value);

    if (!preg_match('/\d+x\d+/', $value)) {
        throw new ParameterException('Invalid size for image specified');
    }

    return $value;
}

/**
 * This function returns a GET or POST array parameter as array with optional
 * default.  If the default isn't specified and the array parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the array parameter does not
 * contain valid alphanumext strings, a ParameterException exception is thrown
 *
 * Valid characters for array values are a-z and A-Z and 0-9 and _ and - and . and / and space char
 *
 * @param string The GET or POST array parameter you want returned
 * @param mixed [optional] the default array for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_array($name) {
    if (!empty($_POST[$name])) {
        return fix_utf8($_POST[$name]);
    }
    else {
        if (func_num_args() >= 2) {
            $value = func_get_arg(1);
            if ($value === null) {
                return array();
            }
            else if (is_array($value)) {
                return $value;
            }
            else {
                throw new ParameterException("The '$name' default parameter is not an array");
            }
        }
        else {
            throw new ParameterException("Missing parameter '$name' and no default supplied");
        }
    }
}

/**
 * Makes sure the data is using valid utf8, invalid characters are discarded.
 *
 * Note: this function is not intended for full objects with methods and private properties.
 *
 * @param mixed $value
 * @return mixed with proper utf-8 encoding
 */
function fix_utf8($value) {
    if (is_null($value) or $value === '') {
        return $value;
    }
    else if (is_string($value)) {
        if ((string)(int)$value === $value) {
            // Shortcut.
            return $value;
        }
        // No null bytes expected in our data, so let's remove it.
        $value = str_replace("\0", '', $value);

        static $buggyiconv = null;
        if ($buggyiconv === null) {
            $buggyiconv = (!function_exists('iconv') or @iconv('UTF-8', 'UTF-8//IGNORE', '100' . chr(130) . '€') !== '100€');
        }

        if ($buggyiconv) {
            if (function_exists('mb_convert_encoding')) {
                $subst = mb_substitute_character();
                mb_substitute_character('');
                $result = mb_convert_encoding($value, 'utf-8', 'utf-8');
                mb_substitute_character($subst);
            }
            else {
                // Warn admins on admin/index.php page.
                $result = $value;
            }
        }
        else {
            $result = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        }

        return $result;
    }
    else if (is_array($value)) {
        $newvalue = array();
        foreach ($value as $k => $v) {
            $newvalue[fix_utf8($k)] = fix_utf8($v);
        }

        return $newvalue;
    }
    else if (is_object($value)) {
        // Use clean object to ensure no funny keys are kept.
        $newvalue = array();
        foreach ($value as $k => $v) {
            $newvalue[fix_utf8($k)] = fix_utf8($v);
        }

        return (object)$newvalue;
    }
    else {
        // This is some other type, no utf-8 here.
        return $value;
    }
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
    if (!$domain = get_config('cookiedomain')) {
        $domain = $url['host'];
    }
    // If no headers are sent - to avoid CLI scripts calling logout() problems
    if (!headers_sent()) {
        setcookie($name, $value, $expires, $url['path'], $domain, is_https(), true);
        if ($access) {  // View access cookies may be needed on this request
            $_COOKIE[$name] = $value;
        }
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
    require_once(get_config('libroot') . 'country.php');
    $codes = Country::iso3166_alpha2();

    foreach ($codes as $c) {
        $countries[$c] = get_string("country.{$c}");
    };
    uasort($countries, 'strcoll');
    return $countries;
}

/**
 * Returns an assoc array of timezones suitable for use with the "select" form
 * element
 *
 * @return array Associative array of timezone => timezone
 */
function getoptions_timezone() {
    static $timezones;
    if (!empty($timezones)) {
        return $timezones;
    }
    $zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

    foreach ($zones as $z) {
        $timezones[$z] = $z;
    };
    return $timezones;
}

/**
 * Returns an HTML string with a help icon image that can be used on a page.
 * When the icon is clicked, a dialog box will be shown with contextual help
 * for the element or page the icon is connected to.
 *
 * All parameters except $title determine where the help text will be found.
 * For example:
 * <code>
 * // Returns the help text in artefact/blog/lang/[lang]/help/forms/editpost.draft.html
 * get_help_icon('artefact', 'blog', 'editpost', 'draft');
 * // Returns the help text in artefact/internal/lang/[lang]/help/pages/index.html
 * get_help_icon('artefact', 'internal', '', '', 'index');
 * </code>
 *
 * @param string $plugintype the type of plugin to find help text for
 * @param string $pluginname the name of the plugin to find help text for
 * @param string $form the ID of the form this help icon is connected to
 * @param string $element the ID of the form element this help icon is connected to
 * @param string $page the page this help icon describes
 * @param string $section the section this help icon describes
 * @param string $title the title/label of the element this help icon is connected to
 *
 * @return string HTML with help icon element
 */
function get_help_icon($plugintype, $pluginname, $form, $element, $page='', $section='', $title=null) {
    global $THEME;

    if ($title) {
        $content = get_string('helpfor', 'mahara', $title);
    }
    else {
        $content = get_string('Help');
    }

    return ' <span class="help"><a href="#" title="' . get_string('Help') . '" onclick="'.
        hsc(
            'contextualHelp(' . json_encode($form) . ',' .
            json_encode($element) . ',' . json_encode($plugintype) . ',' .
            json_encode($pluginname) . ',' . json_encode($page) . ',' .
            json_encode($section)
            . ',this); return false;'
        ) . '"><span class="icon icon-info-circle" role="presentation"></span><span class="sr-only">'. $content . '</span></a></span>';
}

function pieform_get_help(Pieform $form, $element) {
    $plugintype = isset($element['helpplugintype']) ? $element['helpplugintype'] : $form->get_property('plugintype');
    $pluginname = isset($element['helppluginname']) ? $element['helppluginname'] : $form->get_property('pluginname');
    $formname = isset($element['helpformname']) ? $element['helpformname'] : $form->get_name();
    return get_help_icon($plugintype, $pluginname, $formname, $element['name'], '', '', (isset($element['title']) ? $element['title'] : null));
}

/**
 * Is this a page in the admin area?
 *
 * @return bool
 */
function in_admin_section() {
    return defined('ADMIN') || defined('INSTITUTIONALADMIN') || defined('STAFF') || defined('INSTITUTIONALSTAFF') || defined('INADMINMENU');
}

/**
 * Returns the entries in the standard admin menu
 *
 * See the function find_menu_children() in lib/web.php
 * for a description of the expected array structure.
 *
 * @return $adminnav a data structure containing the admin navigation
 */
function admin_nav() {
    $menu = array(
        'adminhome' => array(
            'path'   => 'adminhome',
            'url'    => 'admin/index.php',
            'title'  => get_string('adminhome', 'admin'),
            'weight' => 10,
            'iconclass' => 'home',
        ),
        'adminhome/home' => array(
            'path'   => 'adminhome/home',
            'url'    => 'admin/index.php',
            'title'  => get_string('overview'),
            'weight' => 10,
        ),
        'adminhome/registersite' => array(
            'path'   => 'adminhome/registersite',
            'url'    => 'admin/registersite.php',
            'title'  => get_string('register'),
            'weight' => 20,
        ),
        'configsite' => array(
            'path'   => 'configsite',
            'url'    => 'admin/site/options.php',
            'title'  => get_string('configsite', 'admin'),
            'weight' => 20,
            'iconclass' => 'cogs',
        ),
        'configsite/siteoptions' => array(
            'path'   => 'configsite/siteoptions',
            'url'    => 'admin/site/options.php',
            'title'  => get_string('siteoptions', 'admin'),
            'weight' => 10,
        ),
        'configsite/sitepages' => array(
            'path'   => 'configsite/sitepages',
            'url'    => 'admin/site/pages.php',
            'title'  => get_string('staticpages', 'admin'),
            'weight' => 20
        ),
        'configsite/privacy' => array(
            'path'   => 'configsite/privacy',
            'url'    => 'admin/site/privacy.php',
            'title'  => get_string('legal', 'admin'),
            'weight' => 25,
        ),
        'configsite/sitemenu' => array(
            'path'   => 'configsite/sitemenu',
            'url'    => 'admin/site/menu.php',
            'title'  => get_string('menus', 'admin'),
            'weight' => 30,
        ),
        'configsite/networking' => array(
            'path'   => 'configsite/networking',
            'url'    => 'admin/site/networking.php',
            'title'  => get_string('networking', 'admin'),
            'weight' => 40,
        ),
        'configsite/sitelicenses' => array(
            'path'   => 'configsite/sitelicenses',
            'url'    => 'admin/site/licenses.php',
            'title'  => get_string('sitelicenses', 'admin'),
            'weight' => 45,
        ),
        'configsite/siteviews' => array(
            'path'   => 'configsite/siteviews',
            'url'    => 'admin/site/views.php',
            'title'  => get_string('Viewscollections', 'view'),
            'weight' => 50,
        ),
        'configsite/share' => array(
            'path'   => 'configsite/share',
            'url'    => 'admin/site/shareviews.php',
            'title'  => get_string('share', 'view'),
            'weight' => 70,
        ),
        'configsite/sitefiles' => array(
            'path'   => 'configsite/sitefiles',
            'url'    => 'artefact/file/sitefiles.php',
            'title'  => get_string('Files', 'artefact.file'),
            'weight' => 80,
        ),
        'configsite/cookieconsent' => array(
            'path'   => 'configsite/cookieconsent',
            'url'    => 'admin/site/cookieconsent.php',
            'title'  => get_string('cookieconsent', 'cookieconsent'),
            'weight' => 90,
        ),
        'configusers' => array(
            'path'   => 'configusers',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('users'),
            'weight' => 30,
            'iconclass' => 'user',
        ),
        'configusers/usersearch' => array(
            'path'   => 'configusers/usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 10,
        ),
        'configusers/suspendedusers' => array(
            'path'   => 'configusers/suspendedusers',
            'url'    => 'admin/users/suspended.php',
            'title'  => get_string('suspendeduserstitle', 'admin'),
            'weight' => 15,
        ),
        'configusers/staffusers' => array(
            'path'   => 'configusers/staffusers',
            'url'    => 'admin/users/staff.php',
            'title'  => get_string('sitestaff', 'admin'),
            'weight' => 20,
        ),
        'configusers/adminusers' => array(
            'path'   => 'configusers/adminusers',
            'url'    => 'admin/users/admins.php',
            'title'  => get_string('siteadmins', 'admin'),
            'weight' => 30,
        ),
        'configusers/exportqueue' => array(
            'path'   => 'configusers/exportqueue',
            'url'    => 'admin/users/exportqueue.php',
            'title'  => get_string('exportqueue', 'admin'),
            'weight' => 35,
        ),
        'configusers/adduser' => array(
            'path'   => 'configusers/adduser',
            'url'    => 'admin/users/add.php',
            'title'  => get_string('adduser', 'admin'),
            'weight' => 40,
        ),
        'configusers/uploadcsv' => array(
            'path'   => 'configusers/uploadcsv',
            'url'    => 'admin/users/uploadcsv.php',
            'title'  => get_string('uploadcsv', 'admin'),
            'weight' => 50,
        ),
        'managegroups' => array(
            'path'   => 'managegroups',
            'url'    => 'admin/groups/groups.php',
            'title'  => get_string('groups', 'admin'),
            'accessibletitle' => get_string('administergroups', 'admin'),
            'weight' => 40,
            'iconclass' => 'users',
        ),
        'managegroups/groups' => array(
            'path'   => 'managegroups/groups',
            'url'    => 'admin/groups/groups.php',
            'title'  => get_string('administergroups', 'admin'),
            'weight' => 10,
        ),
        'managegroups/categories' => array(
            'path'   => 'managegroups/categories',
            'url'    => 'admin/groups/groupcategories.php',
            'title'  => get_string('groupcategories', 'admin'),
            'weight' => 20,
        ),
        'managegroups/archives' => array(
            'path'   => 'managegroups/archives',
            'url'    => 'admin/groups/archives.php',
            'title'  => get_string('archivedsubmissions', 'admin'),
            'weight' => 25,
        ),
        'managegroups/uploadcsv' => array(
            'path'   => 'managegroups/uploadcsv',
            'url'    => 'admin/groups/uploadcsv.php',
            'title'  => get_string('uploadgroupcsv', 'admin'),
            'weight' => 30,
        ),
        'managegroups/uploadmemberscsv' => array(
            'path'   => 'managegroups/uploadmemberscsv',
            'url'    => 'admin/groups/uploadmemberscsv.php',
            'title'  => get_string('uploadgroupmemberscsv', 'admin'),
            'weight' => 40,
        ),
        'manageinstitutions' => array(
            'path'   => 'manageinstitutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('Institutions', 'admin'),
            'weight' => 50,
            'iconclass' => 'university',
        ),
        'manageinstitutions/institutions' => array(
            'path'   => 'manageinstitutions/institutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('settings', 'admin'),
            'weight' => 10,
        ),
        'manageinstitutions/sitepages' => array(
            'path'   => 'manageinstitutions/sitepages',
            'url'    => 'admin/users/institutionpages.php',
            'title'  => get_string('staticpages', 'admin'),
            'weight' => 15
        ),
        'manageinstitutions/privacy' => array(
            'path'   => 'manageinstitutions/privacy',
            'url'    => 'admin/users/institutionprivacy.php',
            'title'  => get_string('legal', 'admin'),
            'weight' => 17
        ),
        'manageinstitutions/institutionusers' => array(
            'path'   => 'manageinstitutions/institutionusers',
            'url'    => 'admin/users/institutionusers.php',
            'title'  => get_string('Members', 'admin'),
            'weight' => 20,
        ),
        'manageinstitutions/institutionstaff' => array(
            'path'   => 'manageinstitutions/institutionstaff',
            'url'    => 'admin/users/institutionstaff.php',
            'title'  => get_string('Staff', 'admin'),
            'weight' => 30,
        ),
        'manageinstitutions/institutionadmins' => array(
            'path'   => 'manageinstitutions/institutionadmins',
            'url'    => 'admin/users/institutionadmins.php',
            'title'  => get_string('Admins', 'admin'),
            'weight' => 40,
        ),
        'manageinstitutions/adminnotifications' => array(
            'path'   => 'manageinstitutions/adminnotifications',
            'url'    => 'admin/users/notifications.php',
            'title'  => get_string('adminnotifications', 'admin'),
            'weight' => 50,
        ),
        'manageinstitutions/progressbar' => array(
            'path'   => 'manageinstitutions/progressbar',
            'url'    => 'admin/users/progressbar.php',
            'title'  => get_string('progressbar', 'admin'),
            'weight' => 55,
        ),
        'manageinstitutions/institutionviews' => array(
            'path'   => 'manageinstitutions/institutionviews',
            'url'    => 'view/institutionviews.php',
            'title'  => get_string('Viewscollections', 'view'),
            'weight' => 60,
        ),
        'manageinstitutions/share' => array(
            'path'   => 'manageinstitutions/share',
            'url'    => 'view/institutionshare.php',
            'title'  => get_string('share', 'view'),
            'weight' => 80,
        ),
        'manageinstitutions/institutionfiles' => array(
            'path'   => 'manageinstitutions/institutionfiles',
            'url'    => 'artefact/file/institutionfiles.php',
            'title'  => get_string('Files', 'artefact.file'),
            'weight' => 90,
        ),
        'manageinstitutions/pendingregistrations' => array(
            'path'   => 'manageinstitutions/pendingregistrations',
            'url'    => 'admin/users/pendingregistrations.php',
            'title'  => get_string('pendingregistrations', 'admin'),
            'weight' => 100,
        ),
        'manageinstitutions/pendingdeletions' => array(
            'path'   => 'manageinstitutions/pendingdeletions`',
            'url'    => 'admin/users/pendingdeletions.php',
            'title'  => get_string('pendingdeletions', 'admin'),
            'weight' => 110,
        ),
        'reports' => array(
            'path'   => 'reports',
            'url'    => 'admin/users/statistics.php',
            'title'  => get_string('reports', 'statistics'),
            'weight' => 60,
            'iconclass' => 'pie-chart',
        ),
        'configextensions' => array(
            'path'   => 'configextensions',
            'url'    => 'admin/extensions/plugins.php',
            'title'  => get_string('Extensions', 'admin'),
            'weight' => 70,
            'iconclass' => 'puzzle-piece',
        ),
        'configextensions/pluginadmin' => array(
            'path'   => 'configextensions/pluginadmin',
            'url'    => 'admin/extensions/plugins.php',
            'title'  => get_string('pluginadmin', 'admin'),
            'weight' => 10,
        ),
        'configextensions/filters' => array(
            'path'   => 'configextensions/filters',
            'url'    => 'admin/extensions/filter.php',
            'title'  => get_string('htmlfilters', 'admin'),
            'weight' => 20,
        ),
        'configextensions/iframesites' => array(
            'path'   => 'configextensions/iframesites',
            'url'    => 'admin/extensions/iframesites.php',
            'title'  => get_string('allowediframesites', 'admin'),
            'weight' => 30,
        ),
        'configextensions/cleanurls' => array(
            'path'   => 'configextensions/cleanurls',
            'url'    => 'admin/extensions/cleanurls.php',
            'title'  => get_string('cleanurls', 'admin'),
            'weight' => 40,
        ),
    );

    // Add the menu items for skins, if that feature is enabled
    if (get_config('skins')) {
        $menu['configsite/siteskins'] = array(
           'path'   => 'configsite/siteskins',
           'url'    => 'admin/site/skins.php',
           'title'  => get_string('siteskinmenu', 'skin'),
           'weight' => 75,
        );
        $menu['configsite/sitefonts'] = array(
           'path'   => 'configsite/sitefonts',
           'url'    => 'admin/site/fonts.php',
           'title'  => get_string('sitefontsmenu', 'skin'),
           'weight' => 76,
        );
    }

    // Add the menu items for tags, if that feature is enabled in a visible institution.
    if (($selector = get_institution_selector(false, false, false, false, false, true)) && !empty($selector['options'])) {
        $menu['manageinstitutions/institutiontags'] = array(
            'path'   => 'manageinstitutions/tags',
            'url'    => 'admin/users/institutiontags.php',
            'title'  => get_string('tags'),
            'weight' => 95
        );
    }

    // enable plugins to augment the admin menu structure
    foreach (array('artefact', 'interaction', 'module', 'auth') as $plugintype) {
        if ($plugins = plugins_installed($plugintype)) {
            foreach ($plugins as &$plugin) {
                if (safe_require_plugin($plugintype, $plugin->name)) {
                    $plugin_menu = call_static_method(generate_class_name($plugintype,$plugin->name), 'admin_menu_items');
                    $menu = array_merge($menu, $plugin_menu);
                }
            }
        }
    }

    return $menu;
}

/**
 * Returns the entries in the standard institutional admin menu
 *
 * See the function find_menu_children() in lib/web.php
 * for a description of the expected array structure.
 *
 * @return $adminnav a data structure containing the admin navigation
 */
function institutional_admin_nav() {
    global $USER;

    $ret = array(
        'configusers' => array(
            'path'   => 'configusers',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('users'),
            'weight' => 10,
            'iconclass' => 'user',
        ),
        'configusers/usersearch' => array(
            'path'   => 'configusers/usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 10,
        ),
        'configusers/suspendedusers' => array(
            'path'   => 'configusers/suspendedusers',
            'url'    => 'admin/users/suspended.php',
            'title'  => get_string('suspendeduserstitle', 'admin'),
            'weight' => 20,
        ),
        'configusers/exportqueue' => array(
            'path'   => 'configusers/exportqueue',
            'url'    => 'admin/users/exportqueue.php',
            'title'  => get_string('exportqueue', 'admin'),
            'weight' => 25,
        ),
        'configusers/adduser' => array(
            'path'   => 'configusers/adduser',
            'url'    => 'admin/users/add.php',
            'title'  => get_string('adduser', 'admin'),
            'weight' => 30,
        ),
        'configusers/uploadcsv' => array(
            'path'   => 'configusers/uploadcsv',
            'url'    => 'admin/users/uploadcsv.php',
            'title'  => get_string('uploadcsv', 'admin'),
            'weight' => 40,
        ),
        'managegroups' => array(
            'path'   => 'managegroups',
            'url'    => 'admin/groups/uploadcsv.php',
            'title'  => get_string('groups', 'admin'),
            'accessibletitle' => get_string('administergroups', 'admin'),
            'weight' => 20,
            'iconclass' => 'users',
        ),
        'managegroups/archives' => array(
            'path'   => 'managegroups/archives',
            'url'    => 'admin/groups/archives.php',
            'title'  => get_string('archivedsubmissions', 'admin'),
            'weight' => 5,
        ),
        'managegroups/uploadcsv' => array(
            'path'   => 'managegroups/uploadcsv',
            'url'    => 'admin/groups/uploadcsv.php',
            'title'  => get_string('uploadgroupcsv', 'admin'),
            'weight' => 10,
        ),
        'managegroups/uploadmemberscsv' => array(
            'path'   => 'managegroups/uploadmemberscsv',
            'url'    => 'admin/groups/uploadmemberscsv.php',
            'title'  => get_string('uploadgroupmemberscsv', 'admin'),
            'weight' => 20,
        ),
        'manageinstitutions' => array(
            'path'   => 'manageinstitutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('Institutions', 'admin'),
            'weight' => 30,
            'iconclass' => 'university',
        ),
        'manageinstitutions/institutions' => array(
            'path'   => 'manageinstitutions/institutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('settings'),
            'weight' => 10,
        ),
        'manageinstitutions/sitepages' => array(
            'path'   => 'manageinstitutions/sitepages',
            'url'    => 'admin/users/institutionpages.php',
            'title'  => get_string('staticpages', 'admin'),
            'weight' => 15
        ),
        'manageinstitutions/privacy' => array(
            'path'   => 'manageinstitutions/privacy',
            'url'    => 'admin/users/institutionprivacy.php',
            'title'  => get_string('legal', 'admin'),
            'weight' => 17
        ),
        'manageinstitutions/institutionusers' => array(
            'path'   => 'manageinstitutions/institutionusers',
            'url'    => 'admin/users/institutionusers.php',
            'title'  => get_string('Members', 'admin'),
            'weight' => 20,
        ),
        'manageinstitutions/institutionstaff' => array(
            'path'   => 'manageinstitutions/institutionstaff',
            'url'    => 'admin/users/institutionstaff.php',
            'title'  => get_string('Staff', 'admin'),
            'weight' => 30,
        ),
        'manageinstitutions/institutionadmins' => array(
            'path'   => 'manageinstitutions/institutionadmins',
            'url'    => 'admin/users/institutionadmins.php',
            'title'  => get_string('Admins', 'admin'),
            'weight' => 40,
        ),
        'manageinstitutions/adminnotifications' => array(
            'path'   => 'manageinstitutions/adminnotifications',
            'url'    => 'admin/users/notifications.php',
            'title'  => get_string('adminnotifications', 'admin'),
            'weight' => 50,
        ),
        'manageinstitutions/progressbar' => array(
            'path'   => 'manageinstitutions/progressbar',
            'url'    => 'admin/users/progressbar.php',
            'title'  => get_string('progressbar', 'admin'),
            'weight' => 55,
        ),
        'manageinstitutions/institutionviews' => array(
            'path'   => 'manageinstitutions/institutionviews',
            'url'    => 'view/institutionviews.php',
            'title'  => get_string('Viewscollections', 'view'),
            'weight' => 60,
        ),
        'manageinstitutions/share' => array(
            'path'   => 'manageinstitutions/share',
            'url'    => 'view/institutionshare.php',
            'title'  => get_string('share', 'view'),
            'weight' => 80,
        ),
        'manageinstitutions/institutionfiles' => array(
            'path'   => 'manageinstitutions/institutionfiles',
            'url'    => 'artefact/file/institutionfiles.php',
            'title'  => get_string('Files', 'artefact.file'),
            'weight' => 90,
        ),
        'manageinstitutions/pendingregistrations' => array(
            'path'   => 'manageinstitutions/pendingregistrations',
            'url'    => 'admin/users/pendingregistrations.php',
            'title'  => get_string('pendingregistrations', 'admin'),
            'weight' => 100,
        ),
        'manageinstitutions/pendingdeletions' => array(
            'path'   => 'manageinstitutions/pendingdeletions`',
            'url'    => 'admin/users/pendingdeletions.php',
            'title'  => get_string('pendingdeletions', 'admin'),
            'weight' => 110,
        ),
        'reports' => array(
            'path'   => 'reports',
            'url'    => 'admin/users/statistics.php',
            'title'  => get_string('reports', 'statistics'),
            'weight' => 40,
            'iconclass' => 'pie-chart',
        ),
    );

    // Add the menu items for tags, if that feature is enabled in a visible institution.
    if (($selector = get_institution_selector(false, false, false, false, false, true)) && !empty($selector['options'])) {
        $ret['manageinstitutions/institutiontags'] = array(
            'path'   => 'manageinstitutions/tags',
            'url'    => 'admin/users/institutiontags.php',
            'title'  => get_string('tags'),
            'weight' => 95
        );
    }

    // enable plugins to augment the institution admin menu structure
    foreach (array('artefact', 'interaction', 'module', 'auth') as $plugintype) {
        if ($plugins = plugins_installed($plugintype)) {
            foreach ($plugins as &$plugin) {
                if (safe_require_plugin($plugintype, $plugin->name)) {
                    $plugin_menu = call_static_method(generate_class_name($plugintype,$plugin->name), 'institution_menu_items');
                    $ret = array_merge($ret, $plugin_menu);
                }
            }
        }
    }

    return $ret;
}

/**
 * Returns the entries in the staff menu
 *
 * See the function find_menu_children() in lib/web.php
 * for a description of the expected array structure.
 *
 * @return a data structure containing the staff navigation
 */
function staff_nav() {
    $menu = array(
        'usersearch' => array(
            'path'   => 'usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 10,
            'iconclass' => 'user',
        ),
        'reports' => array(
            'path'   => 'reports',
            'url'    => 'admin/users/statistics.php',
            'title'  => get_string('reports', 'statistics'),
            'weight' => 30,
            'iconclass' => 'pie-chart',
        ),
    );

    // enable plugins to augment the institution staff menu structure
    foreach (array('artefact', 'interaction', 'module', 'auth') as $plugintype) {
        if ($plugins = plugins_installed($plugintype)) {
            foreach ($plugins as &$plugin) {
                if (safe_require_plugin($plugintype, $plugin->name)) {
                    $plugin_menu = call_static_method(generate_class_name($plugintype,$plugin->name), 'institution_staff_menu_items');
                    $menu = array_merge($menu, $plugin_menu);
                }
            }
        }
    }

    return $menu;
}

/**
 * Returns the entries in the institutional staff menu
 *
 * See the function find_menu_children() in lib/web.php
 * for a description of the expected array structure.
 *
 * @return a data structure containing the institutional staff navigation
 */
function institutional_staff_nav() {
    return array(
        'usersearch' => array(
            'path'   => 'usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 10,
            'iconclass' => 'user',
        ),
        'reports' => array(
            'path'   => 'reports',
            'url'    => 'admin/users/statistics.php',
            'title'  => get_string('reports', 'statistics'),
            'weight' => 20,
            'iconclass' => 'pie-chart',
        ),
    );
}

/**
 * Returns the entries in the standard user menu
 *
 * See the function find_menu_children() in lib/web.php
 * for a description of the expected array structure.
 *
 * @return $standardnav a data structure containing the standard navigation
 */
function mahara_standard_nav() {
    global $SESSION;

    $exportenabled = (plugins_installed('export') && !$SESSION->get('handheld_device')) ? TRUE : FALSE;
    $importenabled = (plugins_installed('import') && !$SESSION->get('handheld_device')) ? TRUE : FALSE;

    $menu = array(
        'home' => array(
            'path' => 'home',
            'url' => '',
            'title' => get_string('dashboard', 'view'),
            'weight' => 10,
            'iconclass' => 'tachometer'
        ),
        'create' => array(
            'path' => 'create',
            'url'  => null,
            'title' => get_string('Create'),
            'weight' => 20,
            'iconclass' => 'plus',
        ),
        'share' => array(
            'path' => 'share',
            'url' => null,
            'title' => get_string('share'),
            'weight' => 30,
            'iconclass' => 'unlock-alt',
        ),
        'engage' => array(
            'path' => 'engage',
            'url' => null,
            'title' => get_string('Engage'),
            'weight' => 40,
            'iconclass' => 'users',
        ),
        'create/views' => array(
            'path' => 'create/views',
            'url' => 'view/index.php',
            'title' => get_string('Viewscollections', 'view'),
            'weight' => 10,
        ),
        'create/tags' => array(
            'path' => 'create/tags',
            'url' => 'tags.php',
            'title' => get_string('tags'),
            'weight' => 80,
        ),
        'share/sharedbyme' => array(
            'path' => 'share/sharedbyme',
            'url' => 'view/share.php',
            'title' => get_string('sharedbyme', 'view'),
            'weight' => 10,
        ),
        'share/sharedviews' => array(
            'path' => 'share/sharedviews',
            'url' => 'view/sharedviews.php',
            'title' => get_string('sharedwithme', 'view'),
            'weight' => 20,
        ),
        'manage/export' => array(
            'path' => 'manage/export',
            'url' => 'export/index.php',
            'title' => get_string('Export', 'export'),
            'weight' => 70,
            'ignore' => !$exportenabled,
        ),
        'manage/import' => array(
            'path' => 'manage/import',
            'url' => 'import/index.php',
            'title' => get_string('Import', 'import'),
            'weight' => 80,
            'ignore' => !$importenabled,
        ),
        'manage' => array(
            'path' => 'manage',
            'url' => null,
            'title' => get_string('Manage'),
            'weight' => 40,
            'iconclass' => 'wrench',
        ),
        'engage/people' => array(
            'path' => 'engage/people',
            'url' => 'user/index.php',
            'title' => get_string('people'),
            'weight' => 10,
        ),
        'engage/index' => array(
            'path' => 'engage/index',
            'url' => 'group/index.php',
            'title' => get_string('groups'),
            'weight' => 30,
        ),
        'engage/institutionmembership' => array(
            'path' => 'engage/institutions',
            'url' => 'account/institutions.php',
            'title' => get_string('institutionmembership'),
            'weight' => 60,
        ),
    );

    if (can_use_skins()) {
        $menu['create/skins'] = array(
           'path' => 'create/skins',
           'url' => 'skin/index.php',
           'title' => get_string('myskins', 'skin'),
           'weight' => 70,
        );
    }

    return $menu;
}

/**
 * Builds a data structure representing the menu for Mahara.
 *
 * @return array
 */
function main_nav($type = null) {
    global $USER, $SESSION;

    $language = current_language();
    $cachemenu = false;
    // Get the first institution
    $institution = $USER->get_primary_institution();
    $menutype = $SESSION->get('handheld_device') ? 'mob_' : '';
    if ($type == 'adminnav') {
        global $USER, $SESSION;
        if ($USER->get('admin')) {
            $menutype .= 'admin_nav';
            if (!($cachemenu = get_config_institution($institution, $menutype . '_' . $language))) {
                $menu = admin_nav();
            }
        }
        else if ($USER->get('staff')) {
            $menutype .= 'staff_nav';
            if (!($cachemenu = get_config_institution($institution, $menutype . '_' . $language))) {
                $menu = staff_nav();
            }
        }
        else if ($USER->is_institutional_admin()) {
            $menutype .= 'instadmin_nav';
            if (!($cachemenu = get_config_institution($institution, $menutype . '_' . $language))) {
                $menu = institutional_admin_nav();
            }
        }
        else {
            $menutype .= 'inststaff_nav';
            if (!($cachemenu = get_config_institution($institution, $menutype . '_' . $language))) {
                $menu = institutional_staff_nav();
            }
        }
    }
    else {
        // Build the menu structure for the site
        $menutype .= 'standard_nav';
        if (!($cachemenu = get_config_institution($institution, $menutype . '_' . $language))) {
            $menu = mahara_standard_nav();
        }
    }

    if ($cachemenu) {
        $menu = json_decode($cachemenu, true);
    }
    else {
        $menu = array_filter($menu, function($a) { return empty($a["ignore"]); });

        // enable plugins to augment the menu structure
        foreach (array('artefact', 'interaction', 'module', 'auth') as $plugintype) {
            if ($plugins = plugins_installed($plugintype)) {
                foreach ($plugins as &$plugin) {
                    if (safe_require_plugin($plugintype, $plugin->name)) {
                        $plugin_menu = call_static_method(generate_class_name($plugintype,$plugin->name), 'menu_items');
                        $menu = array_merge($menu, $plugin_menu);
                    }
                }
            }
        }
        set_config_institution($institution, $menutype . '_' . $language, json_encode($menu));
    }

    // local_main_nav_update allows sites to customise the menu by munging the $menu array.
    // as there is no internal way to know if the local_main_nav array has changed we keep it outside the cached menu
    if (function_exists('local_main_nav_update')) {
        local_main_nav_update($menu);
    }

    $menu_structure = find_menu_children($menu, '');
    return $menu_structure;
}

/**
 * Clear the cached menu so that the next visit to the site will recreate the cache.
 *
 * @param   string   $institution   Optional institution name if we only want to delete cache from a certain institution
 */
function clear_menu_cache($institution = null) {
    if ($institution) {
        try {
            delete_records_sql("DELETE FROM {institution_config} WHERE field LIKE '%/_nav/_%' ESCAPE '/' AND institution = ?", array($institution));
        }
        catch (SQLException $e) {
            // Institution_config table may not exist on install/upgrade at this point
        }
    }
    else {
        try {
            delete_records_sql("DELETE FROM {institution_config} WHERE field LIKE '%/_nav/_%' ESCAPE '/'", array());
        }
        catch (SQLException $e) {
            // Institution_config table may not exist on install/upgrade at this point
        }
    }
}

function message_nav() {
    global $USER, $THEME;
    $menu = array(
        'inbox' => array(),
    );

    if (safe_require_plugin('module', 'multirecipientnotification')) {
        $plugin_nav_menu = call_static_method(generate_class_name('module', 'multirecipientnotification'),
                                              'messages_menu_items');
        $menu = array_merge($menu, $plugin_nav_menu);
    }
    return $menu;
}

function right_nav() {
    global $USER, $THEME;

    safe_require('notification', 'internal');
    $unread = $USER->get('unread');

    $menu = array(
        'userdashboard' => array(
            'path' => 'userdashboard',
            'url' => profile_url($USER, false),
            'title' => display_default_name($USER),
            'alt' => '',
            'weight' => 10,
            'iconclass' => 'user'
        ),
        'settings' => array(
            'path' => 'settings',
            'url' => null,
            'title' => get_string('settings'),
            'alt' => '',
            'weight' => 20,
            'iconclass' => 'cogs'
        ),
        'settings/account' => array(
            'path' => 'settings/account',
            'url' => 'account/index.php',
            'title' => get_string('preferences'),
            'weight' => 10,
            'iconclass' => 'user'
        ),
        'settings/privacy' => array(
            'path' => 'settings/privacy',
            'url' => 'account/userprivacy.php',
            'title' => get_string('legal', 'admin'),
            'weight' => 30
        ),
        'settings/apps' => array(
            'path' => 'settings/apps',
            'url' => 'account/apps.php',
            'title' => get_string('connectedapps'),
            'weight' => 50
        ),
        'settings/notifications' => array(
            'path' => 'settings/notifications',
            'url' => 'account/activity/preferences/index.php',
            'title' => get_string('notifications'),
            'weight' => 40,
            'iconclass' => 'flag'
        ),
    );

    // enable plugins to augment the menu structure
    foreach (array('artefact', 'blocktype', 'interaction', 'module') as $plugintype) {
        if ($plugins = plugins_installed($plugintype)) {
            foreach ($plugins as &$plugin) {
                if (safe_require_plugin($plugintype, $plugin->name)) {
                    $plugin_nav_menu = call_static_method(generate_class_name($plugintype, $plugin->name),
                        'right_nav_menu_items');
                    $menu = array_merge($menu, $plugin_nav_menu);
                }
            }
        }
    }
    // local_right_nav_update allows sites to customise the menu by munging the $menu array.
    if (function_exists('local_right_nav_update')) {
        local_right_nav_update($menu);
    }
    $menu_structure = find_menu_children($menu, '');
    return $menu_structure;
}


function footer_menu($all=false) {
    global $USER;
    $wwwroot = get_config('wwwroot');

    $menu = array(
        'legal' => array(
            'url'   => ($USER->is_logged_in() ? $wwwroot . 'account/userprivacy.php' : $wwwroot . 'legal.php'),
            'title' => get_string('legal'),
        ),
        'about' => array(
            'url'   => $wwwroot . 'about.php',
            'title' => get_string('about'),
        ),
        'contactus' => array(
            'url'   => $wwwroot . 'contact.php',
            'title' => get_string('contactus'),
        ),
    );
    $helpkeys = null;
    if (defined('MENUITEM')) {
        $helpkey = ($USER->is_logged_in() ? '' : 'loggedout') . MENUITEM;
        $helpkeys = explode('/', $helpkey);
    }
    if (defined('SECTION_PAGE') && !defined('RESUME_SUBPAGE')) {
        $helpkeys[] = SECTION_PAGE;
    }
    if (defined('VIEW_TYPE')) {
        $helpkeys[] = VIEW_TYPE;
    }
    // To handle the 'Report' pages where type/subtype are passed in as params
    if (defined('MENUITEM') && MENUITEM == 'reports') {
        if (param_exists('type')) {
            $helpkeys[] = param_alpha('type');
        }
        if (param_exists('subtype')) {
            $helpkeys[] = param_variable('subtype');
        }
    }
    // To handle the configmanager page where plugintye/pluginname are
    // passed in as params (not when defined as part of php file)
    if (param_exists('plugintype')) {
        $helpkeys[] = param_alpha('plugintype');
    }
    if (param_exists('pluginname')) {
        $helpkeys[] = param_variable('pluginname');
    }
    // If we are in an arrow submenu
    if (defined('MENUITEM_SUBPAGE')) {
        $helpkeys[] = MENUITEM_SUBPAGE;
    }
    // If group is set
    if (param_exists('group')) {
        $helpkeys[] = 'group';
    }
    // If institution is set
    if (param_exists('institution') || param_exists('i')) {
        $helpkeys[] = 'institution';
    }
    // To handle when things have a 'new' state vs edit state
    if (param_exists('id')) {
        if (param_exists('new')) {
            $helpkeys[] = 'new';
        }
    }
    // To handle when things have an explicit 'filter' state
    if (param_exists('filter')) {
        $helpkeys[] = param_alphanum('filter', null);
    }

    $menu['manualhelp'] = array('fullurl' => get_manual_help_link($helpkeys),
                                'title' => get_string('externalmanual'),
                                'url' => _get_manual_link_prefix(),
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
    if ($customlinks = get_config('footercustomlinks')) {
        $customlinks = unserialize($customlinks);
        foreach ($customlinks as $k => $v) {
            if (!empty($menu[$k])) {
                $menu[$k]['url'] = $v;
            }
        }
    }
    return $menu;
}

function apps_get_menu_tabs() {
    $menu = array();

    foreach (plugin_types_installed() as $plugin_type_installed) {
        foreach (plugins_installed($plugin_type_installed) as $plugin) {
            safe_require($plugin_type_installed, $plugin->name);
            if (method_exists(generate_class_name($plugin_type_installed, $plugin->name), 'app_tabs')) {
                $plugin_menu = call_static_method(
                    generate_class_name($plugin_type_installed, $plugin->name),
                    'app_tabs'
                );
                $menu = array_merge($menu, $plugin_menu);
            }
        }
    }
    if (defined('MENUITEM')) {
        $key = substr(MENUITEM, strlen('settings/'));
        if ($key && isset($menu[$key])) {
            $menu[$key]['selected'] = true;
        }
    }

    // Sort the menu items by weight
    uasort($menu, "sort_menu_by_weight");

    return $menu;
}

/**
 * Given a menu structure and a path, returns a data structure representing all
 * of the child menu items of the path, and removes those items from the menu
 * structure
 *
 * The menu structure should be an array. Each item in the array should be
 * a sub-array representing one of the nodes in the menu.
 *
 * The keys of each menu node are as follows:
 *   path: Where the link sits in the menu. E.g. 'myporfolio/myplugin'
 *   parent: (optional) The parent path - use if tertiary level menu
 *   url:  The URL to the page, relative to wwwroot. E.g. 'artefact/myplugin/'
 *   title: Translated text to use for the text of the link. E.g. get_string('myplugin', 'artefact.myplugin')
 *   weight: Where in the menu the item should be inserted. Larger number are to the right.
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
                || ($item['path'] . '/' == substr(MENUITEM, 0, strlen($item['path'])+1))
                || (!empty($item['parent']) && $item['parent'] == MENUITEM));
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

function selfsearch_sideblock() {
    global $USER;
    if (get_config('showselfsearchsideblock')) {
        $sideblock = array(
            'name'   => 'selfsearch',
            'weight' => 5,
            'data'   => array(),
            'template' => 'sideblocks/selfsearch.tpl',
            'visible' => $USER->is_logged_in() &&
                         (defined('MENUITEM') &&
                          in_array(MENUITEM, array('profile',
                                                   'create/files',
                                                   'share/sharedbyme',
                                                   'create/views')
                                   )
                         ),
        );
        return $sideblock;
    }
    return null;
}

function ssopeer_sideblock() {
    global $USER;

    if (get_config('enablenetworking')) {
        require_once(get_config('docroot') .'api/xmlrpc/lib.php');
        $ssopeers = get_service_providers($USER->authinstance);
        $sideblock = array(
            'name'   => 'ssopeers',
            'weight' => 1,
            'data'   => $ssopeers,
            'template' => 'sideblocks/ssopeers.tpl',
            'visible' => ($USER->is_logged_in() && $ssopeers),
        );
        return $sideblock;
    }
    return null;
}

/**
 * Site-level sidebar menu (list of links)
 * There is no admin files table yet so just get the urls.
 * @return $menu a data structure containing the site menu
 */
function site_menu() {
    global $USER;

    if (!get_config('installed') || in_admin_section()) {
        return null;
    }
    $menu = array();
    $public = $loggedin = false;
    if ($menuitems = get_records_array('site_menu','public',(int) !$USER->is_logged_in(), 'displayorder')) {
        foreach ($menuitems as $i) {
            if ($i->public) {
                $public = true;
            }
            if (!$i->public) {
                $loggedin = true;
            }
            if ($i->url) {
                $safeurl = sanitize_url($i->url);
                if ($safeurl != '') {
                    $menu[] = array('name' => $i->title,
                                    'link' => $safeurl);
                }
            }
            else if ($i->file) {
                $menu[] = array('name' => $i->title,
                                'link' => get_config('wwwroot') . 'artefact/file/download.php?file=' . $i->file);
            }
        }
    }

    $sideblock = array(
        'name' => 'linksandresources',
        'weight' => 10,
        'data' => $menu,
        'smarty' => array('SITEMENU' => $menu),
        'visible' => ($loggedin && $USER->is_logged_in() && !in_admin_section()) || ($public && !$USER->is_logged_in()),
        'template' => 'sideblocks/linksandresources.tpl',
    );
    return $sideblock;
}

/**
 * Returns the list of site content pages
 * @return array of names
 */
function site_content_pages() {
    return array('about', 'home', 'loggedouthome');
}

/**
 * Returns the list of site versioned content pages
 * @return array of names
 */
function site_content_version_pages() {
    return array('privacy', 'termsandconditions');
}

function get_site_page_content($pagename) {
    global $USER;
    $institution = $USER->sitepages_institutionname_by_theme($pagename);

    // try to get the content for this institution and if it fails try to get default site information
    // first check to see if the db upgrade has been run so the institution column exists
    if (get_config('version') >= '2014010801') {
        if ($pagedata = get_record('site_content', 'name', $pagename, 'institution', $institution)) {
            return $pagedata->content;
        }
        else if ($defaultpagedata = get_record('site_content', 'name', $pagename, 'institution', 'mahara')) {
            return $defaultpagedata->content;
        }
        return get_string('sitecontentnotfound', 'mahara', get_string($pagename, $institution));
    }
    else {
        if ($pagedata = get_record('site_content', 'name', $pagename)) {
            return $pagedata->content;
        }
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
 * Builds the pieform for the search field in the page header
 */
function header_search_form() {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    return call_static_method(
        generate_class_name('search', $plugin),
        'header_search_form'
    );
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

    }
    else {
        log_warn('Warning: Could not find any of these web server variables: $REQUEST_URI, $PHP_SELF, $SCRIPT_NAME or $URL');
        return false;
    }
}

/**
 * Get the requested servername in preference to the host in the configured
 * wwwroot.  Usually the same unless some parts of the site are at subdomains.
 *
 * @return string
 */
function get_requested_host_name() {
    global $CFG;

    $hostname = false;
    if (false === $hostname && !empty($_SERVER['SERVER_NAME'])) {
        $hostname = $_SERVER['SERVER_NAME'];
    }
    if (false === $hostname && !empty($_ENV['SERVER_NAME'])) {
        $hostname = $_ENV['SERVER_NAME'];
    }
    if (false === $hostname && !empty($_SERVER['HTTP_HOST'])) {
        $hostname = $_SERVER['HTTP_HOST'];
    }
    if (false === $hostname && !empty($_ENV['HTTP_HOST'])) {
        $hostname = $_ENV['HTTP_HOST'];
    }
    if (false === $hostname && !empty($CFG->wwwroot)) {
        $url = parse_url($CFG->wwwroot);
        if (!empty($url['host'])) {
            $hostname = $url['host'];
        }
    }

    if (false === $hostname) {
        log_warn('Warning: could not find the name of this server!');
        return false;
    }
    else {
        $hostname = strtolower($hostname);
        // Because the hostname can be user provided data (from the HTTP request), we
        // should whitelist it.
        if (!preg_match(
                '/^([a-z0-9]|[a-z0-9][a-z0-9-]*[a-z0-9])(\\.([a-z0-9]|[a-z0-9][a-z0-9-]*[a-z0-9]))*$/',
                $hostname
            )
        ) {
            log_warn('Warning: invalid hostname found in get_requested_host_name.');
            return false;
        }

        return $hostname;
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

    if (!$hostname = get_requested_host_name()) {
        return false;
    }

    if (!empty($url['port'])) {
        $hostname .= ':'.$url['port'];
    } else if (!empty($_SERVER['SERVER_PORT'])) {
        // SSL proxy could be on a random port and we don't want it to appear in URL.
        if (get_config('sslproxy')) {
            $_SERVER['SERVER_PORT'] = '443';
        }

        if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $hostname .= ':'.$_SERVER['SERVER_PORT'];
        }
    }

    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
    }
    else if (isset($_SERVER['SERVER_PORT'])) { # Apache2 does not export $_SERVER['HTTPS']
        $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    }
    else {
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
 * Get query string from url
 *
 * Takes in a URL and returns the querystring portion
 * or returns $_SERVER['QUERY_STRING']) if set
 *
 * @param string $url the url which may have a query string attached
 * @return string
 */
function get_querystring($url = null) {

    if (!empty($url) && $commapos = strpos($url, '?')) {
        return substr($url, $commapos + 1);
    }
    else if (!empty($_SERVER['QUERY_STRING'])) {
        return $_SERVER['QUERY_STRING'];
    }
    else {
        return '';
    }
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
    }
    else {
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
 * Get the list of custom filters to be used in HTMLPurifier
 * @return array
 */
function get_htmlpurifier_custom_filters() {
    $customfilters = array();
    if (get_config('filters')) {
        foreach (unserialize(get_config('filters')) as $filter) {
            // These filters are no longer necessary and have been removed
            $builtinfilters = array('YouTube', 'TeacherTube', 'SlideShare', 'SciVee', 'GoogleVideo');

            if (!in_array($filter->file, $builtinfilters)) {
                include_once(get_config('libroot') . 'htmlpurifiercustom/' . $filter->file . '.php');
                $classname = 'HTMLPurifier_Filter_' . $filter->file;
                if (class_exists($classname)) {
                    $customfilters[] = new $classname();
                }
            }
        }
    }
    return $customfilters;
}

/**
 * Given raw html (eg typed in by a user), this function cleans it up
 * and removes any nasty tags that could mess up pages.
 *
 * NOTE: The HTMLPurifier config is cached. You'll need to bump $CFG->cacheversion
 * to clear the cache. (The easiest way to do that is to bump htdocs/lib/version.php)
 *
 * @param string $text The text to be cleaned
 * @param boolean $xhtml HTML 4.01 will be used for all of mahara, except very special cases (eg leap2a exports)
 * @return string The cleaned up text
 */
function clean_html($text, $xhtml=false) {
    require_once('htmlpurifier/HTMLPurifier.auto.php');
    $config = HTMLPurifier_Config::createDefault();

    // Uncomment this line to disable the cache during debugging
    // $config->set('Cache.DefinitionImpl', null);

    $config->set('HTML.DefinitionID', 'Mahara customisations to default config');
    $config->set('HTML.DefinitionRev', get_config('cacheversion', 0));

    $config->set('Cache.SerializerPermissions', get_config('directorypermissions'));
    $config->set('Cache.SerializerPath', get_config('dataroot') . 'htmlpurifier');
    if (empty($xhtml)) {
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
    }
    else {
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
    }
    $config->set('AutoFormat.Linkify', true);

    if (get_config('disableexternalresources')) {
        $config->set('URI.DisableExternalResources', true);
        $config->set('URI.Host', get_config('wwwhost'));
    }

    // Permit embedding contents from other sites
    $config->set('HTML.SafeEmbed', true);
    $config->set('HTML.SafeObject', true);
    $config->set('Output.FlashCompat', true);
    if ($iframeregexp = get_config('iframeregexp')) {
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', $iframeregexp);
    }

    // Allow namespaced IDs
    // see http://htmlpurifier.org/docs/enduser-id.html
    $config->set('Attr.EnableID', true);
    $config->set('Attr.IDPrefix', 'user_');

    // Allow base64 images via the 'data' option
    // need to set all the allowed schemes for this to work
    $config->set('URI.AllowedSchemes', array('http' => true,
                                             'https' => true,
                                             'mailto' => true,
                                             'ftp' => true,
                                             'nntp' => true,
                                             'news' => true,
                                             'tel' => true,
                                             'data' => true));

    $customfilters = get_htmlpurifier_custom_filters();
    if (!empty($customfilters)) {
        $config->set('Filter.Custom', $customfilters);
    }

    require_once('htmlpurifiercustom/MixedContent.php');
    $uri = $config->getDefinition('URI');
    $uri->addFilter(new HTMLPurifier_URIFilter_MixedContent(), $config);

    if ($def = $config->maybeGetRawHTMLDefinition()) {
        $def->addAttribute('a', 'target', 'Enum#_blank,_self');
        # Allow iframes with custom attributes such as fullscreen
        # This overrides lib/htmlpurifier/HTMLPurifier/HTMLModule/Iframe.php
        $def->addElement(
            'iframe',
            'Inline',
            'Flow',
            'Common',
            array(
                'src' => 'URI#embedded',
                'width' => 'Length',
                'height' => 'Length',
                'name' => 'ID',
                'scrolling' => 'Enum#yes,no,auto',
                'frameborder' => 'Enum#0,1',
                'allowfullscreen' => 'Enum#,0,1',
                'webkitallowfullscreen' => 'Enum#,0,1',
                'mozallowfullscreen' => 'Enum#,0,1',
                'longdesc' => 'URI',
                'marginheight' => 'Pixels',
                'marginwidth' => 'Pixels',
            )
        );
        // allow the tags used with image map to be rendered
        // see http://htmlpurifier.org/phorum/read.php?3,5046
        $def->addAttribute('img', 'usemap', 'CDATA');
        // Add map tag
        $map = $def->addElement(
            'map',
            'Block',
            'Flow',
            'Common',
            array(
                'name' => 'CDATA',
            )
        );
        $map->excludes = array('map' => true);

        // Add area tag
        $area = $def->addElement(
            'area',
            'Block',
            'Empty',
            'Common',
            array(
                'name' => 'CDATA',
                'alt' => 'Text',
                'coords' => 'CDATA',
                'accesskey' => 'Character',
                'nohref' => new HTMLPurifier_AttrDef_Enum(array('nohref')),
                'href' => 'URI',
                'shape' => new HTMLPurifier_AttrDef_Enum(array('rect','circle','poly','default')),
                'tabindex' => 'Number',
            )
        );
        $area->excludes = array('area' => true);
        // Allow button tags
        $def->addElement('button', 'Inline', 'Inline', 'Common');
    }

    $purifier = new HTMLPurifier($config);
    return $purifier->purify($text);
}

/**
 * Like clean_html(), but for CSS stylesheets! (May not be secure for CSS directly
 * in an HTML document a la <style>.)
 *
 * Much of the code in this function was taken from the sample code in this post:
 * http://stackoverflow.com/questions/3241616/sanitize-user-defined-css-in-php#5209050
 *
 * NOTE: The HTMLPurifier config is cached. You'll need to bump $CFG->cacheversion
 * to clear the cache. (The easiest way to do that is to bump htdocs/lib/version.php)
 *
 * @param string $input_css
 * @param string $preserve_css, if turns on the CSS comments will be preserved
 * @return string The cleaned CSS
 */
function clean_css($input_css, $preserve_css=false) {
    require_once('htmlpurifier/HTMLPurifier.auto.php');
    require_once('csstidy/class.csstidy.php');

    // Create a new configuration object
    $config = HTMLPurifier_Config::createDefault();

    // Uncomment this line to disable the cache during debugging
    // $config->set('Cache.DefinitionImpl', null);

    $config->set('HTML.DefinitionID', 'Mahara customisations to default config for CSS');
    $config->set('HTML.DefinitionRev', get_config('cacheversion', 0));
    $config->set('CSS.DefinitionRev', get_config('cacheversion', 0));

    $config->set('Cache.SerializerPermissions', get_config('directorypermissions'));
    $config->set('Cache.SerializerPath', get_config('dataroot') . 'htmlpurifier');

    $config->set('Filter.ExtractStyleBlocks', true);
    $config->set('Filter.ExtractStyleBlocks.PreserveCSS', $preserve_css);

    // Prevents "&<>" from being escaped. Escaping those is helpful
    // if you're dealing with CSS declarations within an HTML document, but is
    // not necessary for CSS in isolation.
    $config->set('Filter.ExtractStyleBlocks.Escaping', false);

    if (get_config('disableexternalresources')) {
        $config->set('URI.DisableExternalResources', true);
    }

    // Create a new purifier instance
    $purifier = new HTMLPurifier($config);

    // Wrap our CSS in style tags and pass to purifier.
    // we're not actually interested in the html response though
    $html = $purifier->purify('<style>'.$input_css.'</style>');

    // The "style" blocks are stored seperately
    $output_css = $purifier->context->get('StyleBlocks');

    // Get the first style block
    if (is_array($output_css) && count($output_css)) {
        return $output_css[0];
    }
    return '';
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
        $smarty->assign('htmlremovedmessage', get_string('htmlremovedmessage', 'artefact.file', hsc($filename), profile_url((int) $params['owner']), hsc(display_name($params['owner']))));
    }
    else {
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
 * @param bool $usenl2br if false, only do HTML-escapes (default true)
 * @return string
 */
function str_shorten_html($str, $maxlen=100, $truncate=false, $newlines=true, $usenl2br=true) {
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

    $str = hsc($str);
    if ($usenl2br) {
        $str = nl2br($str);
    }
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
 * - url: The base URL to use for all links (it should not contain special characters)
 * - count: The total number of results to paginate for
 * - setlimit: toggle variable for enabling/disabling limit dropbox, default value = false
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
 * - numbersincludeprevnext: The number of pagelinks, adjacent the the current page,
 *   to include per side
 * - jumplinks: The maximum number of page jump links to have between first- and current-,
     and current- and last page
 * - resultcounttextsingular: The text to use for 'result'
 * - resultcounttextplural: The text to use for 'results'
 * - limittext: The text to use for the limitoption, e.g. "Max items per page" or "Page size"
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
    $limitoptions = array(1, 10, 20, 50, 100, 500);
    // Bail if the required attributes are not present
    $required = array('url', 'count', 'limit', 'offset');
    foreach ($required as $option) {
        if (!isset($params[$option])) {
            throw new ParameterException('You must supply option "' . $option . '" to build_pagination');
        }
    }

    if (isset($params['setlimit']) && $params['setlimit']) {
        if (!in_array($params['limit'], $limitoptions)) {
            $params['limit'] = 10;
        }
        if (!isset($params['limittext'])) {
            $params['limittext'] = get_string('maxitemsperpage1');
        }
    }
    else {
        $params['setlimit'] = false;
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
    if ($params['limit']) {
        $params['offset'] -= $params['offset'] % $params['limit'];
    }

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
        $params['numbersincludeprevnext'] = 1;
    }
    else {
        $params['numbersincludeprevnext'] = (int) $params['numbersincludeprevnext'];
    }

    if (!isset($params['extradata'])) {
        $params['extradata'] = null;
    }

    // Begin building the output
    $output = '<div id="' . $params['id'] . '" class="pagination-wrapper';
    if (isset($params['class'])) {
        $output .= ' ' . hsc($params['class']);
    }
    $output .= '">';
    // Output the count of results
    $resultsstr = ($params['count'] == 1) ? $params['resultcounttextsingular'] : $params['resultcounttextplural'];
    if($params['count'] > 0){
        $output .= '<div class="lead text-small results float-right">' . $params['count'] . ' ' . $resultsstr . '</div>';
    }

    if ($params['limit'] && ($params['limit'] < $params['count'])) {
        $output .= '<ul class="pagination pagination-sm">';
        $pages = ceil($params['count'] / $params['limit']);
        $page = $params['offset'] / $params['limit'];

        $last = $pages - 1;
        if (!empty($params['lastpage'])) {
            $page = $last;
        }
        $prev = max(0, $page - 1);
        $next = min($last, $page + 1);

        // Build a list of what pagenumbers will be put between the previous/next links
        $pagenumbers = array();

        // First page
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = 0;
        }

        $maxjumplinks = isset($params['jumplinks']) ? (int) $params['jumplinks'] : 0;

        // Jump pages between first page and current page
        $betweencount = $page;
        $jumplinks = $pages ? round($maxjumplinks * ($betweencount / $pages)) : 0;
        $jumpcount = $jumplinks ? round($betweencount / ($jumplinks + 1)) : 0;
        $gapcount = 1;
        if ($jumpcount > 1) {
            for ($bc = 1; $bc < $betweencount; $bc++) {
                if ($gapcount > $jumpcount) {
                    $pagenumbers[] = $bc;
                    $gapcount = 1;
                }
                $gapcount++;
            }
        }

        // Current page with adjacent prev and next pages
        if ($params['numbersincludeprevnext'] > 0) {
            for ($i = 1; $i <= $params['numbersincludeprevnext']; $i++) {
                $prevlink = $page - $i;
                if ($prevlink < 0) {
                    break;
                }
                $pagenumbers[] = $prevlink;
            }
            unset($prevlink);
        }
        $pagenumbers[] = $page;
        if ($params['numbersincludeprevnext'] > 0) {
            for ($i = 1; $i <= $params['numbersincludeprevnext']; $i++) {
                $nextlink = $page + $i;
                if ($nextlink > $last) {
                    break;
                }
                $pagenumbers[] = $nextlink;
            }
        }

        // Jump pages between current and last
        $betweencount = $pages - $page;
        $jumplinks = $pages ? round($maxjumplinks * ($betweencount / $pages)) : 0;
        $jumpcount = $jumplinks ? round($betweencount / ($jumplinks + 1)) : 0;
        $gapcount = 1;
        if ($jumpcount > 1) {
            for ($bc = $page; $bc < $last; $bc++) {
                if ($gapcount > $jumpcount) {
                    $pagenumbers[] = $bc;
                    $gapcount = 1;
                }
                $gapcount++;
            }
        }

        // Last page
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = $last;
        }
        $pagenumbers = array_unique($pagenumbers);
        sort($pagenumbers);

        // Build the first/previous links
        $isfirst = $page == 0;

        $output .= build_pagination_pagelink(
                    '',
                    '&laquo;',
                    get_string('prevpage'),
                    $isfirst,
                    $params['url'],
                    $params['setlimit'],
                    $params['limit'],
                    $params['limit'] * $prev,
                    $params['offsetname']
                  );

        // Build the pagenumbers in the middle
        foreach ($pagenumbers as $k => $i) {

             // add ellipsis if pages skipped
            $text = $i + 1;
            if ($k != 0 && $prevpagenum < $i - 1) {
                $text = '<span class="metadata d-none d-md-inline-block">...</span>' . ($i + 1);
            }

            if ($i == $page) {
                $output .= build_pagination_pagelink('active', $text, '', true);

            }
            else {

                $output .= build_pagination_pagelink(
                    '',
                    $text,
                    '',
                    false,
                    $params['url'],
                    $params['setlimit'],
                    $params['limit'],
                    $params['limit'] * $i,
                    $params['offsetname']
                );
            }
            $prevpagenum = $i;
        }



        // Build the next/last links
        $islast = $page == $last;
        $output .= build_pagination_pagelink(
            '',
            ' &raquo;',
            get_string('nextpage'),
            $islast,
            $params['url'],
            $params['setlimit'],
            $params['limit'],
            $params['limit'] * $next,
            $params['offsetname']
        );
        $output .= '</ul>';
    }
    // Build limitoptions dropbox if results are more than 10 (minimum dropbox pagination) and that we are not in the block editor screen
    if ($params['setlimit'] && $params['count'] > 10 && (!isset($params['editing']) || $params['editing'] === false)) {
        $strlimitoptions = array();
        $limit = $params['limit'];
        for ($i = 0; $i < count($limitoptions); $i++) {
            if ($limit == $limitoptions[$i]) {
                $strlimitoptions[] = "<option value = '$limit' selected='selected'> $limit </option>";
            }
            else {
                $strlimitoptions[] = "<option value = '$limitoptions[$i]'> $limitoptions[$i] </option>";
            }
        }
        $output .= '<form class="form-pagination js-pagination form-inline pagination-page-limit dropdown" action="' . hsc($params['url']) . '" method="POST">
            <label for="setlimitselect" class="set-limit"> ' . $params['limittext'] . ' </label>' .
            '<span class="picker input-sm"><select id="setlimitselect" class="js-pagination input-sm select form-control" name="limit"> '.
                join(' ', $strlimitoptions) .
            '</select></span>
            <input class="currentoffset" type="hidden" name="' . $params['offsetname'] . '" value="' . $params['offset'] . '"/>
            <input class="pagination js-hidden d-none" type="submit" name="submit" value="' . get_string('change') . '"/>
        </form>';
    }
    // if $params['count'] is less than 10 add the setlimitselect as a hidden field so that elasticsearch js can access it
    else if ($params['setlimit']) {
        $output .= '<input type="hidden" id="setlimitselect" name="limit" value="' . $params['limit'] . '">';
    }

    // Work out what javascript we need for the paginator
    $js = '';
    $id = json_encode($params['id']);
    if (isset($params['jsonscript']) && isset($params['datatable'])) {
        $paginator_js = hsc(get_config('wwwroot') . 'js/paginator.js');
        $datatable    = json_encode($params['datatable']);
        if (!empty($params['searchresultsheading'])) {
            $heading  = json_encode($params['searchresultsheading']);
        }
        else {
            $heading  = 'null';
        }
        $jsonscript   = json_encode($params['jsonscript']);
        $extradata    = json_encode($params['extradata']);
        $js .= "new Paginator($id, $datatable, $heading, $jsonscript, $extradata);";
    }
    else {
        $extradata = null;
        $js .= "new Paginator($id, null, null, null, null);";
    }

    // Close the container div
    $output .= '</div>';

    return array('html' => $output, 'javascript' => $js);

}

/**
 * Builds 'Show more' pagination for HTML display.
 *
 * This pagination is for adding a 'Show more' button when more results exist
 * rather than 'paging' the results so the page only loads certain amount
 * at a time but the user can see all loaded results in one view.
 *
 * This function takes one array that contains the options to configure the
 * pagination.
 * Required include:
 * - jsonscript: Relative path to json script to return subsequent results
 * - count: The total number of results to paginate for
 * - limit: How many to show per fetch from db
 * - offset: At which result to begin fetch for results
 * - orderby: What order the results will be returned in
 * - databutton: The ID of the 'Show more' button
 *
 * Optional include:
 * - group: Group id the pagination is for
 * - institution: Institution name the pagination is for
 */
function build_showmore_pagination($params) {
    // Bail if the required attributes are not present
    $required = array('jsonscript', 'count', 'limit', 'offset', 'orderby', 'databutton');
    foreach ($required as $option) {
        if (!isset($params[$option])) {
            throw new ParameterException('You must supply option "' . $option . '" to build_pagination');
        }
    }
    $output = $js = '';
    if ((int) $params['count'] > ((int) $params['offset'] + (int) $params['limit'])) {
        // Need to add 'showmore' button
        $output  = '<div class="showmore">' . "\n";
        $output .= '    <div id="' . $params['databutton'] . '" class="btn btn-secondary"';
        $output .= ' data-orderby="' . $params['orderby'] . '"';
        $output .= ' data-offset="' . ((int) $params['offset'] + (int) $params['limit']) . '"';
        $output .= ' data-group="' . (isset($params['group']) ? $params['group'] : '') . '"';
        $output .= ' data-jsonscript="' . $params['jsonscript'] . '"';
        $output .= ' data-institution="' . (isset($params['institution']) ? $params['institution'] : '') . '"';
        $output .= ' tabindex="0">';
        $output .= get_string('showmore', 'mahara') . '</div>' . "\n";
        $output .= '</div>';

        $js  = 'jQuery("#' . $params['databutton'] . '").on("click", function() {';
        $js .= '    pagination_showmore(jQuery(this));';
        $js .= '});' . "\n";

        $js .= 'jQuery("#' . $params['databutton'] . '").on("keydown", function(e) {';
        $js .= '    if (e.keyCode == $j.ui.keyCode.SPACE || e.keyCode == $j.ui.keyCode.ENTER) {';
        $js .= '        pagination_showmore(jQuery(this));';
        $js .= '    }';
        $js .= '});' . "\n";
    }

    return array('html' => $output, 'javascript' => $js);
}

/**
 * Used by build_pagination to build individual links. Shouldn't be used
 * elsewhere.
 *
 * @param $class String
 * @param $text String
 * @param $title String
 * @param $disabled Boolean (optional)
 * @param $url String
 * @param $setlimit Int
 * @param $limit Int
 * @param $offset Int
 * @param $offsetname String (optional)
 */
function build_pagination_pagelink($class, $text, $title, $disabled=false, $url=false, $setlimit=false, $limit=false, $offset=false,  $offsetname='offset') {

    if ($url) {
        $url = (false === strpos($url, '?')) ? $url . '?' : $url . '&';
        $url .= "$offsetname=$offset";

        if ($setlimit) {
            $url .= '&' . "setlimit=$setlimit";
            $url .= '&' . "limit=$limit";
        }
    }


    $result = "<li class='page-item $class'>";

    if (!empty($title)) {
        $text .= '<span class="sr-only">' . $title . '</span>';
    }

    if ($disabled) {
        $result .= '<span class="page-link">';
        $result .= $text;
        $result .= '</span>';
    } else {
        $result .= '<a class="page-link" href="' . hsc($url) . '" title="' . $title . '">';
        $result .= $text;
        $result .= '</a>';
    }

    $result .=  '</li>';

    return $result;
}

function mahara_http_request($config, $quiet=false) {
    $ch = curl_init();

    // standard curl_setopt stuff; configs passed to the function can override these
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (!ini_get('open_basedir')) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    }

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
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
        }
    }

    $result = new stdClass();
    $result->data = curl_exec($ch);
    $result->info = curl_getinfo($ch);
    $result->error = curl_error($ch);
    $result->errno = curl_errno($ch);

    if ($result->errno) {
        if ($quiet) {
            // When doing something unimportant like fetching rss feeds, some errors should not pollute the logs.
            $dontcare = array(
                CURLE_COULDNT_RESOLVE_HOST, CURLE_COULDNT_CONNECT, CURLE_PARTIAL_FILE, CURLE_OPERATION_TIMEOUTED,
                CURLE_GOT_NOTHING,
            );
            $quiet = in_array($result->errno, $dontcare);
        }
        if (!$quiet) {
            log_warn('Curl error: ' . $result->errno . ': ' . $result->error);
        }
    }

    curl_close($ch);

    return $result;
}

/**
 * Fetch the true full url from a shorthand url by getting
 * the location from the redirected header information.
 *
 * @param   string $url    The shorthand url eg https://goo.gl/maps/pZTiA
 * @param   bool   $quiet  To record errors in the logs
 *
 * @return  object  $result Contains the short url, full url, the headers, and any errors
 */
function mahara_shorturl_request($url, $quiet=false) {
    $ch = curl_init($url);

    // standard curl_setopt stuff; configs passed to the function can override these
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);

    $result = new stdClass();
    $result->shorturl = $url;
    $result->data = curl_exec($ch);
    $result->error = curl_error($ch);
    $result->errno = curl_errno($ch);

    if ($result->errno) {
        if ($quiet) {
            // When doing something unimportant like fetching rss feeds, some errors should not pollute the logs.
            $dontcare = array(
                CURLE_COULDNT_RESOLVE_HOST, CURLE_COULDNT_CONNECT, CURLE_PARTIAL_FILE, CURLE_OPERATION_TIMEOUTED,
                CURLE_GOT_NOTHING,
            );
            $quiet = in_array($result->errno, $dontcare);
        }
        if (!$quiet) {
            log_warn('Curl error: ' . $result->errno . ': ' . $result->error);
        }
    }

    curl_close($ch);

    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $result->data)); // Parse information
    $result->fullurl = false;
    foreach ($fields as $field) {
        if (strpos($field, 'Location') !== false) {
            $result->fullurl = str_replace('Location: ', '', $field);
        }
    }

    return $result;
}

/**
 * Generates the language selection form, for logged-out users.
 * (And though Pieform magic, also handles submission of that form.)
 *
 * @return string      HTML of language select form
 */
function language_select_form() {
    global $SESSION;

    $languageform = '';
    $languages = get_languages();

    if (count($languages) > 1) {

        $languages = array_merge(array('default' => get_string('sitedefault', 'admin') . ' (' .
            get_string_from_language(get_config('lang'), 'thislanguage') . ')'), $languages);

        $languageform = pieform(array(
            'name'                => 'languageselect',
            'renderer'            => 'div',
            'class'               => 'form-inline with-label-widthauto',
            'successcallback'      => 'language_select_form_submit',
            'elements'            => array(
                'inputgroup' => array(
                    'type' => 'fieldset',
                    'class' => 'input-group',
                    'elements' => array(
                        'lang' => array(
                            'type' => 'select',
                            'title' => get_string('language') . ':',
                            'hiddenlabel' => true,
                            'options' => $languages,
                            'defaultvalue' => $SESSION->get('lang') ? $SESSION->get('lang') : 'default',
                            'rules' => array('required' => true),
                        ),
                        'changelang' => array(
                            'type' => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-secondary input-group-append',
                            'value' => get_string('change'),
                        )
                    )
                )
            ),
        ));
    }
    return $languageform;
}

/**
 * Submission method for the language selection form
 *
 * @param object $form
 * @param array $data
 */
function language_select_form_submit($form, $data) {
    global $SESSION;
    // Pieforms will have already validated that $lang is an installed language or "default"
    $SESSION->set('lang', $data['lang']);
    redirect(get_relative_script_path());
}


/**
 * Sanitises URIs provided before displaying them to the world, as well as checking they are of
 * appropriate protocols and complete.
 *
 *  @return string    Either an empty string if supplied URI fails tests, or the supplied URI verbatim
 */
function sanitize_url($url) {

    $parsedurl = parse_url($url);
    if (empty($parsedurl['scheme'])) {
        if (!empty($parsedurl['path'])) {
            $url = get_config('wwwroot') . ltrim($url, '/');
            $parsedurl = parse_url($url);
        }
        else {
            return '';
        }
    }
    // Make sure the URL starts with a valid protocol (or "//", indicating that it's protocol-relative)
    if (
            !(
                    in_array($parsedurl['scheme'], array('https', 'http', 'ftp', 'mailto'))
                    || preg_match('#^//[a-zA-Z0-9]#', $url) === 1
            )
    ) {
        return '';
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }
    return $url;
}

/**
 * Sanitises header text per rfc5322
 *
 *  @return string    A string with undesired characters filtered out
 */
function clean_email_headers($headertext) {

    $decoloned = str_replace(':', '', $headertext);
    $filtered = filter_var($decoloned, FILTER_SANITIZE_STRING, array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_STRIP_HIGH));
    return substr($filtered, 0, 100);

}

function favicon_display_url($host) {
    $url = sprintf(get_config('favicondisplay'), $host);
    if (is_https()) {
        $url = str_replace('http://', 'https://', $url);
    }
    return $url;
}

/**
 * Given an user object, return raw urlid string that will be used in generate_urlid().
 * to make a clean url.
 *
 * @param object  $user     An object containing $username
 *                                               $firstname
 *                                               $lastname
 *                                               $preferredname (optional)
 *
 * @return string    A raw urlid string
 */
function get_raw_user_urlid($user) {
    if (!get_config('nousernames')) {
        $urlid = $user->username;
    }
    else {
        $urlid = display_default_name($user);
    }
    return $urlid;
}

/**
 * Given an arbitrary string, generate a string containing only the allowed
 * characters for use in a clean url.
 *
 * @param string  $dirty string containing invalid or undesirable url characters
 * @param mixed   $default an integer id or clean string to use as the default
 * @param integer $minlength
 * @param integer $maxlength
 *
 * @return string    A string of the specified length containing only valid characters
 */
function generate_urlid($dirty, $default, $minlength=3, $maxlength=100) {
    $charset = get_config('cleanurlcharset');
    if ($charset != 'ASCII' || preg_match('/[^\x00-\x7F]/', $dirty)) {
        $dirty = iconv('UTF-8', $charset . '//TRANSLIT', $dirty);
    }
    $dirty = preg_replace(get_config('cleanurlinvalidcharacters'), '-', $dirty);
    $s = substr(strtolower(trim($dirty, '-')), 0, $maxlength);

    // If the string is too short, use the default, padding with zeros if necessary
    $length = strlen($s);
    if ($length < $minlength) {
        if (is_numeric($default)) {
            $format = '%0' . $minlength . 'd';
            $default = sprintf($format, (int) $default);
        }
        if ($length > 0) {
            $default .= '-' . $s;
        }
        $s = $default;
    }
    return $s;
}

/**
 * Sorts an array by one of the value fields
 *
 * @param array  $data an array of arrays
 * @param string $sort a key field value of second tier array
 * @param string $direction the direction of the sort
 */
function sorttablebycolumn(&$data, $sort, $direction) {
    global $sortvalue;
    $sortvalue = $sort;
    if ($direction == 'desc') {
        usort($data, 'sorttablearraydesc');    }
    else {
        usort($data, 'sorttablearrayasc');
    }

}

/**
 * Compare function for sorttablebycolumn()
 * Sorts ascending.
 */
function sorttablearrayasc($a, $b) {
    global $sortvalue;
    if (is_string($a[$sortvalue])) {
        return strcmp(strtolower($a[$sortvalue]), strtolower($b[$sortvalue]));
    }
    return ($a[$sortvalue] < $b[$sortvalue]) ? -1 : 1;
}

/**
 * Compare function for sorttablebycolumn()
 * Sorts descending
 */
function sorttablearraydesc($a, $b) {
    global $sortvalue;
    if (is_string($a[$sortvalue])) {
        return strcmp(strtolower($b[$sortvalue]), strtolower($a[$sortvalue]));
    }
    return ($b[$sortvalue] < $a[$sortvalue]) ? -1 : 1;
}

/**
 * Add version number to url
 * This allows auto refreshing of cache when upgrading
 * or updating Mahara to different version
 */
function append_version_number($urls) {
    if (is_array($urls)) {
        $formattedurls = array();
        foreach ($urls as $url) {
            if (preg_match('/\?/',$url)) {
                $url .= '&v=' . get_config('cacheversion', 0);
            }
            else {
                $url .= '?v=' . get_config('cacheversion', 0);
            }
            $formattedurls[] = $url;
        }
        return $formattedurls;
    }
    if (preg_match('/\?/',$urls)) {
        $urls .= '&v=' . get_config('cacheversion', 0);
    }
    else {
        $urls .= '?v=' . get_config('cacheversion', 0);
    }
    return $urls;
}

/**
 * Escape a string so that it's suitable to be used as a CSS quote-enclosed string
 * If it's single-quoted, preface single-quotes with a backslash. If it's double-quoted,
 * preface double-quotes with a backslash. Preface non-escaping backslashes with a
 * backslash. Remove newlines.
 * @param string $string The string to escape
 * @param bool $singlequote True to escape for single quotes, False to escape for double
 * @return string
 */
function escape_css_string($string, $singlequote=true) {
    if ($singlequote) {
        $delim = "'";
    }
    else {
        $delim = '"';
    }
    return str_replace(
        array('\\', "\n", $delim),
        array('\\\\', '', "\\$delim"),
        $string
    );
}

/**
 * Indicates whether a particular user can use skins on their pages or not. This is in
 * lib/web.php instead of lib/skin.php so that we can use it while generating the main nav.

 * @param int $userid The Id of the user to check. Null checks the current user.
 * @param bool $managesiteskin = true if admins try to manage the site skin
 * @param bool $issiteview = true if admins try to use skins for site views
 * @return bool
 */
function can_use_skins($userid = null, $managesiteskin=false, $issiteview=false) {
    global $USER;

    if (!get_config('skins')) {
        return false;
    }

    // Site Admins can access site skin
    if ($USER->get('admin') && ($managesiteskin || $issiteview)) {
        return true;
    }

    // A user can belong to multiple institutions. If any of their institutions allow it, then
    // let them use skins!
    $results = get_configs_user_institutions('skins', $userid);
    foreach ($results as $r) {
        if ($r) {
            return true;
        }
    }
    return false;
}

/**
 * Display image icon based on name
 *
 * @param string $type  Type of icon image to show
 * @param string $id    Optional id to add to the image
 * @param string $title Optional title string to add to the image
 *
 * @return string    An <img> tag of the icon we want
 */
function display_icon($type, $id = false, $title = "") {
    global $THEME;
    switch ($type) {
        case 'on':
        case 'yes':
        case 'success':
        case 'true':
        case 'enabled':
        case 'enabledspecific':
            $image = 'icon icon-lg icon-check text-success ';
            break;
        case 'off':
        case 'no':
        case 'fail':
        case 'false':
        case 'disabled':
        case 'disabledspecific':
            $image = 'icon icon-lg icon-times text-danger ';
            break;
    }
    $title = empty($title) ? get_string($type) : hsc($title);
    $html = '<span class="' . $image . 'displayicon" title="' . $title . '"';
    if ($id) {
        $html .= ' id="' . $id . '"';
    }
    $html .= '> </span>';
    return $html;
}

/**
 * Is the supplied URL valid
 * That is, can this Mahara reach/resolve the URL at the time
 * of checking. Useful if you are checking a url field in a form.
 *
 * Caution: Probably not want to use this function in a large loop situation.
 *
 * @param string    $url    The URL to check
 *
 * @return bool
 */
function is_valid_url($url) {
    $result = mahara_http_request(
        array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_CONNECTTIMEOUT => 2,
        ),
        true
    );
    if (!$result || $result->error || $result->info['http_code'] == 404) {
        return false;
    }
    return true;
}
