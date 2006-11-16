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
        return array(
            'firstname' => 'text', 
            'lastname'  => 'text', 
            'studentid' => 'text', 
        );
    }

    public static function get_public_fields() {
        return array();
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $mandatory = self::get_mandatory_fields();
        $public = self::get_public_fields();

        $form = array(
            'name'       => 'profileprefs',
            'method'     => 'post', 
            'ajaxpost'   => true,
            'plugintype' => 'artefact',
            'pluginname' => 'internal',
            'elements'   => array()
        );

        foreach (array_keys(self::get_all_fields()) as $field) {
            $form['elements'][$field . 'mandatory'] = array(
                'defaultvalue' => (array_key_exists($field, $mandatory)) ? 'checked' : '',
                'title'        => get_string($field, 'artefact.internal'),
                'type'         => 'checkbox',
            );
            $form['elements'][$field . 'public'] = array(
                'defaultvalue' => (in_array($field, $public)) ? 'checked' : '',
                'title'        => get_string($field, 'artefact.internal'),
                'type'         => 'checkbox',

            );
        }
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

    protected $email_id;
    protected $verified;

    public function __construct($id=0, $data=null) {
        $type = $this->get_artefact_type();

        if (!empty($id)) {
            return parent::__construct($id, $data);
        }

        if (isset($data['owner']) && isset($data['email'])) {
            $a = get_record('artefact_internal_profile_email', 'email', $data['email'], 'owner', $data['owner']);

            if (!$a) {
                throw new ArtefactNotFoundException('Profile Email field for user ' . $data['owner'] . ' email ' . $data['email'] . ' not found');
            }

            $this->title = $a->email;
            $this->verified = $a->verified;
            $this->id = $a->artefact;
            $this->owner = $a->owner;
            $this->artefacttype = $type;

            return;
        }

        return parent::__construct($id, $data);
    }

    public function commit() {
        if ($this->verified) {
            $this->commit_basic();
        }

        if (empty($this->email_id)) {
            $this->id = insert_record(
                'artefact_internal_profile_email',
                (object) array(
                    'owner'    => $this->owner,
                    'email'    => $this->title,
                    'verified' => $this->verified,
                )
            );
        }
        else {
            // update_record('artefact', $fordb, 'id');
        }
    }

    public function delete() {
        if ($this->verified) {
            $this->delete_basic();
        }
    }
}

class ArtefactTypeStudentid extends ArtefactTypeProfileField {}
class ArtefactTypeIntroduction extends ArtefactTypeProfileField {}
class ArtefactTypeOfficialwebsite extends ArtefactTypeProfileField {}
class ArtefactTypePersonalwebsite extends ArtefactTypeProfileField {}
class ArtefactTypeBlog extends ArtefactTypeProfileField {}
class ArtefactTypeAddress extends ArtefactTypeProfileField {}
class ArtefactTypeTown extends ArtefactTypeProfileField {}
class ArtefactTypeCity extends ArtefactTypeProfileField {}
class ArtefactTypeCountry extends ArtefactTypeProfileField {}
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
