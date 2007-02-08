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
 * @subpackage core or plugintype-pluginname
 * @author     Your Name <you@example.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginArtefactResume extends Plugin {
    
    public static function get_artefact_types() {
        return array(
            'coverletter', 
            'contactinformation',
            'personalinformation',
            'employmenthistory',
            'educationhistory',
            'certification',
            'book',
            'membership',
            'interest',
        );
    }

    public static function get_plugin_name() {
        return 'resume';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myresume',
                'link' => '',
                'submenu' => array(
                    array(
                        'name' => 'myresume', 
                        'link' => 'index.php'
                    ),
                    array(
                        'name' => 'mygoals',
                        'link' => 'goals.php',
                    ),
                    array(
                        'name' => 'myskills',
                        'link' => 'skills.php',
                    ),
                ),
            )
        );
    }

    public static function get_toplevel_artefact_types() {
        return array('resume'); 
    }
}

class ArtefactTypeResume extends ArtefactType {

    public function get_icon() {}
    
    public static function is_singular() {
        return false;
    }

    public static function format_child_data($artefact, $pluginname) {
        $a = new StdClass;
        $a->id         = $artefact->id;
        $a->isartefact = true;
        $a->title      = '';
        $a->text       = get_string($artefact->artefacttype, 'artefact.resume'); // $artefact->title;
        $a->container  = (bool) $artefact->container;
        $a->parent     = $artefact->id;
        return $a;
    }
}

class ArtefactTypeCoverletter extends ArtefactTypeResume {
    
    public static function is_singular() {
        return true;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }
}

class ArtefactTypeContactinformation extends ArtefactTypeResume {

    public function get_html($editing=true) {
        $smarty = smarty();
        $fields = ArtefactTypeContactinformation::get_profile_fields();
        foreach ($fields as $f) {
            try {
                $$f = artefact_instance_from_type($f);
                $smarty->assign($f, $$f->render(FORMAT_ARTEFACT_RENDERFULL, array()));
            }
            catch (Exception $e) { }
        }

        $template = 'artefact:resume:fragments/contactinformation.';
        if (!empty($editing)) {
            $template .= 'editing.';
        }
        $template .= 'tpl';
        return $smarty->fetch($template);
    }

    public static function is_singular() {
        return true;
    }

    public static function setup_new($userid) {
        $artefact = new ArtefactTypeContactinformation(null, array(
            'owner' => $userid,
            'title' => get_string('contactinformation', 'artefact.resume')
        ));
        $artefact->commit();
        return $artefact;
    }

    public static function get_profile_fields() {
        static $fields = array(
            'address', 
            'town',
            'city', 
            'country', 
            'faxnumber',
            'businessnumber',
            'homenumber',
            'mobilenumber'
        );
        return $fields;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_LISTCHILDREN,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function listchildren($options) {
        return $this->get_html(false);
    }

    public function render_full($options) {
        return $this->get_html(false);
    }
}

class ArtefactTypePersonalinformation extends ArtefactTypeResume {
    
    private $composites;

    public function __construct($id=0, $data=null) {
        if (empty($id)) {
            $data['title'] = get_string('personalinformation', 'artefact.resume');
        }
        parent::__construct($id, $data);
        $this->composites = ArtefactTypePersonalinformation::get_composite_fields();
        if (!empty($id)) {
            $this->composites = (array)get_record('artefact_resume_personal_information', 'artefact', $id);
        }
    }

    public function set_composite($field, $value) {
        if (!array_key_exists($field, $this->composites)) {
            throw new InvalidArgumentException("Tried to set a non existant composite, $field");
        }
        if ($this->composites[$field] == $value) {
            return true;
        }
        // only set it to dirty if it's changed
        $this->dirty = true;
        $this->mtime = time();
        if ($field == 'dateofbirth') {
            $value = db_format_timestamp($value);
        }
        $this->composites[$field] = $value;
    }   

    public function get_composite($field) {
        return $this->composites[$field];
    }

    public function commit() {
        if (empty($this->dirty)) {
            return true;
        }

        db_begin(); 

        $data = new StdClass;
        foreach ($this->composites as $field => $value) {
            $data->{$field} = $value;
        }   
        $inserting = empty($this->id);
        parent::commit();
        $data->artefact = $this->id;
        if ($inserting) {
            insert_record('artefact_resume_personal_information', $data);
        }
        else {
            update_record('artefact_resume_personal_information', $data, 'artefact');
        }

        db_commit();
    }

    public static function get_composite_fields() {
        static $composites = array(
            'dateofbirth' => '',
            'placeofbirth' => '', 
            'citizenship' => '',
            'visastatus' => '',
            'gender' => '',
            'maritalstatus' => '',
        );
        return $composites;
    }

    public static function is_singular() {
        return true;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_LISTCHILDREN,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }
}

class ArtefactTypeInterest extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }
}


class ArtefactTypeResumeComposite extends ArtefactTypeResume {

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_LISTCHILDREN,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA
        );
    }
}

class ArtefactTypeEmploymenthistory extends ArtefactTypeResumeComposite { }

class ArtefactTypeEducationhistory extends ArtefactTypeResumeComposite { }

class ArtefactTypeCertification extends ArtefactTypeResumeComposite { }

class ArtefactTypeBook extends ArtefactTypeResumeComposite { }

class ArtefactTypeMembership extends ArtefactTypeResumeComposite { } 



?>
