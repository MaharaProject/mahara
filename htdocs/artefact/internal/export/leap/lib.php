<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-internal-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

class LeapExportInternal extends LeapExportArtefactPlugin {


    public function get_export_xml() {
        $element = new LeapExportElementInternal($this->exporter, $this->artefacts);
        return $element->get_export_xml();
    }
}

class LeapExportElementInternal extends LeapExportElement {

    protected $artefacts = array();

    public function __construct(LeapExporter $exporter, array $artefacts) {
        parent::__construct(null, $exporter);
        $this->artefacts = $artefacts;
        $this->assign_smarty_vars();
    }

    public function assign_smarty_vars() {
        $this->smarty->assign('artefacttype', 'internal');
        $this->smarty->assign('artefactplugin', 'internal');
        $this->smarty->assign('title', display_name($this->get('exporter')->get('user'), $this->get('exporter')->get('user')));
        // If this ID is changed, you'll have to change it in author.tpl too
        $this->smarty->assign('id', 'portfolio:artefactinternal');
        $this->smarty->assign('leaptype', $this->get_leap_type());
        $persondata = array();
        $spacialdata = array();
        foreach ($this->artefacts as $a) {
            if (!$data = $this->data_mapping($a)) {
                if ($a->get('artefacttype') == 'introduction') {
                    $this->smarty->assign('contenttype', 'html');
                    $this->smarty->assign('content', clean_html($a->get('title')));
                }
                continue;
            }
            $value = $a->render_self(array());
            $value = $value['html']; // TODO fix this when we non-js stuff
            $data = array_merge(array(
                'value'          => $value,
                'artefacttype'   => $a->get('artefacttype'),
                'artefactplugin' => 'internal', // include this incase something else is injecting
            ), $data);
            if (array_key_exists('spacial', $data)) {
                $spacialdata[] = (object)$data;
            }
            else {
                $data = array_merge($data, array(
                    'label'        => get_string($a->get('artefacttype'), 'artefact.internal'),
                ));
                $persondata[] = (object)$data;
            }
        }
        if ($extras = $this->exporter->get('extrapersondata')) {
            $persondata = array_merge($persondata, $extras);
        }
        $this->smarty->assign('persondata', $persondata);
        $this->smarty->assign('spacialdata', $spacialdata);
    }

    public function get_template_path() {
        return 'export:leap/internal:entry.tpl';
    }

    public function get_leap_type() {
        return 'person';
    }

    public function data_mapping(ArtefactType $artefact) {
        $artefacttype = $artefact->get('artefacttype');
        static $mapping =  array(
            'firstname'       => 'legal_given_name',
            'lastname'        => 'legal_family_name',
            'preferredname'   => 'preferred_given_name',
            'email'           => 'email',
            'blogaddress'     => 'website',
            'personalwebsite' => 'website',
            'officialwebsite' => 'website',
            'mobilenumber'    => 'mobile',
            'businessnumber'  => 'workphone',
            'homenumber'      => 'homephone',
            'faxnumber'       => 'fax'
        );

        static $idmapping  = array(
            'jabberusername'  => 'jabber',
            'skypeusername'   => 'skype',
            'yahoochat'       => 'yahoo',
            'aimscreenname'   => 'aim',
            'msnnumber'       => 'msn',
            'icqnumber'       => 'icq',
        );

        static $spacialmapping = array(
            'country' => 'country',
            'city'    => 'addressline',
            'town'    => 'addressline',
            'address' => 'addressline',
        );

        if (array_key_exists($artefacttype, $mapping)) {
            return array('field' => $mapping[$artefacttype]);
        }
        if (array_key_exists($artefacttype, $idmapping)) {
            return array('field' => 'id', 'service' => $idmapping[$artefacttype]);
        }

        if (array_key_exists($artefacttype, $spacialmapping)) {
            $result = array('spacial' => true, 'type' => $spacialmapping[$artefacttype]);
            if ($artefacttype == 'country') {
                require_once('country.php');
                $result['countrycode'] = Country::iso3166_1alpha2_to_iso3166_1alpha3($artefact->get('title'));
            }
            return $result;
        }
        if ($artefacttype == 'studentid') {
            return array('field' => 'other', 'label' => 'Student ID');
        }
            /*
            'industry     // not part of persondata
            'occupation   // not part of persondata
            'introduction // not part of persondata
            */
        return false;
    }

}

class LeapExportElementInternalNonPerson extends LeapExportElement {
    public function override_plugin_specialcase() {
        return true;
    }

    public function assign_smarty_vars() {
        parent::assign_smarty_vars();
        $this->smarty->assign('title', ucfirst($this->artefact->get('artefacttype')));
    }

    public function get_content() {
        return $this->artefact->get('title');
    }
}

class LeapExportElementIndustry extends LeapExportElementInternalNonPerson { }
class LeapExportElementOccupation extends LeapExportElementInternalNonPerson { }

?>
