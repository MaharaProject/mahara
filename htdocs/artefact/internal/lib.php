<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
            'profileicon'
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
            array(
                'path' => 'profile',
                'url'  => 'artefact/internal/', // @todo possibly do path aliasing and dispatch?
                'title' => get_string('profile', 'artefact.internal'),
                'weight' => 20,
            ),
            array(
                'path' => 'profile/edit',
                'url' => 'artefact/internal/',
                'title' => get_string('editprofile', 'artefact.internal'),
                'weight' => 10,
            ),
            array(
                'path' => 'profile/icons',
                'url' => 'artefact/internal/profileicons.php',
                'title' => get_string('profileicons', 'artefact.internal'),
                'weight' => 11,
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

    public function render_full($options) {
        return array('html' => $this->title,
                     'javascript' => null);
    }

    public function get_icon() {

    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERFULL, FORMAT_ARTEFACT_RENDERMETADATA);
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
        return $p;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $mandatory = self::get_mandatory_fields();
        $public = self::get_public_fields();
        $alwaysmandatory = self::get_always_mandatory_fields();
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
        }

        $form['elements']['emptyrow'] = array(
            'title' => '    ',
            'type' => 'html',
            'value' => '&nbsp;',
        );
        $currentwidth = get_config_plugin('artefact', 'internal', 'profileiconwidth');
        $form['elements']['profileiconwidth'] = array(
            'type' => 'text',
            'size' => 4,
            'suffix' => get_string('widthshort'),
            'title' => get_string('profileiconsize', 'artefact.internal'),
            'defaultvalue' => ((!empty($currentwidth)) ? $currentwidth : 100),
            'rules' => array(
                'required' => true,
                'integer'  => true,
            )
        );
        $currentheight = get_config_plugin('artefact', 'internal', 'profileiconheight');
        $form['elements']['profileiconheight'] = array(
            'type' => 'text',
            'suffix' => get_string('heightshort'),
            'size' => 4,
            'title' => get_string('profileiconsize', 'artefact.internal'),
            'defaultvalue' => ((!empty($currentheight)) ? $currentheight : 100),
            'rules' => array(
                'required' => true,
                'integer'  => true,
            ),
            'help' => true,
        );

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
        set_config_plugin('artefact', 'internal', 'profileiconwidth', $values['profileiconwidth']);
        set_config_plugin('artefact', 'internal', 'profileiconheight', $values['profileiconheight']);
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/internal/',
        );
    }
}

class ArtefactTypeProfileField extends ArtefactTypeProfile {
    public static function collapse_config() {
        return 'profile';
    }

    /**
     * This method is optional, and specifies how child data should be formatted
     * for the artefact tree.
     *
     * It should return a StdClass object, with the following fields set:
     *
     *  - id
     *  - title
     *  - text
     *  - container
     *  - parent
     *
     *  @param object $data The data to reformat. Contains some fields from the
     *                      <kbd>artefact</kbd> table, namely title, artefacttype
     *                      and container
     *  @return object      The reformatted data
     */
    public static function format_child_data($data, $pluginname) {
        $res = new StdClass;
        $res->id         = $data->id;
        $res->title      = $data->title;
        $res->isartefact = true;
        if ($data->artefacttype == 'email') {
            $res->text = get_string('email') . ' - ' . $data->title;
        }
        else {
            $res->text = get_string($data->artefacttype, "artefact.$pluginname");
        }
        $res->container = 0;
        $res->parent    = null;
        return $res;
    }

}

class ArtefactTypeCachedProfileField extends ArtefactTypeProfileField {
    
    public function commit() {
        global $USER;
        parent::commit();
        $field = $this->get_artefact_type();
        set_field('usr', $field, $this->title, 'id', $this->owner);
        if ($this->owner == $USER->get('id')) {
            $USER->{$field} = $this->title;
        }
    }

    public function delete() {
        parent::delete();
        $field = $this->get_artefact_type();
        set_field('usr', $field, null, 'id', $this->owner);
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
            }
        }
    }
}

class ArtefactTypeStudentid extends ArtefactTypeProfileField {}
class ArtefactTypeIntroduction extends ArtefactTypeProfileField {}
class ArtefactTypeWebAddress extends ArtefactTypeProfileField {
    public function listself($options) {
        if ($options['link'] == true) {
            $html = make_link($this->title);
        }
        else {
            $html = $this->title;
        }
        return array('html' => $html, 'javascript' => null);
    }
    public function render_full($options) {
        if ($options['link'] == true) {
            $html = make_link($this->title);
        }
        else {
            $html = $this->title;
        }
        return array('html' => $html, 'javascript' => null);
    }
}
class ArtefactTypeOfficialwebsite extends ArtefactTypeWebAddress {}
class ArtefactTypePersonalwebsite extends ArtefactTypeWebAddress {}
class ArtefactTypeBlogAddress extends ArtefactTypeProfileField {}
class ArtefactTypeAddress extends ArtefactTypeProfileField {}
class ArtefactTypeTown extends ArtefactTypeProfileField {}
class ArtefactTypeCity extends ArtefactTypeProfileField {}
class ArtefactTypeCountry extends ArtefactTypeProfileField {
    public function listself($options) {
        return array('html' => get_string("country.{$this->title}"), 'javascript' => null);
    }
    public function render_full($options) {
        return array('html' => get_string("country.{$this->title}"), 'javascript' => null);
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

class ArtefactTypeProfileIcon extends ArtefactTypeProfileField {
    public static function is_note_private() {
        return true;
    }

    public function render_full($options) {
        $html = '<img src="' . get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $this->id . '"'
            . 'alt="' . hsc($this->title) . '"';
        if (isset($options['width'])) {
            $html .= ' width="' . hsc($options['width']) . '"';
        }
        if (isset($options['height'])) {
            $html .= ' height="' . hsc($options['height']) . '"';
        }
        $html .= '>';
        return array('html' => $html, 'javascript' => null);
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/internal/profileicons.php',
        );
    }
}


?>
