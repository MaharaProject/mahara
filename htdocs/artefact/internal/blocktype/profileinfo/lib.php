<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-profileinfo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeProfileinfo extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.internal/profileinfo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.internal/profileinfo');
    }

    public static function get_categories() {
        return array('internal' => 26000);
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array('js/configform.js');
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        safe_require('artefact', 'internal');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');

        $data = array();
        $data['internalprofiles'] = array();
        $data['socialprofiles'] = array();

        // add in the selected email address
        if (!empty($configdata['email']) && get_field('artefact', 'id', 'id', $configdata['email'])) {
            $configdata['artefactids'][] = $configdata['email'];
        }

        $viewowner = get_field('view', 'owner', 'id', $instance->get('view'));
        $sortorder = array_keys(ArtefactTypeProfile::get_all_fields());
        // Get data about the profile fields in this blockinstance
        if (!empty($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] as $id) {
                try {
                    $artefact = artefact_instance_from_id($id);
                    if (is_a($artefact, 'ArtefactTypeProfile') && $artefact->get('owner') == $viewowner) {
                        $rendered = $artefact->render_self(array('link' => true));
                        $artefacttype = $artefact->get('artefacttype');
                        if ($artefacttype == 'socialprofile') {
                            if (get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile', 'artefactplugin', 'internal')) {
                                $data['socialprofiles'][] = array(
                                    'link' => ArtefactTypeSocialprofile::get_profile_link(
                                        $artefact->get('title'),
                                        $artefact->get('note')),
                                    'title' => $artefact->get('title'),
                                    'description' => $artefact->get('description'),
                                    'note' => $artefact->get('note'),
                                );
                            }
                        }
                        else {
                            if ($artefacttype == 'introduction') {
                                $data['introduction'] = $rendered['html'];
                            }
                            else {
                                $data['internalprofiles'][] = array(
                                    'type' => $artefacttype,
                                    'description' => $rendered['html'],
                                    'order' => array_search($artefacttype, $sortorder),
                                );
                            }
                        }
                    }
                }
                catch (ArtefactNotFoundException $e) {
                    log_debug('Artefact not found when rendering a block instance. '
                        . 'There might be a bug with deleting artefacts of this type? '
                        . 'Original error follows:');
                    log_debug($e->getMessage());
                }
            }
            // Sort internal profiles by how they display on Content -> Profile page
            $orders = array();
            foreach ($data['internalprofiles'] as $key => $row) {
                $orders[$key]  = $row['order'];
            }
            array_multisort($orders, SORT_ASC, $data['internalprofiles']);
            // Sort social profiles alphabetically (in ASC order)
            $description = array();
            foreach ($data['socialprofiles'] as $key => $row) {
                $description[$key]  = $row['description'];
            }
            array_multisort($description, SORT_ASC, $data['socialprofiles']);
        }
        else if ($editing && !empty($configdata['templateids'])) {
            foreach ($configdata['templateids'] as $artefacttype) {
                $data['internalprofiles'][] = array(
                    'type' => $artefacttype,
                    'description' => '',
                    'order' => array_search($artefacttype, $sortorder),
                );
            }
            // Sort internal profiles by how they display on Content -> Profile page
            $orders = array();
            foreach ($data['internalprofiles'] as $key => $row) {
                $orders[$key]  = $row['order'];
            }
            array_multisort($orders, SORT_ASC, $data['internalprofiles']);
        }
        else if ($editing) {
            $data['nodata'] = get_string('noprofilesselectone', 'blocktype.internal/profileinfo');
        }

        // Work out the path to the thumbnail for the profile image
        if (!empty($configdata['profileicon'])) {
            if (!empty($configdata['templateids'])) {
                $downloadpath = get_config('wwwroot') . 'thumb.php?type=profileicon&id=0';
                $smarty->assign('profileiconpath', $downloadpath);
                $smarty->assign('profileiconalt', get_string('profileimagetexttemplate', 'mahara'));
            }
            else {
                $downloadpath = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $configdata['profileicon'] . '&view=' . $instance->get('view');
                $downloadpath .= '&maxwidth=80';
                $smarty->assign('profileiconpath', $downloadpath);
                $smarty->assign('profileiconalt', get_string('profileimagetext', 'mahara', display_default_name(get_user($viewowner))));
            }
        }
        // Override the introduction text if the user has any for this
        // particular blockinstance
        if (!empty($configdata['introtext'])) {
            $data['introduction'] = $configdata['introtext'];
        }

        $smarty->assign('profileinfo', $data);

        return $smarty->fetch('blocktype:profileinfo:content.tpl');
    }

    /**
     * Overrides the standard get_artefacts method to make sure the profileicon
     * is added also.
     *
     * @param BlockInstance $instance The blockinstance to get artefacts for
     * @return array A list of artefact IDs in the blockinstance, or false if
     *               there are none
     */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $return = array();
        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            $return = $configdata['artefactids'];
        }
        if (!empty($configdata['profileicon'])) {
            $return = array_merge($return, array($configdata['profileicon']));
        }

        return $return ? $return : false;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        $form = array();
        $owner = $instance->get_view()->get('owner');
        if (!$owner) {
            $configdata['artefactids'] = array();
            if (isset($configdata['templateids']) && !empty($configdata['templateids'])) {
                $element = self::artefactchooser_element(null, $owner);
                foreach ($element['artefacttypes'] as $key => $type) {
                    if (array_search($type, $configdata['templateids']) !== false) {
                        $configdata['artefactids'][] = $key;
                    }
                }
                $configdata['templateids'] = array();
            }
            $form['blocktemplatehtml'] = array(
                'type' => 'html',
                'value' => get_string('blockinstanceconfigownerchange', 'mahara'),
            );
        }
        // Which fields does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null, $owner);
        if ($owner) {
            // Profile icon
            if (!$result = get_records_sql_array('SELECT a.id, a.artefacttype, a.title, a.note
                FROM {artefact} a
                WHERE (artefacttype = \'profileicon\' OR artefacttype = \'email\')
                AND a.owner = (
                    SELECT "owner"
                    FROM {view}
                    WHERE id = ?
                )
                ORDER BY a.id', array($instance->get('view')))) {
                $result = array();
            }

            $iconoptions = array(
                0 => get_string('dontshowprofileicon', 'blocktype.internal/profileinfo'),
            );
            $emailoptions = array(
                0 => get_string('dontshowemail', 'blocktype.internal/profileinfo'),
            );
            foreach ($result as $profilefield) {
                if ($profilefield->artefacttype == 'profileicon') {
                    $iconoptions[$profilefield->id] = ($profilefield->title) ? $profilefield->title : $profilefield->note;
                }
                else {
                    $emailoptions[$profilefield->id] = $profilefield->title;
                }
            }

            if (count($iconoptions) == 1) {
                $form['noprofileicon'] = array(
                    'type'  => 'html',
                    'title' => get_string('profileicon', 'artefact.file'),
                    'description' => get_string('uploadaprofileicon', 'blocktype.internal/profileinfo', get_config('wwwroot')),
                    'value' => '',
                );
                $form['profileicon'] = array(
                    'type'    => 'hidden',
                    'value'   => 0,
                );
            }
            else {
                $form['profileicon'] = array(
                    'type'    => 'radio',
                    'title'   => get_string('profileicon', 'artefact.file'),
                    'options' => $iconoptions,
                    'defaultvalue' => (isset($configdata['profileicon'])) ? $configdata['profileicon'] : 0,
                );
            }

            $form['email'] = array(
                'type'    => 'radio',
                'title'   => get_string('email', 'artefact.internal'),
                'options' => $emailoptions,
                'defaultvalue' => (isset($configdata['email']) && get_field('artefact', 'id', 'id', $configdata['email'])) ? $configdata['email'] : 0,
            );
        }
        else {
            $form['profileicon'] = array(
                'title' => get_string('profileicon', 'artefact.file'),
                'type'  => 'switchbox',
                'defaultvalue' => (isset($configdata['profileicon'])) ? boolval($configdata['profileicon']) : 0,
            );
            $form['blocktemplate'] = array(
                    'type'    => 'hidden',
                    'value'   => 1,
            );
        }
        // Introduction
        $form['introtext'] = array(
            'type'    => 'wysiwyg',
            'title'   => get_string('introtext', 'blocktype.internal/profileinfo'),
            'description' => get_string('useintroductioninstead', 'blocktype.internal/profileinfo'),
            'defaultvalue' => (isset($configdata['introtext'])) ? $configdata['introtext'] : '',
            'width' => '100%',
            'height' => '150px',
            'rules' => array('maxlength' => 1000000),
        );

        return $form;
    }

    public static function instance_config_save($values, $instance) {
        require_once('embeddedimage.php');
        if (!empty($values['introtext'])) {
            $newtext = EmbeddedImage::prepare_embedded_images($values['introtext'], 'introtext', $instance->get('id'));
            $values['introtext'] = $newtext;
        }
        else {
            EmbeddedImage::delete_embedded_images('introtext', $instance->get('id'));
        }
        if (isset($values['blocktemplate']) && !empty($values['blocktemplate'])) {
            // Need to adjust info to be a template
            $owner = $instance->get_view()->get('owner');
            $values['templateids'] = array();
            $element = self::artefactchooser_element(null, $owner);
            foreach ($element['artefacttypes'] as $key => $type) {
                if (array_search($key, $values['artefactids']) !== false) {
                    $values['templateids'][] = $type;
                }
            }
            $values['artefactids'] = array();
        }

        return $values;
    }

    public static function delete_instance(BlockInstance $instance) {
        require_once('embeddedimage.php');
        EmbeddedImage::delete_embedded_images('introtext', $instance->get('id'));
    }

    public static function artefactchooser_element($default=null, $owner=true) {
        safe_require('artefact', 'internal');
        $artefacttypes = PluginArtefactInternal::get_profile_artefact_types();
        if ($owner) {
            $artefacttypes = array_diff($artefacttypes, array('email'));
            if ($default && is_array($default)) {
                $emails = get_column_sql("SELECT id FROM {artefact} WHERE artefacttype = 'email'
                                          AND id IN (" . join(',', array_map('db_quote', $default)) . ")");
                if ($emails) {
                    $default = array_diff($default, $emails);
                }
            }
        }

        if (!get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
            $artefacttypes = array_diff($artefacttypes, array('socialprofile'));
        }

        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('fieldstoshow', 'blocktype.internal/profileinfo'),
            'defaultvalue' => $default,
            'blocktype' => 'profileinfo',
            'blocktemplate' => empty($owner),
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => false,
            'search'    => false,
            'artefacttypes' => $artefacttypes,
            'template'  => 'artefact:internal:artefactchooser-element.tpl',
        );
    }
    /**
     * Allow the introduction option to be displayed correctly
     */
    public static function artefactchooser_get_element_data($artefact) {

        if ($artefact->artefacttype == 'introduction') {
            unset($artefact->description);
        }
        return $artefact;
    }

    /**
     * Deliberately enforce _no_ sort order. The database will return them in
     * the order they were inserted, which means roughly the order that they
     * are listed in the profile screen
     */
    public static function artefactchooser_get_sort_order() {
        safe_require('artefact', 'internal');
        $artefacttypes = array_diff(PluginArtefactInternal::get_profile_artefact_types(), array('email'));
        if (!get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
            $artefacttypes = array_diff($artefacttypes, array('socialprofile'));
        }
        $sortorder = array();
        foreach ($artefacttypes as $type) {
            $sortorder[] = array('fieldname' => 'artefacttype',
                                 'fieldvalue' => $type,
                                 'order' => 'DESC');
        }
        return $sortorder;
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        safe_require('artefact', 'internal');
        if ($view->get('owner') !== null) {
            $artefacttypes = array_diff(PluginArtefactInternal::get_profile_artefact_types(), array('email'));
            if (!get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
                $artefacttypes = array_diff($artefacttypes, array('socialprofile'));
            }
            if (!empty($configdata['blocktemplate'])) {
                if (!empty($configdata['templateids'])) {
                    $artefactids = get_column_sql('
                        SELECT a.id FROM {artefact} a
                        WHERE a.owner = ? AND a.artefacttype != ? AND a.artefacttype IN (' . join(',', array_map('db_quote', $configdata['templateids'])) . ')', array($view->get('owner'), 'email'));
                    if (in_array('email', $configdata['templateids'])) {
                        if ($newemail = get_field('artefact_internal_profile_email', 'artefact', 'principal', 1, 'owner', $view->get('owner'))) {
                            $configdata['email'] = $newemail;
                        }
                    }
                }
                else {
                    $artefactids = array();
                }
                unset($configdata['blocktemplatehtml']);
                unset($configdata['templateids']);
                unset($configdata['blocktemplate']);
            }
            else {
                $artefactids = get_column_sql('
                    SELECT a.id FROM {artefact} a
                    WHERE a.owner = ? AND a.artefacttype IN (' . join(',', array_map('db_quote', $artefacttypes)) . ')', array($view->get('owner')));
            }
            $configdata['artefactids'] = $artefactids;
            if (isset($configdata['email'])) {
                if ($newemail = get_field('artefact_internal_profile_email', 'artefact', 'principal', 1, 'owner', $view->get('owner'))) {
                    $configdata['email'] = $newemail;
                }
                else {
                    unset($configdata['email']);
                }
            }
            if (isset($configdata['profileicon'])) {
                if ($newicon = get_field('usr', 'profileicon', 'id', $view->get('owner'))) {
                    $configdata['profileicon'] = $newicon;
                }
                else {
                    unset($configdata['profileicon']);
                }
            }
        }
        else {
            $configdata['artefactids'] = array();
        }
        return $configdata;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Profileinfo blocktype is only allowed in personal views, because
     * there's no such thing as group/site profiles
     */
    public static function allowed_in_view(View $view) {
        return true;
    }

    /**
     * Overrides the default implementation so we can export enough information
     * to reconstitute profile information again.
     *
     * Leap2A export doesn't export profile related artefacts as entries, so we
     * need to take that into account when exporting config for it.
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        return PluginArtefactInternal::export_blockinstance_config_leap($bi);
    }

    /**
     * Sister method to export_blockinstance_config_leap (creates block
     * instance based of what that method exports)
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        return PluginArtefactInternal::import_create_blockinstance_leap($biconfig, $viewconfig);
    }

}
