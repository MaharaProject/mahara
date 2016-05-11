<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', true);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'skin');
define('SECTION_PAGE', 'design');

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
safe_require('artefact', 'file');

$fieldset = param_alpha('fs', 'viewskin');
$designsiteskin = param_boolean('site', false);

if (!can_use_skins(null, $designsiteskin)) {
    throw new FeatureNotEnabledException();
}

if ($designsiteskin) {
    define('ADMIN', 1);
    if (!$USER->get('admin')) {
        $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
        redirect();
    }
    define('MENUITEM', 'configsite/siteskins');
    $goto = '/admin/site/skins.php';
    $redirect = '/admin/site/skins.php';
}
else {
    define('MENUITEM', 'myportfolio/skins');
    $goto = '/skin/index.php';
    $redirect = '/skin/index.php';
}
$id = param_integer('id', 0); // id of Skin to be edited...
$skindata = null;
if ($id > 0) {
    $skinobj = new Skin($id);
    if ($skinobj->can_edit()) {
        $viewskin = $skinobj->get('viewskin');
        // check to see if any background images being referenced have not
        // been deleted from site and if they have set the value to false
        if (!empty($viewskin['body_background_image'])) {
            if (!record_exists('artefact', 'id', $viewskin['body_background_image'])) {
                $viewskin['body_background_image'] = false;
            }
        }
        if (!empty($viewskin['view_background_image'])) {  // TODO remove this
            if (!record_exists('artefact', 'id', $viewskin['view_background_image'])) {
                $viewskin['view_background_image'] = false;
            }
        }
    }
    else {
        throw new AccessDeniedException("You can't access and/or edit Skin with id $id");
    }
    define('TITLE', get_string('editskin', 'skin'));
}
else {
    define('TITLE', get_string('createskin', 'skin'));
    $skinobj = new Skin();
}

// Set the Skin access options (for creating or editing form)...
$designsiteskin = $designsiteskin || (isset($skinobj) && $skinobj->get('type') == 'site');
if ($designsiteskin) {
    $accessoptions = array(
            'site' => get_string('siteskinaccess', 'skin')
    );
}
else {
    $accessoptions = array(
            'private' => get_string('privateskinaccess', 'skin'),
            'public' => get_string('publicskinaccess', 'skin'),
    );
}
// because the page has two artefact choosers in the form
// we need to handle how the browse works differently from normal
$folder = param_integer('folder', null);
$highlight = null;
if ($file = param_integer('file', 0)) {
    $highlight = array($file);
}
$skintitle = $skinobj->get('title');
$skindesc = $skinobj->get('description');
$skintype = $skinobj->get('type');

$positions = array(
    1 => get_string('topleft', 'skin'),
    2 => get_string('top', 'skin'),
    3 => get_string('topright', 'skin'),
    4 => get_string('left', 'skin'),
    5 => get_string('centre', 'skin'),
    6 => get_string('right', 'skin'),
    7 => get_string('bottomleft', 'skin'),
    8 => get_string('bottom', 'skin'),
    9 => get_string('bottomright', 'skin'),
);

$elements = array();
$elements['id'] = array(
        'type' => 'hidden',
        'value' => $id,
);
$elements['viewskin'] = array(
        'type'   => 'fieldset',
        'legend' => get_string('skingeneraloptions', 'skin'),
        'class'  => $fieldset != 'viewskin' ? 'collapsed' : '',
        'elements'     => array(
                'viewskin_title' => array(
                        'type' => 'text',
                        'title' => get_string('skintitle', 'skin'),
                        'defaultvalue' => (!empty($skintitle) ? $skintitle : null),
                ),
                'viewskin_description' => array(
                        'type' => 'textarea',
                        'rows' => 3,
                        'cols' => 40,
                        'resizable' => true,
                        'title' => get_string('skindescription', 'skin'),
                        'defaultvalue' => (!empty($skindesc) ? $skindesc : null),
                ),
                'viewskin_access' => array(
                        'type' => 'select',
                        'title' => get_string('skinaccessibility1', 'skin'),
                        'defaultvalue' => (!empty($skintype) ? $skintype : null),
                        'options' => $accessoptions,
                ),
        ),
);
$elements['skinbg'] = array(
    'type'   => 'fieldset',
    'legend' => get_string('skinbackgroundoptions1', 'skin'),
    'class'  => $fieldset != 'skinbg' ? 'collapsed' : '',
    'elements'     => array(
            'body_background_color' => array(
                    'type' => 'color',
                    'title' => get_string('bodybgcolor1', 'skin'),
                    'defaultvalue' => (!empty($viewskin['body_background_color']) ? $viewskin['body_background_color'] : '#FFFFFF'),
                    'size' => 7,
                    'options' => array(
                        'transparent' => true,
                    ),
            )
    )
);
// Currently site files don't work properly with site skins. And since site files are the only files that would make
// sense with site skins, we're going to just hide background images entirely for site skins for the time being.
if (!$designsiteskin) {
    $elements['skinbg']['elements'] = array_merge($elements['skinbg']['elements'], array(
        'body_background_image' => array(
                'type'         => 'filebrowser',
                'title'        => get_string('bodybgimage1', 'skin'),
                'folder'       => ((isset($folder)) ? $folder : 0),
                'highlight'    => $highlight,
                'browse'       => ((isset($folder)) ? 1 : 0),
                'filters'      => array(
                         'artefacttype' => array('image', 'profileicon'),
                ),
                'page'         => get_config('wwwroot') . 'skin/design.php?id=' . $id . '&fs=skinbg',
                'config'       => array(
                        'upload'          => false,
                        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                        'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                        'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                        'createfolder'    => false,
                        'edit'            => false,
                        'select'          => true,
                        'selectone'       => true,
                ),
                'defaultvalue'       => (!empty($viewskin['body_background_image']) ? array(intval($viewskin['body_background_image'])) : array()),
                'selectlistcallback' => 'artefact_get_records_by_id',
                // TODO: Make this work so skins can include site files
                // 'tabs' => true,
        ),
        'body_background_repeat' => array(
                'type' => 'select',
                'title' => get_string('backgroundrepeat', 'skin'),
                'defaultvalue' => (!empty($viewskin['body_background_repeat']) ? intval($viewskin['body_background_repeat']) : 4),
                'options' => array(
                        Skin::BACKGROUND_REPEAT_NO => get_string('backgroundrepeatno', 'skin'),
                        Skin::BACKGROUND_REPEAT_X => get_string('backgroundrepeatx', 'skin'),
                        Skin::BACKGROUND_REPEAT_Y => get_string('backgroundrepeaty', 'skin'),
                        Skin::BACKGROUND_REPEAT_BOTH => get_string('backgroundrepeatboth', 'skin'),
                ),
        ),
        'body_background_attachment' => array(
                'type' => 'radio',
                'title' => get_string('backgroundattachment', 'skin'),
                'defaultvalue' => (!empty($viewskin['body_background_repeat']) ? $viewskin['body_background_attachment'] : 'scroll'),
                'options' => array(
                        'fixed' => get_string('backgroundfixed', 'skin'),
                        'scroll' => get_string('backgroundscroll', 'skin'),
                ),
        ),
        'body_background_position' => array(
                'type' => 'radio',
                'title' => get_string('backgroundposition', 'skin'),
                'defaultvalue' => (!empty($viewskin['body_background_position']) ? intval($viewskin['body_background_position']) : 1),
                'rowsize' => 3,
                'hiddenlabels' => false,
                'options' => $positions,
        )
    ));
}
$elements['viewbg'] = array( // TODO remove this
    'type'   => 'fieldset',
    'legend' => get_string('viewbackgroundoptions', 'skin'),
    'class'  => 'hidden',
    'elements'     => array(
            'view_background_color' => array(
                    'type' => 'color',
                    'title' => get_string('viewbgcolor', 'skin'),
                    'defaultvalue' => (!empty($viewskin['view_background_color']) ? $viewskin['view_background_color'] : '#FFFFFF'),
                    'size' => 7,
                    'options' => array(
                        'transparent' => true,
                    ),
            )
    )
);
if (!$designsiteskin) {  // TODO remove this
    $elements['viewbg']['elements'] = array_merge($elements['viewbg']['elements'], array(
        'view_background_image' => array(
                'type'         => 'filebrowser',
                'title'        => get_string('viewbgimage', 'skin'),
                'folder'       => ((isset($folder)) ? $folder : 0),
                'highlight'    => $highlight,
                'browse'       => ((isset($folder)) ? 1 : 0),
                'filters'      => array(
                         'artefacttype' => array('image', 'profileicon'),
                ),
                'page'         => get_config('wwwroot') . 'skin/design.php?id=' . $id . '&fs=viewbg',
                'config'       => array(
                        'upload'          => false,
                        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                        'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                        'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                        'createfolder'    => false,
                        'edit'            => false,
                        'select'          => true,
                        'selectone'       => true,
                ),
                'defaultvalue'       => (!empty($viewskin['view_background_image']) ? array(intval($viewskin['view_background_image'])) : array()),
                'selectlistcallback' => 'artefact_get_records_by_id',
                // TODO: make this work, so skins can include site files
                // 'tabs' => true,
        ),
        'view_background_repeat' => array(
                'type' => 'select',
                'title' => get_string('backgroundrepeat', 'skin'),
                'defaultvalue' => (!empty($viewskin['view_background_repeat']) ? intval($viewskin['view_background_repeat']) : 4),
                'options' => array(
                        Skin::BACKGROUND_REPEAT_NO => get_string('backgroundrepeatno', 'skin'),
                        Skin::BACKGROUND_REPEAT_X => get_string('backgroundrepeatx', 'skin'),
                        Skin::BACKGROUND_REPEAT_Y => get_string('backgroundrepeaty', 'skin'),
                        Skin::BACKGROUND_REPEAT_BOTH => get_string('backgroundrepeatboth', 'skin'),
                ),
        ),
        'view_background_attachment' => array(
                'type' => 'radio',
                'title' => get_string('backgroundattachment', 'skin'),
                'defaultvalue' => (!empty($viewskin['view_background_repeat']) ? $viewskin['view_background_attachment'] : 'scroll'),
                'options' => array(
                        'fixed' => get_string('backgroundfixed', 'skin'),
                        'scroll' => get_string('backgroundscroll', 'skin'),
                ),
        ),
        'view_background_position' => array(
                'type' => 'radio',
                'title' => get_string('backgroundposition', 'skin'),
                'defaultvalue' => (!empty($viewskin['view_background_position']) ? intval($viewskin['view_background_position']) : 1),
                'rowsize' => 3,
                'hiddenlabels' => false,
                'options' => $positions,
        ),
        'view_background_width' => array(
                'type' => 'select',
                'title' => get_string('viewwidth', 'skin'),
                'defaultvalue' => (!empty($viewskin['view_background_width']) ? intval($viewskin['view_background_width']) : 90),
                'options' => array(
                        50 => '50%',
                        60 => '60%',
                        70 => '70%',
                        80 => '80%',
                        90 => '90%',
                        100 => '100%',
                ),
        ),
    ));
}
$elements['viewheader'] = array(  // TODO remove this
        'type'   => 'fieldset',
        'legend' => get_string('viewheaderoptions', 'skin'),
        'class'  => 'hidden',
        'elements'     => array(
                'header_background_color' => array(
                        'type' => 'color',
                        'title' => get_string('backgroundcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['header_background_color']) ? $viewskin['header_background_color'] : '#CCCCCC'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'header_text_font_color' => array(
                        'type' => 'color',
                        'title' => get_string('textcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['header_text_font_color']) ? $viewskin['header_text_font_color'] : '#000000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'header_link_normal_color' => array(
                        'type' => 'color',
                        'title' => get_string('normallinkcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['header_link_normal_color']) ? $viewskin['header_link_normal_color'] : '#0000EE'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'header_link_normal_underline' => array(
                        'type' => 'switchbox',
                        'title' => get_string('linkunderlined', 'skin'),
                        'defaultvalue' => (isset($viewskin['header_link_normal_underline']) and intval($viewskin['header_link_normal_underline']) == 1 ? 'checked' : ''),
                ),
                'header_link_hover_color' => array(
                        'type' => 'color',
                        'title' => get_string('hoverlinkcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['header_link_hover_color']) ? $viewskin['header_link_hover_color'] : '#EE0000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'header_link_hover_underline' => array(
                        'type' => 'switchbox',
                        'title' => get_string('linkunderlined', 'skin'),
                        'defaultvalue' => (isset($viewskin['header_link_hover_underline']) and intval($viewskin['header_link_hover_underline']) == 1 ? 'checked' : ''),
                ),
                'header_logo_image' => array(
                        'type' => 'radio',
                        'id' => 'designskinform_header_logo',
                        'title' => get_string('headerlogoimage1', 'skin'),
                        'defaultvalue' => (!empty($viewskin['header_logo_image']) ? $viewskin['header_logo_image'] : 'normal'),
                        'options' => array(
                                'normal' => get_string('headerlogoimagenormal', 'skin'),
                                'light' => get_string('headerlogoimagelight1', 'skin'),
                                'dark' => get_string('headerlogoimagedark1', 'skin'),
                        )
                ),
        ),
);
$elements['viewcontent'] = array(
        'type'   => 'fieldset',
        'legend' => get_string('viewcontentoptions1', 'skin'),
        'class'  => $fieldset != 'viewcontent' ? 'collapsed' : '',
        'elements'     => array(
                'view_heading_font_family' => array(
                        'type' => 'select',
                        'title' => get_string('headingfontfamily', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_heading_font_family']) ? $viewskin['view_heading_font_family'] : 'Arial'),
                        'width' => 144,
                        'options' => Skin::get_all_font_options(),
                ),
                'view_text_font_family' => array(
                        'type' => 'select',
                        'title' => get_string('textfontfamily', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_text_font_family']) ? $viewskin['view_text_font_family'] : 'Arial'),
                        'width' => 144,
                        'options' => Skin::get_textonly_font_options(),
                ),
                'view_text_font_size' => array(
                        'type' => 'select',
                        'title' => get_string('fontsize', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_text_font_size']) ? $viewskin['view_text_font_size'] : 'small'),
                        'width' => 144,
                        'options' => array(
                                'xx-small' => array('value' => get_string('fontsizesmallest', 'skin'), 'style' => 'font-size: xx-small;'),
                                'x-small' => array('value' => get_string('fontsizesmaller', 'skin'), 'style' => 'font-size: x-small;'),
                                'small' => array('value' => get_string('fontsizesmall', 'skin'), 'style' => 'font-size: small;'),
                                'medium' => array('value' => get_string('fontsizemedium', 'skin'), 'style' => 'font-size: medium;'),
                                'large' => array('value' => get_string('fontsizelarge', 'skin'), 'style' => 'font-size: large;'),
                                'x-large' => array('value' => get_string('fontsizelarger', 'skin'), 'style' => 'font-size: x-large;'),
                                'xx-large' => array('value' => get_string('fontsizelargest', 'skin'), 'style' => 'font-size: xx-large;'),
                        ),
                ),
                'view_text_font_color' => array(
                        'type' => 'color',
                        'title' => get_string('textcolor', 'skin'),
                        'description' => get_string('textcolordescription', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_text_font_color']) ? $viewskin['view_text_font_color'] : '#000000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_text_heading_color' => array(
                        'type' => 'color',
                        'title' => get_string('headingcolor', 'skin'),
                        'description' => get_string('headingcolordescription', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_text_heading_color']) ? $viewskin['view_text_heading_color'] : '#000000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_text_emphasized_color' => array(
                        'type' => 'color',
                        'title' => get_string('emphasizedcolor', 'skin'),
                        'description' => get_string('emphasizedcolordescription', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_text_emphasized_color']) ? $viewskin['view_text_emphasized_color'] : '#000000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_link_normal_color' => array(
                        'type' => 'color',
                        'title' => get_string('normallinkcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_link_normal_color']) ? $viewskin['view_link_normal_color'] : '#0000EE'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_link_normal_underline' => array(
                        'type' => 'switchbox',
                        'title' => get_string('linkunderlined', 'skin'),
                        'defaultvalue' => (isset($viewskin['view_link_normal_underline']) and intval($viewskin['view_link_normal_underline']) == 1 ? 'checked' : ''),
                ),
                'view_link_hover_color' => array(
                        'type' => 'color',
                        'title' => get_string('hoverlinkcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_link_hover_color']) ? $viewskin['view_link_hover_color'] : '#EE0000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_link_hover_underline' => array(
                        'type' => 'switchbox',
                        'title' => get_string('linkunderlined', 'skin'),
                        'defaultvalue' => (isset($viewskin['view_link_hover_underline']) and intval($viewskin['view_link_hover_underline']) == 1 ? 'checked' : ''),
                ),
        ),
);
$elements['viewtable'] = array(  // TODO remove this
        'type'   => 'fieldset',
        'legend' => get_string('viewtableoptions', 'skin'),
        'class'  => 'hidden',
        'elements'     => array(
                'view_table_border_color' => array(
                        'type' => 'color',
                        'title' => get_string('tableborder', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_table_border_color']) ? $viewskin['view_table_border_color'] : '#CCCCCC'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_table_header_color' => array(
                        'type' => 'color',
                        'title' => get_string('tableheader', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_table_header_color']) ? $viewskin['view_table_header_color'] : '#CCCCCC'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_table_header_text_color' => array(
                        'type' => 'color',
                        'title' => get_string('tableheadertext', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_table_header_text_color']) ? $viewskin['view_table_header_text_color'] : '#000000'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_table_odd_row_color' => array(
                        'type' => 'color',
                        'title' => get_string('tableoddrows', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_table_odd_row_color']) ? $viewskin['view_table_odd_row_color'] : '#EEEEEE'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_table_even_row_color' => array(
                        'type' => 'color',
                        'title' => get_string('tableevenrows', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_table_even_row_color']) ? $viewskin['view_table_even_row_color'] : '#FFFFFF'),
                        'size' => 7,
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_button_normal_color' => array(
                        'type' => 'color',
                        'title' => get_string('normalbuttoncolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_button_normal_color']) ? $viewskin['view_button_normal_color'] : '#CCCCCC'),
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_button_hover_color' => array(
                        'type' => 'color',
                        'title' => get_string('hoverbuttoncolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_button_hover_color']) ? $viewskin['view_button_hover_color'] : '#EEEEEE'),
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
                'view_button_text_color' => array(
                        'type' => 'color',
                        'title' => get_string('buttontextcolor', 'skin'),
                        'defaultvalue' => (!empty($viewskin['view_button_text_color']) ? $viewskin['view_button_text_color'] : '#FFFFFF'),
                        'options' => array(
                            'transparent' => true,
                        ),
                ),
        ),
);
$elements['viewadvanced'] = array(
        'type'   => 'fieldset',
        'legend' => get_string('viewadvancedoptions', 'skin'),
        'class'  =>  $fieldset != 'viewadvanced' ? 'collapsed' : '',
        'elements'     => array(
                'view_custom_css' => array(
                        'type' => 'textarea',
                        'rows' => 7,
                        'cols' => 50,
                        'style' => 'font-family:monospace',
                        'resizable' => true,
                        'fullwidth' => true,
                        'title' => get_string('skincustomcss','skin'),
                        'description' => get_string('skincustomcssdescription', 'skin'),
                        'defaultvalue' => ((!empty($viewskin['view_custom_css'])) ? $viewskin['view_custom_css'] : null),
                ),
        ),
);
$elements['fs'] = array(
        'type' => 'hidden',
        'value' => $fieldset,
);
$elements['submit'] = array(
        'type' => 'submitcancel',
        'class' => 'btn-primary',
        'value' => array(get_string('save', 'mahara'), get_string('cancel', 'mahara')),
        'goto' => get_config('wwwroot') . $goto,
);

$designskinform = pieform(array(
        'name'       => 'designskinform',
        'class'      => 'jstabs form-group-nested',
        'method'     => 'post',
        //'jsform'     => true,
        'plugintype' => 'core',
        'pluginname' => 'skin',
        'renderer'   => 'div',  // don't change unless you also modify design.js to not require tables.
        'autofocus'  => false,
        'configdirs' => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
        'elements' => $elements
));


$smarty = smarty(array(), array(), array(
    'mahara' => array(
        'tab',
        'selected',
    ),
));
$smarty->assign('LANG', substr($CFG->lang, 0, 2));
$smarty->assign('USER', $USER);
$smarty->assign('designskinform', $designskinform);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('skin/design.tpl');

function designskinform_validate(Pieform $form, $values) {
    global $USER;

    if (isset($values['viewskin_access']) && !($values['viewskin_access'] == 'site')) {
        $artefactfields = array(
            'body_background_image',
            'view_background_image'
        );
        foreach ($artefactfields as $field) {
            if (empty($values[$field])) {
                continue;
            }
            $obj = new ArtefactTypeImage($values[$field]);
            // Make sure the user has access to each of the image artefacts they're trying to
            // embed. This will indicate that they've hacked the HTTP request, so we don't
            // need to bother with a clean response.
            if (!$USER->can_publish_artefact($obj)) {
                throw new AccessDeniedException();
            }
        }
    }
}

function designskinform_submit(Pieform $form, $values) {
    global $USER, $SESSION, $redirect;

    $siteskin = (isset($values['viewskin_access']) && ($values['viewskin_access'] == 'site'));
    // Only an admin can create a site skin
    if ($siteskin && !$USER->get('admin')) {
        $values['viewskin_access'] = 'private';
    }

    // Join all view skin css/formating data to array...
    $skin = array();
    $skin['body_background_color'] = $values['body_background_color'];
    if (!$siteskin) {
        $skin['body_background_image'] = $values['body_background_image'];
        $skin['body_background_repeat'] = $values['body_background_repeat'];
        $skin['body_background_attachment'] = $values['body_background_attachment'];
        $skin['body_background_position'] = $values['body_background_position'];
    }
    $skin['header_background_color'] = $values['header_background_color']; // TODO remove this
    $skin['header_text_font_color'] = $values['header_text_font_color']; // TODO remove this
    $skin['header_link_normal_color'] = $values['header_link_normal_color']; // TODO remove this
    $skin['header_link_normal_underline'] = $values['header_link_normal_underline']; // TODO remove this
    $skin['header_link_hover_color'] = $values['header_link_hover_color']; // TODO remove this
    $skin['header_link_hover_underline'] = $values['header_link_hover_underline']; // TODO remove this
    $skin['header_logo_image'] = $values['header_logo_image']; // TODO remove this
    $skin['view_background_color'] = $values['view_background_color']; // TODO remove this
    if (!$siteskin) {  // TODO remove this
        $skin['view_background_image'] = $values['view_background_image'];
        $skin['view_background_repeat'] = $values['view_background_repeat'];
        $skin['view_background_attachment'] = $values['view_background_attachment'];
        $skin['view_background_position'] = $values['view_background_position'];
        $skin['view_background_width'] = $values['view_background_width'];
    }
    $skin['view_text_font_family'] = $values['view_text_font_family'];
    $skin['view_heading_font_family'] = $values['view_heading_font_family'];
    $skin['view_text_font_size'] = $values['view_text_font_size'];
    $skin['view_text_font_color'] = $values['view_text_font_color'];
    $skin['view_text_heading_color'] = $values['view_text_heading_color'];
    $skin['view_text_emphasized_color'] = $values['view_text_emphasized_color'];
    $skin['view_link_normal_color'] = $values['view_link_normal_color'];
    $skin['view_link_normal_underline'] = $values['view_link_normal_underline'];
    $skin['view_link_hover_color'] = $values['view_link_hover_color'];
    $skin['view_link_hover_underline'] = $values['view_link_hover_underline'];
    $skin['view_table_border_color'] = $values['view_table_border_color']; // TODO remove this
    $skin['view_table_header_color'] = $values['view_table_header_color']; // TODO remove this
    $skin['view_table_header_text_color'] = $values['view_table_header_text_color']; // TODO remove this
    $skin['view_table_odd_row_color'] = $values['view_table_odd_row_color']; // TODO remove this
    $skin['view_table_even_row_color'] = $values['view_table_even_row_color']; // TODO remove this
    $skin['view_button_normal_color'] = $values['view_button_normal_color']; // TODO remove this
    $skin['view_button_hover_color'] = $values['view_button_hover_color']; // TODO remove this
    $skin['view_button_text_color'] = $values['view_button_text_color']; // TODO remove this
    $skin['view_custom_css'] = clean_css($values['view_custom_css'], $preserve_css=true);

    $viewskin = array();
    $viewskin['id'] = $values['id'];
    if ($values['viewskin_title'] <> '') {
        $viewskin['title'] = $values['viewskin_title'];
    }
    $viewskin['description'] = $values['viewskin_description'];
    $viewskin['owner'] = $USER->get('id');
    $viewskin['type'] = $values['viewskin_access'];
    $viewskin['viewskin'] = $skin;

    Skin::create($viewskin);

    $SESSION->add_ok_msg(get_string('skinsaved', 'skin'));
    redirect($redirect);
}
