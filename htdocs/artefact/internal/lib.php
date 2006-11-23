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
            'blog',
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
            'file',
            'folder',
            'image',
        );
    }

    public static function get_plugin_name() {
        return 'internal';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myprofile',
                'link' => 'profile/',
            ),
            array(
                'name' => 'myfiles',
                'link' => 'files/',
            ),
        );
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
    }

    public function commit() {
        $this->commit_basic();
    }
    
    public function delete() {
        $this->delete_basic();
    }

    public function render($format, $options) {
        if ($format == ARTEFACT_FORMAT_LISTITEM && $this->title) {
            return $this->title;
        }
        return false;
    }

    public function get_icon() {

    }

    public static function get_render_list() {

    }
    
    public static function can_render_to($format) {

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
            'blog'            => 'text',
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
            'name'       => 'profileprefs',
            'method'     => 'post', 
            'ajaxpost'   => true,
            'plugintype' => 'artefact',
            'pluginname' => 'internal',
            'renderer'   => 'multicolumntable',
            'submitfunction' => 'save_config_options',
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
            )
        );

        $form['elements']['submit'] = array(
            'type' => 'submit',
            'value' =>get_string('save')
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
}

class ArtefactTypeProfileField extends ArtefactTypeProfile {
    public static function collapse_config() {
        return 'profile';
    }
}

class ArtefactTypeCachedProfileField extends ArtefactTypeProfileField {
    
    public function commit() {
        $this->commit_basic();
        $field = $this->get_artefact_type();
        set_field('usr', $field, $this->title, 'id', $this->owner);
    }

    public function delete() {
        $this->delete_basic();
        $field = $this->get_artefact_type();
        set_field('usr', $field, null, 'id', $this->owner);
    }

}

class ArtefactTypeFirstname extends ArtefactTypeCachedProfileField {}
class ArtefactTypeLastname extends ArtefactTypeCachedProfileField {}
class ArtefactTypePreferredname extends ArtefactTypeCachedProfileField {}
class ArtefactTypeEmail extends ArtefactTypeProfileField {
    public function commit() {

        $this->commit_basic();

        $email_record = get_record('artefact_internal_profile_email', 'owner', $this->owner, 'email', $this->title);
        // we've created a new artefact that doesn't have a profile email thingy.
        // we assume that it's a validated email, and set it to primary (if there isn't already one)
        if(!$email_record) {
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

class ArtefactTypeStudentid extends ArtefactTypeProfileField {}
class ArtefactTypeIntroduction extends ArtefactTypeProfileField {}
class ArtefactTypeWebAddress extends ArtefactTypeProfileField {
    public function render($format, $options) {
        if ($format == ARTEFACT_FORMAT_LISTITEM && $this->title) {
            return make_link($this->title);
        }
        return false;
    }
}
class ArtefactTypeOfficialwebsite extends ArtefactTypeWebAddress {}
class ArtefactTypePersonalwebsite extends ArtefactTypeWebAddress {}
class ArtefactTypeBlog extends ArtefactTypeProfileField {}
class ArtefactTypeAddress extends ArtefactTypeProfileField {}
class ArtefactTypeTown extends ArtefactTypeProfileField {}
class ArtefactTypeCity extends ArtefactTypeProfileField {}
class ArtefactTypeCountry extends ArtefactTypeProfileField {
    public function render($format, $options) {
        if ($format == ARTEFACT_FORMAT_LISTITEM && $this->title) {
            $countries = getoptions_country();
            return $countries[$this->title];
        }
        return false;
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

class ArtefactTypeFolder extends ArtefactType {
    public function commit() {
        $this->commit_basic();
    }
    
    public function delete() {
        $this->delete_basic();
    }

    public function render($format, $options) {

    }

    public function get_icon() {

    }

    public static function get_render_list() {

    }
    
    public static function can_render_to($format) {

    }    

    public static function collapse_config() {
        return 'file';
    }
}

class ArtefactTypeFile extends ArtefactType {

    public function commit() {
        $this->commit_basic();
    }
    
    public function delete() {
        $this->delete_basic();
    }

    public function render($format, $options) {

    }

    public function get_icon() {

    }

    public static function get_render_list() {

    }
    
    public static function can_render_to($format) {

    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        return array(); // @todo  
    }
}
class ArtefactTypeImage extends ArtefactTypeFile {
    
    public static function collapse_config() {
        return 'file';
    }
}

?>
