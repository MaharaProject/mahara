<?php

/*
 * Created by De Chiara Antonella
 * Eticeo Santé (http://eticeo.fr)
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editlayout');

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'layoutpreviewimage.php');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

$id = param_integer('id', false);
$new = param_boolean('new', false);

if ($new && $id === false) {
    // Use the site default portfolio page to create a new page
    // cribbed from createview_submit()
    $sitedefaultviewid = get_field('view', 'id', 'institution', 'mahara', 'template', View::SITE_TEMPLATE, 'type', 'portfolio');
    if (!empty($sitedefaultviewid)) {
        $artefactcopies = array();
        $values = array();
        $groupid = param_integer('group', 0);
        $institutionname = param_alphanum('institution', false);
        if (!empty($groupid)) {
            $values['group'] = $groupid;
        }
        else if (!empty($institutionname)) {
            $values['institution'] = $institutionname;
        }

        list($view, $template, $copystatus) = View::create_from_template($values, $sitedefaultviewid, null, true, false, $artefactcopies);
        if (isset($copystatus['quotaexceeded'])) {
            $SESSION->add_error_msg(get_string('viewcreatewouldexceedquota', 'view'));
            redirect(get_config('wwwroot') . 'view/index.php');
        }
    }
    else {
         throw new ConfigSanityException(get_string('viewtemplatenotfound', 'error'));
    }

    $goto = get_config('wwwroot') . 'view/editlayout.php?new=1&id=' . $view->get('id');
    if (!empty($values)) {
        $goto .= '&' . http_build_query($values);
    }
    redirect($goto);
}

$view = new View($id);
$viewid = $view->get('id'); // for tinymce editor
define('TITLE', $view->get('title'));
define('SUBSECTIONHEADING', TITLE);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

$issiteview = $view->get('institution') == 'mahara';
$issitetemplate = ($view->get('template') == View::SITE_TEMPLATE ? true : false);
$canedittitle = $view->can_edit_title();
$canuseskins = !$issitetemplate && can_use_skins(null, false, $issiteview);

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}

$group = $view->get('group');
$institution = $view->get('institution');
$view->set_edit_nav();
$view->set_user_theme();

// Clean urls are only available for portfolio views owned by groups or users who already
// have their own clean profiles or group homepages.
if ($urlallowed = get_config('cleanurls') && $view->get('type') == 'portfolio' && !$institution) {
    if ($group) {
        $groupdata = get_record('group', 'id', $group);
        if ($urlallowed = !is_null($groupdata->urlid) && strlen($groupdata->urlid)) {
            $cleanurlbase = group_homepage_url($groupdata) . '/';
        }
    }
    else {
        $userurlid = $USER->get('urlid');
        if ($urlallowed = !is_null($userurlid) && strlen($userurlid)) {
            $cleanurlbase = profile_url($USER) . '/';
        }
    }
}

if ($group && !group_within_edit_window($group)) {
    throw new AccessDeniedException();
}

$state = get_string('settings', 'view');

$pieformname = 'settings';
list($form, $inlinejavascript) = create_settings_pieform();

$javascript = array('jquery','js/jquery/jquery-ui/js/jquery-ui.min.js');
$stylesheets[] = '<link rel="stylesheet" type="text/css" href="' . append_version_number(get_config('wwwroot') . 'js/jquery/jquery-ui/css/smoothness/jquery-ui.min.css') . '">';

$smarty = smarty($javascript, $stylesheets, array('view' => array('Row', 'rownr')), array('sidebars' => false));

$smarty->assign('INLINEJAVASCRIPT', $inlinejavascript);
$smarty->assign('form', $form);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtype', $view->get('type'));
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('edittitle', $canedittitle);
$smarty->assign('canuseskins', $canuseskins);
$smarty->assign('displaylink', $view->get_url());
$smarty->assign('issiteview', $issiteview);
$smarty->assign('issitetemplate', $issitetemplate);
$smarty->assign('PAGEHEADING', $state);
$returnto = $view->get_return_to_url_and_title();
$smarty->assign('url', $returnto['url']);
$smarty->assign('title', $returnto['title']);

$smarty->display('view/editlayout.tpl');

function create_settings_pieform() {
    global $view, $pieformname, $issiteview, $issitetemplate,
    $canedittitle, $canuseskins;
    $inlinejavascript = '';

    //get elements for each section of the form
    $extrasettingformfields = array();
    if ($canedittitle) {
        $basicelements = get_basic_elements();
        list($advancedelements, $inlinejs) = get_advanced_elements();
        $inlinejavascript .= $inlinejs;
        $extrasettingformfields = array(
            'jsform'     => true,
            'jssuccesscallback' => 'settings_callback',
            'jserrorcallback'   => 'settings_callback',
        );
    }

    if ($canuseskins) {
        list($skinelements, $hiddenskinelements, $inlinejs) = get_skin_elements();
        $inlinejavascript .= $inlinejs;
        $advancedclasslast = '';
    }
    else {
        $advancedclasslast = 'last';
    }

    //visible elements of the sections
    $formelements = array();

    if ($canedittitle) {
        $formelements['basic'] = array(
            'type'        => 'fieldset',
            'class'       => 'first',
            'collapsible' => true,
            'collapsed'   => false,
            'legend'      => get_string('basics', 'view'),
            'elements'    => $basicelements
        );
        $formelements['advanced'] = array(
            'type'        => 'fieldset',
            'class'       =>  $advancedclasslast,
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('advanced', 'view'),
            'elements'    => $advancedelements
        );
    }

    if ($canuseskins) {
        $formelements['skin'] = array(
            'type' => 'fieldset',
            'class' => 'last',
            'collapsible' => true,
            'collapsed' => true,
            'legend' => get_string('skin', 'view'),
            'elements' => $skinelements,
        );
    }

    $formelements['submitform'] = array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('save'),
    );

    //hidden elements of the sections
    $hiddenelements = array(
        // hidden 4 all sections
        'id' => array(
            'type'  => 'hidden',
            'value' => $view->get('id'),
        ),
    );

    if ($canuseskins) {
        $hiddenelements = array_merge($hiddenelements, $hiddenskinelements);
    }

    $elements = array_merge($formelements, $hiddenelements);

    //main form
    $settingsform = array(
        'name'      => $pieformname,
        'method'     => 'post',
        'renderer'   => 'div',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'elements'   => $elements,
    );

    $settingsform = array_merge($settingsform, $extrasettingformfields);

    return array(pieform($settingsform), $inlinejavascript);
}

function get_basic_elements() {
    global $view, $urlallowed, $group, $institution, $USER;

    $formatstring = '%s (%s)';
    $ownerformatoptions = array(
        FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
        FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
        FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
    );

    $displayname = display_name($USER);
    if ($displayname !== '') {
        $ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('preferredname'), $displayname);
    }
    $studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
    if ($studentid !== '') {
        $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
    }

    $createtagsoptions = array();
    $typecast = is_postgres() ? '::varchar' : '';
    if ($selecttags = get_records_sql_array("
        SELECT (
            CASE
                WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                ELSE t.tag
            END) AS tag, t.resourcetype, t.id
        FROM {tag} t
        LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
        LEFT JOIN {institution} i ON i.name = t2.ownerid
        WHERE t.ownertype = ? AND t.ownerid = ?
        AND t.resourcetype IN ('artefact', 'blocktype')
        ORDER BY tag ASC", array('user', $USER->id))) {
        foreach ($selecttags as $k => $tag) {
            $createtagsoptions[$tag->tag] = $tag->tag;
        }
    }

    $elements = array(
        'title'       => array(
            'type'         => 'text',
            'title'        => get_string('title','view'),
            'defaultvalue' => $view->get('title'),
            'rules'        => array( 'required' => true ),
        ),
        'description' => array(
            'type'         => 'textarea',
            'title'        => get_string('description','view'),
            'rows'         => 5,
            'cols'         => 70,
            'class'        => 'view-description',
            'defaultvalue' => $view->get('description'),
            'rules'        => array('maxlength' => 1000000),
        ),
        'tags'        => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescprofile'),
            'defaultvalue' => $view->get('tags'),
            'help'         => true,
            'institution'  =>  $institution,
        )
    );
    if (!($group || $institution) && $createtagsoptions) {
        $elements['createtags'] = array(
            'type'         => 'select',
            'title'        => get_string('createtags', 'view'),
            'description'  => get_string('createtagsdesc1', 'view'),
            'options'      => $createtagsoptions,
            'isSelect2'    => true,
            'class'        => 'js-select2',
            'multiple'     => true,
            'defaultvalue' => null,
            'collapseifoneoption' => false,
            'width'        => '280px',
            'help'         => true,
        );
    }
    $viewhasblocks = count_records('block_instance', 'view', $view->get('id'));
    $accessibleviewdisabled = $viewhasblocks && !$view->get('accessibleview');
    if (!($group || $institution) && $USER->get_account_preference('accessibilityprofile')) {
        $elements['accessibleview'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('accessibleview', 'view'),
            'description'  => get_string('accessibleviewdescription', 'view'),
            'defaultvalue' => !$accessibleviewdisabled,
            'disabled'     => $accessibleviewdisabled,
        );
    }
    return $elements;
}

function get_advanced_elements() {
    global $view, $urlallowed, $group, $institution, $USER, $cleanurlbase;

    $formatstring = '%s (%s)';
    $ownerformatoptions = array(
        FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
        FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
        FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
    );

    $displayname = display_name($USER);
    if ($displayname !== '') {
        $ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('preferredname'), $displayname);
    }
    $studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
    if ($studentid !== '') {
        $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
    }

    $elements = array();
    if ($view->is_instruction_locked()) {
        if (!empty($view->get('instructions'))) {
            $elements['instructions'] = array(
              'type'         => 'html',
              'title'        => get_string('instructions','view'),
              'class'        => 'view-description',
              'value'        => clean_html($view->get('instructions')),
            );
        }
    }
    else {
        $elements['instructions'] = array(
            'type'         => 'wysiwyg',
            'title'        => get_string('instructions','view'),
            'rows'         => 5,
            'cols'         => 70,
            'class'        => 'view-description',
            'defaultvalue' => $view->get('instructions'),
            'rules'        => array('maxlength' => 1000000),
        );
    }

    $elements['urlid'] = array(
        'type'         => 'text',
        'title'        => get_string('viewurl', 'view'),
        'prehtml'      => '<span class="description">' . (isset($cleanurlbase) ? $cleanurlbase : '') . '</span> ',
        'description'  => get_string('viewurldescription', 'view') . ' ' . get_string('cleanurlallowedcharacters'),
        'defaultvalue' => $view->get('urlid'),
        'rules'        => array('maxlength' => 100, 'regex' => get_config('cleanurlvalidate')),
        'ignore'       => !$urlallowed,
    );

    if ($group) {
        $grouproles = $USER->get('grouproles');
        if ($grouproles[$group] == 'admin') {
            $elements['locked'] = array(
                'type'         => 'switchbox',
                'title'        => get_string('Locked', 'view'),
                'description'  => get_string('lockedgroupviewdesc', 'view'),
                'defaultvalue' => $view->get('locked'),
                'disabled'     => $view->get('type') == 'grouphomepage', // This page unreachable for grouphomepage anyway
            );
        }
    }
    $elements['lockblocks'] = array(
        'type'         => 'switchbox',
        'title'        => get_string('lockblocks', 'view'),
        'description'  => !empty($view->get('institution')) ? get_string('lockblocksdescriptioninstitution', 'view') : get_string('lockblocksdescription1', 'view'),
        'defaultvalue' => $view->get('lockblocks'),
    );
    if (!($group || $institution)) {
        $default = $view->get('ownerformat');
        if (!$default) {
            $default = FORMAT_NAME_DISPLAYNAME;
        }
        $elements['ownerformat'] = array(
            'type'         => 'select',
            'title'        => get_string('ownerformat','view'),
            'description'  => get_string('ownerformatdescription','view'),
            'options'      => $ownerformatoptions,
            'defaultvalue' => $default,
            'rules'        => array('required' => true),
        );
    }
    if (get_config('allowanonymouspages')) {
        $elements['anonymise'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('anonymise','view'),
            'description'  => get_string('anonymisedescription','view'),
            'defaultvalue' => $view->get('anonymise'),
        );
    }

    $folder = ArtefactTypeImage::get_coverimage_folder($USER, $group, $institution);

    $highlight = array(0);

    $elements['coverimage'] = array(
        'type'         => 'filebrowser',
        'title'        => get_string('coverimage', 'view'),
        'description'  => get_string('coverimagedescription', 'view'),
        'folder'       => $folder,
        'highlight'    => $highlight,
        'accept'       => 'image/*',
        'institution'  => $institution,
        'group'        => $group,
        'page'         => $view->get_url() . '&browse=1',
        'filters'      => array(
             'artefacttype' => array('image'),
        ),
        'config'       => array(
            'upload'          => true,
            'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
            'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
            'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
            'createfolder'    => false,
            'edit'            => false,
            'select'          => true,
            'selectone'       => true,
        ),
        'defaultvalue'       => ($view->get('coverimage') ? array($view->get('coverimage')) : null),
        'selectlistcallback' => 'artefact_get_records_by_id',
        'selectcallback'     => 'add_view_coverimage',
        'unselectcallback'   => 'delete_view_coverimage',
    );

    if (!$view->is_instruction_locked()) { //later i'll need to check the role of the login user
        $elements['locktemplate'] = array(
          'type'         => 'switchbox',
          'title'        => get_string('locktemplate','view'),
          'description'  => get_string('locktemplatedescription','view'),
          'defaultvalue' => $view->get('locktemplate'),
        );
    }
    else {
        if ($originaltemplate = $view->get_original_template()) {
            $originaltemplate = new View($originaltemplate);
            if (can_view_view($view)) {
                $html = '<a href="' . $originaltemplate->get_url() . '">' . $originaltemplate->get('title') . '</a>';
            }
            else {
                $html = $originaltemplate->get('title');
            }
            $description = get_string('linktooriginaltemplatedescription', 'view');
        }
        else {
            $html = get_string('deletedview', 'view');
            $description = get_string('linktooriginaltemplatedescriptiondeleted', 'view');
        }
        $elements['linktooriginaltemplate'] = array(
            'type'  => 'html',
            'title' => get_string('linktooriginaltemplate', 'view'),
            'value' => $html,
            'description' => $description,
        );
    }
    // give possibility to unlock the view to some roles
    // site admins in institution and site pages
    // institution admins in institution pages
    // group admins in group pages
    if (record_exists('view_instructions_lock', 'view', $view->get('id'))) {
        $canremovelock = false;
        // site admin
        if ($USER->get('admin') && $view->get('institution')) {
            $canremovelock = true;
        }
        //institution admin
        else if ($institution = $view->get('institution') && $USER->is_institutional_admin($institution)) {
            $canremovelock = true;
        }
        // group admin
        else if ($group = $view->get('group')) {
            $role = get_field('group_member', 'role', 'group', $group, 'member', $USER->get('id'));
            if ($role == 'admin') {
                $canremovelock = true;
            }
        }
        if ($canremovelock) {
            $elements['copylocked'] = array(
              'type'         => 'switchbox',
              'title'        => get_string('copylocked','view'),
              'description'  => get_string('copylockeddescription','view'),
              'defaultvalue' => $view->is_instruction_locked(),
            );
        }
    }

    // Theme dropdown
    $theme = $view->set_user_theme();
    $allowedthemes = get_user_accessible_themes();
    $allowedthemes = array_merge(array('' => get_string('nothemeselected1', 'view')), $allowedthemes);

    if ($theme && !isset($allowedthemes[$theme])) {
        // We have page set with an unknown theme
        // So redirect it to the choose theme page first
        redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id'));
    }

    if (get_config('userscanchooseviewthemes') && $view->is_themeable()) {
        $elements['theme'] = array(
            'type'          => 'select',
            'title'         => get_string('theme', 'view'),
            'description'   => get_string('choosethemedesc', 'view'),
            'options'       => $allowedthemes,
            'defaultvalue'  => $theme,
        );
    };

    $inlinejs = <<<EOF
function settings_callback(form, data) {
    settings_coverimage.callback(form, data);
};
EOF;

    return array($elements, $inlinejs);
}

function get_skin_elements() {
    global $view, $USER, $pieformname;
    $issiteview = $view->get('institution') == 'mahara';

    if (!can_use_skins(null, false, $issiteview)) {
        throw new FeatureNotEnabledException();
    }

    // Is page skin already saved/set for current page?
    $skin = param_integer('skin', null);
    $saved = false;
    if (!isset($skin)) {
        $skin = $view->get('skin');
        $saved = true;
    }
    if (!$skin || !($currentskin = get_record('skin', 'id', $skin))) {
        $currentskin = new stdClass();
        $currentskin->id = 0;
        $currentskin->title = get_string('skinnotselected', 'skin');
    }
    $incompatible = (isset($THEME->skins) && $THEME->skins === false && $currentskin->id != 0);
    if ($incompatible) {
        $incompatible = ($view->get('theme')) ? 'notcompatiblewithpagetheme' : 'notcompatiblewiththeme';
        $incompatible = get_string($incompatible, 'skin', $THEME->displayname);
    }
    $metadata = array();
    if (!empty($currentskin->id)) {
        $owner = new User();
        $owner->find_by_id($currentskin->owner);
        $currentskin->metadata = array(
            'displayname' => '<a href="' . get_config('wwwroot') . 'user/view.php?id=' . $currentskin->owner . '">' . display_name($owner) . '</a>',
            'description' => nl2br($currentskin->description),
            'ctime' => format_date(strtotime($currentskin->ctime)),
            'mtime' => format_date(strtotime($currentskin->mtime)),
         );
    }

    $userskins   = Skin::get_user_skins();
    $favorskins  = Skin::get_favorite_skins();
    $siteskins   = Skin::get_site_skins();
    $defaultskin = Skin::get_default_skin();

    if (!$USER->can_edit_view($view)) {
        throw new AccessDeniedException();
    }
    $displaylink = $view->get_url();

    $snippet = smarty_core();
    $snippet->assign('saved', $saved);
    $snippet->assign('incompatible', $incompatible);
    $snippet->assign('currentskin', $currentskin->id);
    $snippet->assign('currenttitle', $currentskin->title);
    $snippet->assign('currentmetadata', (!empty($currentskin->metadata)) ? $currentskin->metadata : null);
    $snippet->assign('userskins', $userskins);
    $snippet->assign('favorskins',$favorskins);
    $snippet->assign('siteskins', $siteskins);
    $snippet->assign('defaultskin', $defaultskin);
    $snippet->assign('viewid', $view->get('id'));
    $snippet->assign('viewtype', $view->get('type'));
    $snippet->assign('edittitle', $view->can_edit_title());
    $snippet->assign('issiteview', $issiteview);
    $skinform = array(
        'skins_html' => array(
            'type' => 'html',
            'value' => $snippet->fetch('view/skin.tpl'),
        )
    );

    $hiddenelements = array(
        'skinid' => array(
            'type' => 'hidden',
            'value' =>  $currentskin->id,
            'sesskey' =>  $USER->get('sesskey'),
        ),
    );

    $inlinejs = <<<JAVASCRIPT

function change_skin(view, skin) {
  var pd   = {
       'id': view,
       'skin': skin,
       'pieformname': "{$pieformname}"
       }
  sendjsonrequest(config['wwwroot'] + 'view/skins.json.php', pd, 'POST', function(data) {
      jQuery('#settings_skins_html_container').html(data.html);
      jQuery('#settings_skinid').val(data.skin);
      formchangemanager.setFormState(jQuery('#' + data.pieformname), FORM_CHANGED);
  });
};

JAVASCRIPT;

    return array($skinform, $hiddenelements, $inlinejs);
}

function settings_validate(Pieform $form, $values) {
    global $view, $issiteview, $issitetemplate, $canuseskins;

    if (isset($values['urlid']) && $values['urlid'] != $view->get('urlid')) {
        if (strlen($values['urlid']) < 3) {
            $form->set_error('urlid', get_string('rule.minlength.minlength', 'pieforms', 3));
        }
        else if ($group = $view->get('group') and record_exists('view', 'group', $group, 'urlid', $values['urlid'])) {
            $form->set_error('urlid', get_string('groupviewurltaken', 'view'));
        }
        else if ($owner = $view->get('owner') and record_exists('view', 'owner', $owner, 'urlid', $values['urlid'])) {
            $form->set_error('urlid', get_string('userviewurltaken', 'view'));
        }
    }

    if ($canuseskins && isset($values['skinid']) && $values['skinid']) {
        $skin = new Skin($values['skinid']);
        if (!$skin->can_use()) {
            throw new AcessDeniedException();
        }
    }
}

function settings_submit(Pieform $form, $values) {
    global $view, $SESSION, $issiteview, $issitetemplate, $canedittitle, $canuseskins;

    if ($canedittitle) {
        set_view_title_and_description($form, $values);
        set_view_advanced($form, $values);
    }

    if ($canuseskins && isset($values['skinid'])) {
        $view->set('skin', $values['skinid']);
    }
    $view->set('coverimage', (isset($values['coverimage']) ? $values['coverimage'] : null));

    if (isset($values['copylocked'])) {
        if ($values['copylocked']) {
            $view->lock_instructions_edit($view->get_original_template());
        }
        else {
            $view->unlock_instructions_edit();
        }
    }

    $view->commit();

    $result = array(
        'error'   => false,
        'message' => get_string('viewsavedsuccessfully', 'view'),
        'goto'    => get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id'),
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function create_block($bt, $configdata, $view, $blockinfo = null, $dimension=null) {
    if ($bt == 'taggedposts') {
        $tagselect = $configdata['tagselect'];
        unset($configdata['tagselect']);
    }
    safe_require('blocktype', $bt);
    $bi = new BlockInstance(0, array('blocktype' => $bt, 'view' => $view->get('id')));
    $blocktypeclass = generate_class_name('blocktype', $bt);
    if (method_exists($blocktypeclass, 'get_instance_title')) {
        $title = call_static_method($blocktypeclass, 'get_instance_title', $bi);
        $defaulttitle = false;
    }
    else {
        $title = $blocktypeclass::get_title();
        $defaulttitle = true;
    }

    $bi->set('title', $title);
    $bi->set('positionx', 0);
    $bi->set('positiony', 0);
    if ($dimension) {
        $bi->set('height', $dimension->height);
        $bi->set('width', $dimension->width);
    }
    else {
        $bi->set('height', 3);
        $bi->set('width', 4);
    }
    $configdata['retractable'] = false;
    $configdata['retractedonload'] = false;
    $bi->set('configdata', $configdata);
    $bi->commit();
    // Now we have committed the block we can check if we can use something other than default block title
    if ($defaulttitle) {
        if (!empty($configdata['artefactid']) && $title = $bi->get_artefact_instance($configdata['artefactid'])->get('title')) {
            $bi->set('title', $title);
        }
        else if (!empty($blockinfo)) {
            $oldbi = new BlockInstance($blockinfo['oldid']);
            $title = $oldbi->get('title');
            $bi->set('title', $title);
        }
    }

    if ($blockinfo['tags']) {
        $bi->set('tags', $blockinfo['tags']);
    }
    if ($bt == 'taggedposts') {
        $blocktypeclass::save_tag_selection($tagselect, $bi);
        // Need to make the block save again now we have made the tag selections
        $bi->set('dirty', true);
    }
    $bi->commit();
    return $bi->get('id');
}

function set_view_title_and_description(Pieform $form, $values) {
    global $view, $urlallowed, $new, $USER;

    $view->set('title', $values['title']);
    $view->set('description', trim($values['description']));
    $tags = $values['tags'] ? $values['tags'] : array();
    $view->set('tags', $tags);
    if (isset($values['createtags'])) {
        $createtags = $values['createtags'] ? $values['createtags'] : array();
        if ($createtags) {
            require_once('searchlib.php');
            require_once('collection.php');
            $data = array();
            // Get all the items containing any of the tags
            foreach ($createtags as $tag) {
                $tagowner  = (object) array('type' => 'user', 'id' => $USER->get('id'));
                $tagdata = get_portfolio_items_by_tag($tag, $tagowner, 0, 0, 'date', 'all');
                $data = array_merge($data, $tagdata->data);
            }

            if ($data) {
                $combineddata = array();
                // Now check what we have so we know what to do with them
                foreach ($data as $item) {
                    // If collection but tag is in one of it's views then no $item->tags so skip
                    if (!isset($item->tags)) {
                        continue;
                    }
                    // Check that the block has all of the tags we entered, and if not skip it
                    if (array_diff($createtags, $item->tags)) {
                        continue;
                    }
                    // Check if the block we are about to add is from the current page, and if so skip it
                    if (isset($item->views) && isset($item->views[$view->get('id')])) {
                        continue;
                    }
                    $type = isset($item->specialtype) ? $item->specialtype : $item->artefacttype;
                    if (!isset($combineddata[$item->type])) {
                        $combineddata[$item->type] = array();
                    }
                    if (!isset($combineddata[$item->type][$type])) {
                        $combineddata[$item->type][$type] = array('count' => 1, 'ids' => array($item->id));
                    }
                    else {
                        $combineddata[$item->type][$type]['count'] ++;
                        $combineddata[$item->type][$type]['ids'][] = $item->id;
                    }
                }
                // Now lets make decisions about what we have
                if (!empty($combineddata['blocktype'])) {
                    foreach ($combineddata['blocktype'] as $bk => $bv) {
                        $bt = false;
                        foreach($bv['ids'] as $bid) {
                            $configdata = unserialize(get_field('block_instance', 'configdata', 'id', $bid));
                            $tags = get_column('tag', 'tag', 'resourcetype', 'blocktype', 'resourceid', $bid);
                            foreach($tags as &$t) {
                                if (preg_match('/^tagid\_(.*)/', $t, $matches)) {
                                     if ($itag = get_record('tag', 'id', $matches[1])) {
                                         $instname = get_field('institution', 'displayname', 'id', $itag->resourceid);
                                         $t = $instname . ': ' . $itag->tag;
                                      }
                                  }
                            }
                            $dimension = get_record('block_instance_dimension', 'block', $bid);
                            $id = create_block($bk, $configdata, $view, array('oldid' => $bid, 'tags' => $tags), $dimension);
                        }
                    }
                }
                if (!empty($combineddata['artefact'])) {
                    $filedownload = array();
                    $plans = array();
                    foreach ($combineddata['artefact'] as $ak => $av) {
                        safe_require('artefact', 'file');
                        $bt = false;
                        if ($ak == 'plan') {
                            // Pass to plans to create later
                            $plans = array_merge($plans, $av['ids']);
                        }
                        if ($ak == 'task') {
                            // We need to add the plan block that the task(s) relate to
                            $taskplans = get_column_sql("SELECT DISTINCT parent FROM {artefact}
                                                         WHERE id IN (" . join(',', $av['ids']) . ")");
                            $plans = array_unique(array_merge($plans, $taskplans));
                        }
                        if ($ak == 'html') { // This is an artefact related to the 'note' block (not 'html' block)
                            // Need to do a loop for each folder
                            foreach($av['ids'] as $noteid) {
                                // Need to add a note block
                                $bt = 'textbox';
                                $configdata = array('artefactid' => $noteid,
                                                    'licensereadonly' => '', // default license placeholder
                                                    'tagsreadonly' => '', // default tag placeholder
                                                   );
                                // We need to get an example of an existing note (textbox) block to find out
                                // if there are meant to be attachments for the note
                                // @TODO: fix this up - we should have a artefact_note_attachment table rather than
                                //        having every note block containing the info
                                if ($oldconfigdata = get_field_sql("
                                        SELECT bi.configdata
                                        FROM {block_instance} bi
                                        JOIN {view_artefact} va ON va.block = bi.id
                                        WHERE va.artefact = ?
                                        AND bi.blocktype = ?
                                        LIMIT 1", array($noteid, 'textbox'))) {
                                    $oldconfigdata = unserialize($oldconfigdata);
                                    $configdata['artefactids'] = !empty($oldconfigdata['artefactids']) ? $oldconfigdata['artefactids'] : null;
                                }

                                $id = create_block($bt, $configdata, $view);
                            }
                            $bt = false;
                        }
                        if ($ak == 'blog') {
                            // Need to do a loop for each folder
                            foreach($av['ids'] as $blogid) {
                                // Need to add a blog block
                                $bt = 'blog';
                                $configdata = array('artefactid' => $blogid,
                                                    'count' => '5', // default number of posts to display
                                                    'copytype' => 'nocopy', // default copy type
                                                   );
                                $id = create_block($bt, $configdata, $view);
                            }
                            $bt = false;
                        }
                        if ($ak == 'blogpost') {
                            // Need to add a taggedpost block
                            $bt = 'taggedposts';
                            $configdata = array('tagselect' => $createtags,
                                                'count' => '10', // default number of posts to display
                                                'copytype' => 'nocopy', // default copy type
                                                'full' => false,
                                               );
                        }
                        if ($ak == 'folder') {
                            // Need to do a loop for each folder
                            foreach($av['ids'] as $folderid) {
                                // Need to add a folder block
                                $bt = 'folder';
                                $configdata = array('artefactid' => $folderid,
                                                    'sortorder' => 'asc',
                                                   );
                                $id = create_block($bt, $configdata, $view);
                            }
                            $bt = false;
                        }
                        if ($ak == 'video' || $ak == 'audio') {
                            if ($av['count'] > 1) {
                                // Need to add a files to download block
                                $filedownload = array_merge($filedownload, $av['ids']);
                            }
                            else {
                                // Need to add an internalmedia block
                                $bt = 'internalmedia';
                                $configdata = array('artefactid' => $av['ids'][0]);
                            }
                        }
                        if ($ak == 'file') {
                            // Need to add a files to download block
                            $filedownload = array_merge($filedownload, $av['ids']);
                        }
                        if ($ak == 'pdf') {
                            if ($av['count'] > 1) {
                                // Need to add a files to download block
                                $filedownload = array_merge($filedownload, $av['ids']);
                            }
                            else {
                                // Need to add a pdf block
                                $bt = 'pdf';
                                $configdata = array('artefactid' => $av['ids'][0],
                                                    'pdfwarning' => get_string('pdfwarning', 'blocktype.file/pdf'),
                                                   );
                            }
                        }
                        if ($ak == 'image') {
                            if ($av['count'] > 1) {
                                // Need to add an image gallery block
                                $bt = 'gallery';
                                $configdata = array('artefactids' => $av['ids'],
                                                    'user' => $view->get('owner'), // normally 'user' is for external gallery but we set it to page owner for interal gallery
                                                    'select' => '1', // to select images by ids
                                                    'style' => '1',  // to display the images as slideshow
                                                    'showdescription' => false,
                                                    'width' => '75', // the default value added to config form
                                                   );
                            }
                            else {
                                // Need to add an image block
                                $bt = 'image';
                                $configdata = array('artefactid' => $av['ids'][0],
                                                    'showdescription' => false,
                                                   'width' => "",
                                                   );
                            }
                        }
                        if ($bt) {
                            // Add the block to the page
                            $id = create_block($bt, $configdata, $view);
                        }
                    }
                    // We add the plan block now
                    if (!empty($plans)) {
                        $bt = 'plans';
                        $configdata = array('artefactids' => $plans,
                                            'count' => 10, // default tasks
                                           );
                        $id = create_block($bt, $configdata, $view);
                    }
                    // We add in the file to download block once we work out what should be in it
                    if (!empty($filedownload)) {
                        $bt = 'filedownload';
                        $configdata = array('artefactids' => $filedownload);
                        $id = create_block($bt, $configdata, $view);
                    }
                }
            }
        }
    }
    if (isset($values['locked'])) {
        $view->set('locked', (int)$values['locked']);
    }
    if (isset($values['lockblocks'])) {
        $view->set('lockblocks', (int)$values['lockblocks']);
    }
    if (isset($values['theme'])) {
        $view->set('theme', $values['theme']);
    }
    if (isset($values['ownerformat']) && $view->get('owner')) {
        $view->set('ownerformat', $values['ownerformat']);
    }
    if (isset($values['anonymise'])) {
        $view->set('anonymise', (int)$values['anonymise']);
    }
    if (isset($values['accessibleview'])) {
        $view->set('accessibleview', (int)$values['accessibleview']);
    }
    if (isset($values['locktemplate'])) {
        $view->set('locktemplate', (int)$values['locktemplate']);
    }
}

function set_view_advanced(Pieform $form, $values) {
    global $view, $urlallowed, $new;

    if (isset($values['instructions']) && trim($values['instructions']) !== '') {
        require_once('embeddedimage.php');
        $view->set('instructions', EmbeddedImage::prepare_embedded_images($values['instructions'], 'instructions', $view->get('id')));
    }
    else {
        $view->set('instructions', '');
    }
    if (isset($values['ownerformat']) && $view->get('owner')) {
        $view->set('ownerformat', $values['ownerformat']);
    }
    // Change the 'untitled' urlid on first save
    if ($new && $urlallowed) {
        // Generate one automatically based on the title
        $desired = generate_urlid($values['title'], get_config('cleanurlviewdefault'), 3, 100);
        $ownerinfo = (object) array('owner' => $view->get('owner'), 'group' => $view->get('group'));
        $view->set('urlid', View::new_urlid($desired, $ownerinfo));
    }
    else if (isset($values['urlid'])) {
        $view->set('urlid', strlen($values['urlid']) == 0 ? null : $values['urlid']);
    }
}


function add_view_coverimage($coverimageid) {
    global $view;
    if ($view) {
        $view->set('coverimage', $coverimageid);
    }
}

function delete_view_coverimage($coverimageid=null) {
    global $view;
    if ($view) {
        $view->set('coverimage', 0);
    }
}
