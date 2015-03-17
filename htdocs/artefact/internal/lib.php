<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginArtefactInternal extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'firstname',
            'lastname',
            'studentid',
            'preferredname',
            'introduction',
            'email',
            'officialwebsite',
            'personalwebsite',
            'blogaddress',
            'address',
            'town',
            'city',
            'country',
            'homenumber',
            'businessnumber',
            'mobilenumber',
            'faxnumber',
            'occupation',
            'industry',
            'html',
            'socialprofile',
        );
    }

    public static function get_profile_artefact_types() {
        return array(
            'firstname',
            'lastname',
            'studentid',
            'preferredname',
            'introduction',
            'email',
            'officialwebsite',
            'personalwebsite',
            'blogaddress',
            'address',
            'town',
            'city',
            'country',
            'homenumber',
            'businessnumber',
            'mobilenumber',
            'faxnumber',
            'occupation',
            'industry',
            'socialprofile',
        );
    }

    public static function get_contactinfo_artefact_types() {
        return array(
            'email',
            'officialwebsite',
            'personalwebsite',
            'blogaddress',
            'address',
            'town',
            'city',
            'country',
            'homenumber',
            'businessnumber',
            'mobilenumber',
            'faxnumber',
            'socialprofile',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'internal';
    }

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'internal');
    }

    public static function menu_items() {
        return array(
            'content/profile' => array(
                'path' => 'content/profile',
                'url' => 'artefact/internal/index.php',
                'title' => get_string('profile', 'artefact.internal'),
                'weight' => 10,
            ),
            'content/notes' => array(
                'path' => 'content/notes',
                'url' => 'artefact/internal/notes.php',
                'title' => get_string('Notes', 'artefact.internal'),
                'weight' => 60,
            ),
        );
    }

    public static function submenu_items() {
        $tabs = array(
            'profile' => array(
                'page'  => 'profile',
                'url'   => 'artefact/internal/index.php',
                'title' => get_string('aboutme', 'artefact.internal'),
            ),
            'contact' => array(
                'page'  => 'contact',
                'url'   => 'artefact/internal/index.php?fs=contact',
                'title' => get_string('contact', 'artefact.internal'),
            ),
            'social' => array(
                'page'  => 'social',
                'url'   => 'artefact/internal/index.php?fs=social',
                'title' => get_string('social', 'artefact.internal'),
            ),
            'general' => array(
                'page'  => 'general',
                'url'   => 'artefact/internal/index.php?fs=general',
                'title' => get_string('general'),
            ),
        );
        if (!get_field('artefact_installed_type', 'name', 'name', 'socialprofile')) {
            unset($tabs['social']);
        }
        if (defined('INTERNAL_SUBPAGE') && isset($tabs[INTERNAL_SUBPAGE])) {
            $tabs[INTERNAL_SUBPAGE]['selected'] = true;
        }
        return $tabs;
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'clean_email_validations',
                'hour'         => '4',
                'minute'       => '10',
            ),
        );
    }

    public static function clean_email_validations() {
        delete_records_select('artefact_internal_profile_email', 'verified=0 AND expiry IS NOT NULL AND expiry < ?', array(db_format_timestamp(time())));
    }

    public static function sort_child_data($a, $b) {
        return strnatcasecmp($a->text, $b->text);
    }

    public static function can_be_disabled() {
        return false;
    }

    public static function get_artefact_type_content_types() {
        return array(
            'introduction'  => array('text'),
            'html'          => array('text'),
            'socialprofile' => array('html'),
        );
    }

    /**
     * This method is provided by the plugin class so it can be used by the
     * profileinfo and contactinfo blocktypes. See the blocktypes'
     * export_blockinstance_config_leap method for more information.
     *
     * Leap2A export doesn't export profile related artefacts as entries, so we
     * need to take that into account when exporting config for it.
     *
     * @param BlockInstance $bi The blockinstance to export the config for.
     * @return array The config for the blockinstance
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        static $cache = array();
        $owner = $bi->get_view()->get('owner');

        // This blocktype is only allowed in personal views
        if (!$owner) {
            return array();
        }

        if (!isset($cache[$owner])) {
            $cache[$owner] = get_records_sql_assoc("SELECT id, artefacttype, title
                FROM {artefact}
                WHERE \"owner\" = ?
                AND artefacttype IN (
                    SELECT name
                    FROM {artefact_installed_type}
                    WHERE plugin = 'internal'
            )", array($owner));
        }

        $configdata = $bi->get('configdata');

        $result = array();
        if (is_array($configdata)) {
            // Convert the actual profile artefact IDs to their field names
            if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
                $result['fields'] = array();
                foreach ($configdata['artefactids'] as $id) {
                    $result['fields'][] = $cache[$owner][$id]->artefacttype;
                }
                $result['fields'] = json_encode(array($result['fields']));
            }

            // Email addresses are not entries in Leap2A (they're elements on
            // the persondata element), so we export the actual address here
            // instead of an artefact ID.
            if (!empty($configdata['email']) && isset($cache[$owner][$configdata['email']])) {
                $result['email'] = json_encode(array($cache[$owner][$configdata['email']]->title));
            }

            if (!empty($configdata['profileicon'])) {
                $result['artefactid'] = json_encode(array(intval($configdata['profileicon'])));
            }

            if (isset($configdata['introtext'])) {
                $result['introtext'] = json_encode(array($configdata['introtext']));
            }
        }

        return $result;
    }

    /**
     * This method is provided by the plugin class so it can be used by the
     * profileinfo and contactinfo blocktypes. See the blocktypes'
     * import_create_blockinstance_leap method for more information.
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        static $cache = array();
        $configdata = array();

        // This blocktype is only allowed in personal views
        if (empty($viewconfig['owner'])) {
            return;
        }
        $owner = $viewconfig['owner'];

        if (isset($biconfig['config']) && is_array($biconfig['config'])) {
            $impcfg = $biconfig['config'];
            if (isset($impcfg['fields']) && is_array($impcfg['fields'])) {
                // Convert the fields to their artefact ids
                $configdata['artefactids'] = array();
                foreach ($impcfg['fields'] as $field) {
                    if (!isset($cache[$owner])) {
                        $cache[$owner] = get_records_sql_assoc("SELECT artefacttype, id
                            FROM {artefact}
                            WHERE \"owner\" = ?
                            AND artefacttype IN (
                                SELECT name
                                FROM {artefact_installed_type}
                                WHERE plugin = 'internal'
                        )", array($owner));
                    }

                    if (isset($cache[$owner][$field])) {
                        $configdata['artefactids'][] = $cache[$owner][$field]->id;
                    }
                }
            }

            if (!empty($impcfg['email'])) {
                if ($artefactid = get_field('artefact_internal_profile_email', 'artefact', 'owner', $owner, 'email', $impcfg['email'])) {
                    $configdata['email'] = $artefactid;
                }
            }

            if (!empty($impcfg['artefactid'])) {
                $configdata['profileicon'] = intval($impcfg['artefactid']);
            }

            if (isset($impcfg['introtext'])) {
                $configdata['introtext'] = $impcfg['introtext'];
            }
        }
        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $configdata,
            )
        );

        return $bi;
    }

    public static function progressbar_additional_items() {
        return array(
                (object)array(
                    'name' => 'joingroup',
                    'title' => get_string('progressbaritem_joingroup', 'artefact.internal'),
                    'plugin' => 'internal',
                    'active' => true,
                    'iscountable' => true,
                    'is_metaartefact' => true,
                ),
                (object)array(
                    'name' => 'makefriend',
                    'title' => get_string('progressbaritem_makefriend', 'artefact.internal'),
                    'plugin' => 'internal',
                    'active' => true,
                    'iscountable' => true,
                    'is_metaartefact' => true,
                )
        );
    }

    public static function progressbar_metaartefact_count($name) {
        global $USER;

        $meta = new StdClass();
        $meta->artefacttype = $name;
        $meta->completed = 0;
        switch ($name) {
            case 'joingroup':
                $sql = "SELECT COUNT(*) AS completed
                         FROM {group_member}
                       WHERE member = ?";
                $count = get_records_sql_array($sql, array($USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            case 'makefriend':
                // We count make friend as either initiating or accepting a friendship
                $sql = "SELECT COUNT(*) AS completed
                         FROM {usr_friend}
                       WHERE usr1 = ? OR usr2 = ?";
                $count = get_records_sql_array($sql, array($USER->get('id'), $USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            default:
                return false;
        }
        return $meta;
    }

    public static function progressbar_link($artefacttype) {
        switch ($artefacttype) {
            case 'firstname':
            case 'lastname':
            case 'studentid':
            case 'preferredname':
            case 'introduction':
                return 'artefact/internal/index.php';
                break;
            case 'email':
            case 'officialwebsite':
            case 'personalwebsite':
            case 'blogaddress':
            case 'address':
            case 'town':
            case 'city':
            case 'country':
            case 'homenumber':
            case 'businessnumber':
            case 'mobilenumber':
            case 'faxnumber':
                return 'artefact/internal/index.php?fs=contact';
                break;
            case 'socialprofile':
                return 'artefact/internal/index.php?fs=social';
                break;
            case 'occupation':
            case 'industry':
                return 'artefact/internal/index.php?fs=general';
                break;
            case 'joingroup':
                return 'group/find.php';
                break;
            case 'makefriend':
                return 'user/find.php';
                break;
            default:
                return 'view/index.php';
        }
    }
}

class ArtefactTypeProfile extends ArtefactType {

    /**
     * overriding this because profile fields
     * are unique in that except for email, you only get ONE
     * so if we don't get an id, we still need to go look for it.
     * On the other hand, if our caller knows the artefact is new,
     * we can skip the query.
     */
    public function __construct($id=0, $data=null, $new = FALSE) {
        $type = $this->get_artefact_type();
        if (!empty($id) || $type == 'email' || $type == 'socialprofile') {
            return parent::__construct($id, $data);
        }
        if (!empty($data['owner'])) {
            if (!$new && $a = get_record('artefact', 'artefacttype', $type, 'owner', $data['owner'])) {
                return parent::__construct($a->id, $a);
            }
            else {
                $this->owner = $data['owner'];
            }
        }
        $this->ctime = time();
        $this->atime = time();
        $this->artefacttype = $type;
        if (empty($id)) {
            $this->dirty = true;
            $this->ctime = $this->mtime = time();
            if (empty($data)) {
                $data = array();
            }
            foreach ((array)$data as $field => $value) {
                if (property_exists($this, $field)) {
                    $this->{$field} = $value;
                }
            }
        }
    }

    public function set($field, $value) {
        if ($field == 'title' && empty($value)) {
            return $this->delete();
        }
        return parent::set($field, $value);
    }

    public static function get_icon($options=null) {

    }

    public static function is_singular() {
        return true;
    }

    public static function get_all_fields() {
        $out = array(
            'firstname'       => 'text',
            'lastname'        => 'text',
            'studentid'       => 'text',
            'preferredname'   => 'text',
            'introduction'    => 'wysiwyg',
            'email'           => 'emaillist',
            'officialwebsite' => 'text',
            'personalwebsite' => 'text',
            'blogaddress'     => 'text',
            'address'         => 'textarea',
            'town'            => 'text',
            'city'            => 'text',
            'country'         => 'select',
            'homenumber'      => 'text',
            'businessnumber'  => 'text',
            'mobilenumber'    => 'text',
            'faxnumber'       => 'text',
            'occupation'      => 'text',
            'industry'        => 'text',
            'maildisabled'    => 'html',
        );
        $social = array();
        if (get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
            $social = array(
                'socialprofile'   => 'html',
            );
        }
        $out = array_merge($out, $social);
        return $out;
    }

    public static function get_field_element_data() {
        return array(
            'firstname'       => array('rules' => array('maxlength' => 50)),
            'lastname'        => array('rules' => array('maxlength' => 50)),
            'studentid'       => array('rules' => array('maxlength' => 50)),
            'preferredname'   => array('rules' => array('maxlength' => 50)),
        );
    }

    public static function get_mandatory_fields() {
        $m = array();
        $all = self::get_all_fields();
        $alwaysm = self::get_always_mandatory_fields();
        if ($man = get_config_plugin('artefact', 'internal', 'profilemandatory')) {
            $mandatory = explode(',', $man);
        }
        else {
            $mandatory = array();
        }
        // If socialprofile is disabled, we need to remove any fields that may
        // have been selected when it was enabled.
        // If socialprofile is enabled, we need to remove any fields that my
        // have been selected when it was disabled.
        $need_to_update = false;
        foreach ($mandatory as $mf) {
            if (isset($all[$mf])) {
                $m[$mf] = $all[$mf];
            }
            else {
                $need_to_update = true;
            }
        }
        if ($need_to_update) {
            // We need to save the config settings for the mandatory fields for the plugin.
            set_config_plugin('artefact', 'internal', 'profilemandatory', join(',', array_keys($m)));
        }
        return array_merge($m, $alwaysm);
    }

    public static function get_always_mandatory_fields() {
        return array(
            'firstname' => 'text',
            'lastname'  => 'text',
            'email'     => 'emaillist',
        );
    }

    public static function get_all_searchable_fields() {
        return array(
            'firstname'       => 'text',
            'lastname'        => 'text',
            'studentid'       => 'text',
            'preferredname'   => 'text',
            'email'           => 'emaillist',
        );
    }

    public static function get_always_searchable_fields() {
        return array(
            'firstname'       => 'text',
            'lastname'        => 'text',
            'preferredname'   => 'text',
        );
    }

    public static function get_searchable_fields() {
        if ($pub = get_config_plugin('artefact', 'internal', 'profilepublic')) {
            $public = explode(',', $pub);
        }
        else {
            $public = array();
        }

        $all      = self::get_all_searchable_fields();
        $selected = self::get_always_searchable_fields();

        // If socialprofile is disabled, we need to remove any fields that may
        // have been selected when it was enabled.
        // If socialprofile is enabled, we need to remove any fields that my
        // have been selected when it was disabled.
        $need_to_update = false;
        foreach ($public as $pf) {
            if (isset($all[$pf])) {
                $selected[$pf] = $all[$pf];
            }
            else {
                $need_to_update = true;
            }
        }
        if ($need_to_update) {
            set_config_plugin('artefact', 'internal', 'profilepublic', join(',', array_keys($selected)));
        }

        return $selected;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $allmandatory    = self::get_all_fields();
        $alwaysmandatory = self::get_always_mandatory_fields();
        $mandatory       = self::get_mandatory_fields();

        $mandatoryfields = array();
        foreach (array_keys($allmandatory) as $field) {
            $mandatoryfields[$field] = array(
                'title'        => get_string($field, 'artefact.internal'),
                'value'        => $field,
                'defaultvalue' => isset($alwaysmandatory[$field]) || isset($mandatory[$field]),
                'disabled'     => isset($alwaysmandatory[$field]),
            );
        }

        $allsearchable    = self::get_all_searchable_fields();
        $alwayssearchable = self::get_always_searchable_fields();
        $searchable       = self::get_searchable_fields();

        $searchablefields = array();
        foreach (array_keys($allsearchable) as $field) {
            $searchablefields[$field] = array(
                'title'        => get_string($field, 'artefact.internal'),
                'value'        => $field,
                'defaultvalue' => isset($alwayssearchable[$field]) || isset($searchable[$field]),
                'disabled'     => isset($alwayssearchable[$field]),
            );
        }

        $form = array(
            'elements'   => array(
                'mandatory' =>  array(
                    'title'        => get_string('mandatoryfields', 'artefact.internal'),
                    'description'  => get_string('mandatoryfieldsdescription', 'artefact.internal'),
                    'help'         => true,
                    'type'         => 'checkboxes',
                    'elements'     => $mandatoryfields,
                    'options'      => $allmandatory, // Only the keys are used by validateoptions
                    'rules'        => array('validateoptions' => true),
                ),
                'searchable' =>  array(
                    'title'        => get_string('searchablefields', 'artefact.internal'),
                    'description'  => get_string('searchablefieldsdescription', 'artefact.internal'),
                    'help'         => true,
                    'type'         => 'checkboxes',
                    'elements'     => $searchablefields,
                    'options'      => $allsearchable, // Only the keys are used by validateoptions
                    'rules'        => array('validateoptions' => true),
                ),
            ),
        );

        return $form;
    }

    public function save_config_options($form, $values) {
        $mandatory = array_merge(array_keys(self::get_always_mandatory_fields()), $values['mandatory']);
        set_config_plugin('artefact', 'internal', 'profilemandatory', join(',', $mandatory));
        $searchable = array_merge(array_keys(self::get_always_searchable_fields()), $values['searchable']);
        set_config_plugin('artefact', 'internal', 'profilepublic', join(',', $searchable));
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/internal/index.php',
        );
    }

    public function in_view_list() {
        return false;
    }

    public function display_title($maxlen=null) {
        return get_string($this->get('artefacttype'), 'artefact.internal');
    }
}

class ArtefactTypeProfileField extends ArtefactTypeProfile {
    public static function collapse_config() {
        return 'profile';
    }

    public function render_self($options) {
        return array('html' => hsc($this->title), 'javascript' => null);
    }

    /**
     * Render the import entry request for profile fields
     */
    public static function render_import_entry_request($entry_content) {
        return clean_html($entry_content['title']);
    }
}

class ArtefactTypeCachedProfileField extends ArtefactTypeProfileField {

    public function commit() {
        global $USER;
        parent::commit();
        $field = $this->get_artefact_type();
        if (!$this->deleted) {
            set_field('usr', $field, $this->title, 'id', $this->owner);
        }
        if ($this->owner == $USER->get('id')) {
            $USER->{$field} = $this->title;
        }
    }

    public function delete() {
        parent::delete();
        $field = $this->get_artefact_type();
        set_field('usr', $field, null, 'id', $this->owner);
        $this->title = null;
    }

}

class ArtefactTypeFirstname extends ArtefactTypeCachedProfileField {}
class ArtefactTypeLastname extends ArtefactTypeCachedProfileField {}
class ArtefactTypePreferredname extends ArtefactTypeCachedProfileField {}
class ArtefactTypeEmail extends ArtefactTypeProfileField {
    public static function is_singular() {
        return false;
    }

    public function commit() {

        parent::commit();

        $email_record = get_record('artefact_internal_profile_email', 'owner', $this->owner, 'email', $this->title);

        if(!$email_record) {
            if (record_exists('artefact_internal_profile_email', 'owner', $this->owner, 'artefact', $this->id)) {
                update_record(
                    'artefact_internal_profile_email',
                    (object) array(
                        'email'     => $this->title,
                        'verified'  => 1,
                    ),
                    (object) array(
                        'owner'     => $this->owner,
                        'artefact'  => $this->id,
                    )
                );
                if (get_field('artefact_internal_profile_email', 'principal', 'owner', $this->owner, 'artefact', $this->id)) {
                    update_record('usr', (object) array( 'email' => $this->title, 'id' => $this->owner));
                }
            }
            else {
                $principal = get_record('artefact_internal_profile_email', 'owner', $this->owner, 'principal', 1);

                insert_record(
                    'artefact_internal_profile_email',
                    (object) array(
                        'owner'     => $this->owner,
                        'email'     => $this->title,
                        'verified'  => 1,
                        'principal' => ( $principal ? 0 : 1 ),
                        'artefact'  => $this->id,
                    )
                );
                update_record('usr', (object)array('email' => $this->title, 'id' => $this->owner));
            }
        }
    }

    public function delete() {
        delete_records('artefact_internal_profile_email', 'artefact', $this->id);
        parent::delete();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_internal_profile_email', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    public function render_self($options) {
        if (array_key_exists('link', $options) && $options['link'] == true) {
            $html = '<a href="mailto:' . hsc($this->title) . '">' . hsc($this->title) . '</a>';
        }
        else {
            $html = $this->title;
        }
        return array('html' => $html, 'javascript' => null);
    }

    static public function is_allowed_in_progressbar() {
        return false;
    }
}

class ArtefactTypeStudentid extends ArtefactTypeCachedProfileField {}
class ArtefactTypeIntroduction extends ArtefactTypeProfileField {
    public function render_self($options) {
        return array('html' => clean_html($this->title), 'javascript' => null);
    }
}
class ArtefactTypeWebAddress extends ArtefactTypeProfileField {

    public function commit() {
        $url = $this->get('title');
        if (strlen($url) && strpos($url, '://') == false) {
            $this->set('title', 'http://' . $url);
        }
        parent::commit();
    }

    public function render_self($options) {
        if (array_key_exists('link', $options) && $options['link'] == true) {
            $html = '<a href="' . hsc($this->title) . '">' . hsc($this->title) . '</a>';
        }
        else {
            $html = $this->title;
        }
        return array('html' => $html, 'javascript' => null);
    }
}
class ArtefactTypeOfficialwebsite extends ArtefactTypeWebAddress {}
class ArtefactTypePersonalwebsite extends ArtefactTypeWebAddress {}
class ArtefactTypeBlogAddress extends ArtefactTypeWebAddress {}
class ArtefactTypeAddress extends ArtefactTypeProfileField {
    public function render_self($options) {
        return array('html' => format_whitespace($this->title), 'javascript' => null);
    }
}
class ArtefactTypeTown extends ArtefactTypeProfileField {}
class ArtefactTypeCity extends ArtefactTypeProfileField {}
class ArtefactTypeCountry extends ArtefactTypeProfileField {

    public function render_self($options) {
          $countries = getoptions_country();
          return array('html' => $countries[$this->title], 'javascript' => null);
    }
    /**
     * Render the import entry request for country fields
     */
    public static function render_import_entry_request($entry_content) {
        $countries = getoptions_country();
        return (isset($countries[$entry_content['title']]) ? $countries[$entry_content['title']] : '');
    }
}
class ArtefactTypeHomenumber extends ArtefactTypeProfileField {}
class ArtefactTypeBusinessnumber extends ArtefactTypeProfileField {}
class ArtefactTypeMobilenumber extends ArtefactTypeProfileField {}
class ArtefactTypeFaxnumber extends ArtefactTypeProfileField {}
class ArtefactTypeOccupation extends ArtefactTypeProfileField {}
class ArtefactTypeIndustry extends ArtefactTypeProfileField {}

/* Artefact type for generic html fragments */
class ArtefactTypeHtml extends ArtefactType {

    public function describe_size() {
        return $this->count_attachments() . ' ' . get_string('attachments', 'artefact.blog');
    }

    public function can_have_attachments() {
        return true;
    }

    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_image_url('note', 'artefact/internal');
    }

    public static function is_singular() {
        return false;
    }

    public static function get_links($id) {
        return array(
            '_default' => get_config('wwwroot') . 'artefact/internal/editnote.php?id=' . $id,
        );
    }

    public function render_self($options) {
        $smarty = smarty_core();
        $smarty->assign('title', $this->get('title'));
        $smarty->assign('owner', $this->get('owner'));
        $smarty->assign('tags', $this->get('tags'));
        $smarty->assign('description', $this->get('description'));
        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
        }
        $attachments = $this->get_attachments();
        if ($attachments) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            foreach ($attachments as &$attachment) {
                $f = artefact_instance_from_id($attachment->id);
                $attachment->size = $f->describe_size();
                $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                $attachment->viewpath = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($options['viewid']) ? $options['viewid'] : 0);
                $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                if (isset($options['viewid'])) {
                    $attachment->downloadpath .= '&view=' . $options['viewid'];
                }
            }
            $smarty->assign('attachments', $attachments);
        }
        return array(
            'html' => $smarty->fetch('artefact.tpl'),
            'javascript'=>''
        );
    }

    public static function is_allowed_in_progressbar() {
        return false;
    }

    public function update_artefact_references(&$view, &$template, &$artefactcopies, $oldid) {
        parent::update_artefact_references($view, $template, $artefactcopies, $oldid);
        // 1. Attach copies of the files that were attached to the old note.
        if (isset($artefactcopies[$oldid]->oldattachments)) {
            foreach ($artefactcopies[$oldid]->oldattachments as $a) {
                if (isset($artefactcopies[$a])) {
                    $this->attach($artefactcopies[$a]->newid);
                }
            }
        }
        // 2. Update embedded images in the note and db
        $regexp = array();
        $replacetext = array();
        if (!empty($artefactcopies[$oldid]->oldembeds)) {
            foreach ($artefactcopies[$oldid]->oldembeds as $a) {
                if (isset($artefactcopies[$a])) {
                    // Change the old image id to the new one
                    $regexp[] = '#<img([^>]+)src="' . get_config('wwwroot') . 'artefact/file/download.php\?file=' . $a . '&embedded=1([^"]+)"#';
                    $replacetext[] = '<img$1src="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactcopies[$a]->newid . '&embedded=1"';
                }
            }
            require_once('embeddedimage.php');
            $newdescription = EmbeddedImage::prepare_embedded_images(
                    preg_replace($regexp, $replacetext, $this->get('description')),
                    'textbox',
                    $this->get('id'),
                    $view->get('group')
                );
            $this->set('description', $newdescription);
        }
    }
}


class ArtefactTypeSocialprofile extends ArtefactTypeProfileField {

    public static $socialnetworks = array(
        'facebook',
        'twitter',
        'tumblr',
        'instagram',
        'pinterest',
        'aim',
        'icq',
        'jabber',
        'skype',
        'yahoo',
    );

    public static function is_singular() {
        return false;
    }

    public function can_have_attachments() {
        return false;
    }

    public function render_self($options) {
        if (array_key_exists('link', $options) && $options['link'] == true) {
            $link = self::get_profile_link($this->title, $this->note);
            if ($link) {
                $html = '<a href="' . hsc($link) . '">' . hsc($this->title) . '</a>';
            }
            else {
                // No valid link, even though you asked for one.
                $html = hsc($this->title);
            }
        }
        else {
            $html = $this->title;
        }
        return array('html' => $html, 'javascript' => null);
    }

    /**
     * Render the import entry request for social profile fields
     */
    public static function render_import_entry_request($entry_content) {
        $html = '<strong>' . $entry_content['description'] . ':</strong>&nbsp;' . $entry_content['title'];
        return clean_html($html);
    }

    /**
     * Get an array of all the social profiles input for this user.
     * @return array of social profiles.
     */
    public function get_social_profiles() {
        global $USER;

        $sql = 'SELECT * FROM {artefact}
            WHERE owner = ? AND artefacttype = ?
            ORDER BY description ASC';

        if (!$data = get_records_sql_array($sql, array($USER->get('id'), 'socialprofile'))) {
            $data = array();
        }

        $data = self::get_profile_icons($data);
        return $data;
    }

    /*
     * Create and return url of input messaging system or return social profile url of input social site.
     *
     * @param string $data The string containing messaging username or user social profile url
     * @param string $type Social profile subtype; one of icq, aim, yahoo, skype, jabber or webpage (default)
     * @return string The URL address
     */
    public static function get_profile_link($data, $type) {

        // If they've entered a full URL, just use that
        if (filter_var($data, FILTER_VALIDATE_URL)) {
            return $data;
        }

        switch ($type) {
            case 'twitter':
                // Strip an "@" sign if they put one on.
                if (strlen($data) && $data[0] == '@') {
                    $data = substr($data, 1);
                }
                $link = 'https://twitter.com/' . hsc($data);
                break;
            case 'instagram':
                // Strip an "@" sign if they put one on.
                if (strlen($data) && $data[0] == '@') {
                    $data = substr($data, 1);
                }
                $link = 'https://instagram.com/' . hsc($data) . '/';
                break;
            case 'pinterest':
                $link = 'https://www.pinterest.com/' . hsc($data) . '/';
                break;
            case 'icq':
                $link = 'http://www.icq.com/people/' . hsc($data);
                break;
            case 'aim':
                $link = 'aim:goim?screenname=' . hsc($data);
                break;
            case 'yahoo':
                $link = 'ymsgr:chat?' . hsc($data);
                break;
            case 'skype':
                $link = 'skype:' . hsc($data) . '?call';
                break;
            case 'jabber':
                $link = 'xmpp:' . hsc($data);
                break;
            default:
                $link = false;
        }

        return $link;
    }

    /**
     * Add favicon of different messaging systems or
     * social sites, contained in the input data.
     * @param array $data with details of the social profile.
     * $data[]->note - the type of social profile (i.e. icq, aim, etc).
     * $data[]->title - display name of the social profile.
     * $data[]->icon - the URL of the icon. Will be populated by this function.
     * @return array of icon details for the specified social profile.
     * $newdata[]->note - originally passed into function.
     * $newdata[]->title - originally passed into function.
     * $newdata[]->icon - the URL of the icon.
     * $newdata[]->link - URL or application call.
     */
    public static function get_profile_icons($data) {
        $newdata = array();
        foreach ($data as $record) {

            $record->link = self::get_profile_link($record->title, $record->note);

            switch ($record->note) {
                case 'facebook':
                    $record->icon = favicon_display_url('facebook.com');
                    break;
                case 'tumblr':
                    $record->icon = favicon_display_url('tumblr.com');
                    break;
                case 'twitter':
                    $record->icon = favicon_display_url('twitter.com');
                    break;
                case 'instagram':
                    $record->icon = favicon_display_url('instagram.com');
                    break;
                case 'pinterest':
                    $record->icon = favicon_display_url('www.pinterest.com');
                    break;
                case 'icq':
                    $record->icon = favicon_display_url('www.icq.com');
                    break;
                case 'aim':
                    $record->icon = favicon_display_url('www.aim.com');
                    break;
                case 'yahoo':
                    $record->icon = favicon_display_url('messenger.yahoo.com');
                    break;
                case 'skype':
                    // Since www.skype.com favicon is not working...
                    $record->icon = favicon_display_url('support.skype.com');
                    break;
                case 'jabber':
                    // Since www.jabber.org favicon is not working...
                    $record->icon = favicon_display_url('planet.jabber.org');
                    break;
                default:
                    // We'll fall back to the "no favicon" default icon
                    $record->icon = favicon_display_url('example.com');

                    // If they've supplied a URL, use its favicon
                    if (filter_var($record->title, FILTER_VALIDATE_URL)) {
                        $url = parse_url($record->title);
                        // Check if $url['host'] actually exists - just in case
                        // it was badly formatted.
                        if (isset($url['host'])) {
                            $record->icon = favicon_display_url($url['host']);
                        }
                    }
            }
            $newdata[] = $record;
        }
        return $newdata;
    }

    public function render_profile_element() {
        $data = self::get_social_profiles();

        // Build pagination for 'socialprofile' artefacts table
        $baseurl = get_config('wwwroot') . 'artefact/internal/index.php' .
                   '?' . http_build_query(array('fs' => 'social'));
        $count   = count($data);
        $limit   = 500;
        $offset  = 0;

        $pagination = build_pagination(array(
            'id'        => 'socialprofiles_pagination',
            'url'       => $baseurl,
            'datatable' => 'socialprofilelist',
            'count'     => $count,
            'limit'     => $limit,
            'offset'    => $offset,
        ));

        // User may delete social profile if:
        //  - there is more than 1 social profile and 'socialprofile' is a mandatory field.
        //  - 'socialprofile' is not mandatory.
        $candelete = true;
        $mandatory_fields = ArtefactTypeProfile::get_mandatory_fields();
        if (isset($mandatory_fields['socialprofile']) && count($data) <= 1) {
            $candelete = false;
        }

        $smarty = smarty_core();
        $smarty->assign('controls', true);
        $smarty->assign('rows', $data);
        $smarty->assign('candelete', $candelete);
        $smarty->assign('pagination', $pagination);

        return array(
            'type' => 'html',
            'value' => $smarty->fetch('artefact:internal:socialprofiles.tpl')
        );
    }

    /**
     * Used in the mandatory fields check during the authentication process.
     */
    public function get_new_profile_elements() {

        $items = array(
            'socialprofile_service' => array(
                'type'         => 'text',
                'title'        => get_string('service', 'artefact.internal'),
                'description'  => get_string('servicedesc', 'artefact.internal'),
                'defaultvalue' => null,
                'size'         => 20,
                'rules'        => array('required' => true),
            ),
            'socialprofile_profiletype' => array(
                'type'        => 'select',
                'title'       => get_string('profiletype', 'artefact.internal'),
                'description' => get_string('profiletypedesc', 'artefact.internal'),
                'options'     => array(
                    'webpage' => get_string('webpage', 'artefact.internal'),
                    'aim'     => get_string('aim', 'artefact.internal'),
                    'icq'     => get_string('icq', 'artefact.internal'),
                    'jabber'  => get_string('jabber', 'artefact.internal'),
                    'skype'   => get_string('skype', 'artefact.internal'),
                    'yahoo'   => get_string('yahoo', 'artefact.internal'),
                ),
                'defaultvalue' => 'webpage',
                'width'        => 171,
                'rules'        => array('required' => true),
            ),
            'socialprofile_profileurl' => array(
                'type'         => 'text',
                'title'        => get_string('profileurl', 'artefact.internal'),
                'description'  => get_string('profileurldesc', 'artefact.internal'),
                'defaultvalue' => null,
                'size'         => 40,
                'rules'        => array('required' => true),
            ),
        );
        $element = array(
            'type'     => 'fieldset',
            'legend'   => get_string('social', 'artefact.internal'),
            'class'    => 'social',
            'elements' => $items,
        );

        return $element;
    }

}
