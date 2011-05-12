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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
            'icqnumber',
            'msnnumber',
            'aimscreenname',
            'yahoochat',
            'skypeusername',
            'jabberusername',
            'occupation',
            'industry',
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
            'icqnumber',
            'msnnumber',
            'aimscreenname',
            'yahoochat',
            'skypeusername',
            'jabberusername',
        );
    }
    
    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'internal';
    }

    public static function menu_items() {
        return array(
            'content/profile' => array(
                'path' => 'content/profile',
                'url' => 'artefact/internal/',
                'title' => get_string('profile', 'artefact.internal'),
                'weight' => 10,
            ),
        );
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
            'introduction' => array('text'),
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
}

class ArtefactTypeProfile extends ArtefactType {

    /**
     * overriding this because profile fields
     * are unique in that except for email, you only get ONE
     * so if we don't get an id, we still need to go look for it
     */
    public function __construct($id=0, $data=null) {
        $type = $this->get_artefact_type();
        if (!empty($id) || $type == 'email') {
            return parent::__construct($id, $data);
        }
        if (!empty($data['owner'])) {
            if ($a = get_record('artefact', 'artefacttype', $type, 'owner', $data['owner'])) {
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
        return array(
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
            'icqnumber'       => 'text',
            'msnnumber'       => 'text',
            'aimscreenname'   => 'text',
            'yahoochat'       => 'text',
            'skypeusername'   => 'text',
            'jabberusername'  => 'text',
            'occupation'      => 'text',
            'industry'        => 'text',
            'maildisabled'    => 'html',
        );
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
        foreach ($mandatory as $mf) {
            $m[$mf] = $all[$mf];
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

    public static function get_public_fields() {
        $all = self::get_all_fields();
        $alwaysp = self::get_always_public_fields();
        $p = array();
        if ($pub = get_config_plugin('artefact', 'internal', 'profilepublic')) {
            $public = explode(',', $pub);
        }
        else {
            $public = array();
        }
        foreach ($public as $pf) {
            $p[$pf] = $all[$pf];
        }
        return array_merge($p, $alwaysp);
    }

    public static function get_always_public_fields() {
        return array(
            'introduction' => 'wysiwyg'
        );
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $mandatory = self::get_mandatory_fields();
        $public = self::get_public_fields();
        $alwaysmandatory = self::get_always_mandatory_fields();
        $alwayspublic = self::get_always_public_fields();
        $form = array(
            'renderer'   => 'multicolumntable',
            'elements'   => array(
                'mandatory' =>  array(
                    'title' => ' ', 
                    'type'  => 'html',
                    'class' => 'header',
                    'value' => get_string('mandatory', 'artefact.internal'),
                ),
                'public' => array(
                    'title' => ' ', 
                    'class' => 'header',
                    'type'  => 'html',
                    'value' => get_string('public', 'artefact.internal'),
                )
            ),
        );

        foreach (array_keys(self::get_all_fields()) as $field) {
            $form['elements'][$field . '_mandatory'] = array(
                'defaultvalue' => ((isset($mandatory[$field])) ? 'checked' : ''),
                'title'        => get_string($field, 'artefact.internal'),
                'type'         => 'checkbox',
            );
            if (isset($alwaysmandatory[$field])) {
                $form['elements'][$field . '_mandatory']['value'] = 'checked';
                $form['elements'][$field . '_mandatory']['disabled'] = true;
            }
            $form['elements'][$field . '_public'] = array(
                'defaultvalue' => ((isset($public[$field])) ? 'checked' : ''),
                'title'        => get_string($field, 'artefact.internal'),
                'type'         => 'checkbox',

            );
            if (isset($alwayspublic[$field])) {
                $form['elements'][$field . '_public']['value'] = 'checked';
                $form['elements'][$field . '_public']['disabled'] = true;
            }
        }

        return $form;
    }

    public function save_config_options($values) {
        $mandatory = '';
        $public = '';
        foreach ($values as $field => $value) {
            if (($value == 'on' || $value == 'checked')
                && preg_match('/([a-zA-Z]+)_(mandatory|public)/', $field, $matches)) {
                if (empty($$matches[2])) {
                    $$matches[2] = $matches[1];
                } 
                else {
                    $$matches[2] .= ',' . $matches[1];
                }
            }
        }
        set_config_plugin('artefact', 'internal', 'profilepublic', $public);
        set_config_plugin('artefact', 'internal', 'profilemandatory', $mandatory);
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/internal/',
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
}

class ArtefactTypeCachedProfileField extends ArtefactTypeProfileField {
    
    public function commit() {
        global $USER;
        parent::commit();
        if (!$this->deleted) {
            $field = $this->get_artefact_type();
            set_field('usr', $field, $this->title, 'id', $this->owner);
            if ($this->owner == $USER->get('id')) {
                $USER->{$field} = $this->title;
            }
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
}

class ArtefactTypeStudentid extends ArtefactTypeProfileField {}
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
}
class ArtefactTypeHomenumber extends ArtefactTypeProfileField {}
class ArtefactTypeBusinessnumber extends ArtefactTypeProfileField {}
class ArtefactTypeMobilenumber extends ArtefactTypeProfileField {}
class ArtefactTypeFaxnumber extends ArtefactTypeProfileField {}
class ArtefactTypeIcqnumber extends ArtefactTypeProfileField {}
class ArtefactTypeMsnnumber extends ArtefactTypeProfileField {}
class ArtefactTypeAimscreenname extends ArtefactTypeProfileField {}
class ArtefactTypeYahoochat extends ArtefactTypeProfileField {}
class ArtefactTypeSkypeusername extends ArtefactTypeProfileField {}
class ArtefactTypeJabberusername extends ArtefactTypeProfileField {}
class ArtefactTypeOccupation extends ArtefactTypeProfileField {}
class ArtefactTypeIndustry extends ArtefactTypeProfileField {}
