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
 * @subpackage artefact-internal-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/*
 * For more information about internal LEAP export, see:
 * http://wiki.mahara.org/Developer_Area/Import//Export/LEAP_Export/Internal_Artefact_Plugin
 */

class LeapExportInternal extends LeapExportArtefactPlugin {
    public function get_export_xml() {
        $element = new LeapExportElementInternal($this->exporter, $this->artefacts);
        return $element->get_export_xml();
    }
}

class LeapExportElementInternal extends LeapExportElement {

    protected $artefacts = array();

    public function __construct(PluginExportLeap $exporter, array $artefacts) {
        parent::__construct(null, $exporter);
        $this->artefacts = $artefacts;
        $this->assign_smarty_vars();
    }

    public function assign_smarty_vars() {
        $user = $this->get('exporter')->get('user');
        $userid = $user->get('id');
        $updated = get_record_sql('select '.db_format_tsfield('max(mtime)', 'mtime').' from {artefact} a join {artefact_installed_type} t on a.artefacttype = t.name where t.plugin = \'internal\'');
        $this->smarty->assign('artefacttype', 'internal');
        $this->smarty->assign('artefactplugin', 'internal');
        $this->smarty->assign('title', display_name($user, $user));
        $this->smarty->assign('updated', PluginExportLeap::format_rfc3339_date($updated->mtime));
        // If this ID is changed, you'll have to change it in author.tpl too
        $this->smarty->assign('id', 'portfolio:artefactinternal');
        $this->smarty->assign('leaptype', $this->get_leap_type());
        $persondata = array();
        $spacialdata = array();
        usort($this->artefacts, array($this, 'artefact_sort'));
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

        // Grab profile icons and link to them, making sure the default is first
        if ($icons = get_column_sql("SELECT id
            FROM {artefact}
            WHERE artefacttype = 'profileicon'
            AND \"owner\" = ?
            ORDER BY id = (
                SELECT profileicon FROM {usr} WHERE id = ?
            ) DESC, id", array($userid, $userid))) {
            foreach ($icons as $icon) {
                $icon = artefact_instance_from_id($icon);
                $this->add_artefact_link($icon, 'related');
            }
            $this->smarty->assign('links', $this->links);
        }

        if (!$categories = $this->get_categories()) {
            $categories = array();
        }
        $this->smarty->assign('categories', $categories);
    }

    public function get_template_path() {
        return 'export:leap/internal:entry.tpl';
    }

    public function get_leap_type() {
        return 'person';
    }

    public function get_categories() {
        return array(
            array(
                'scheme' => 'person_type',
                'term'   => 'Self',
            )
        );
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

    /**
     * Sort artefacts, making sure that the primary e-mail address comes first,
     * then other e-mail addresses, then everything else.
     *
     * Semantically, this means our exports have the most important e-mails
     * first, which we use at import time to make sure we set the primary
     * e-mail correctly.
     */
    private function artefact_sort($a, $b) {
        static $emailcache = array();

        $atype = $a->get('artefacttype');
        $btype = $b->get('artefacttype');
        if ($atype == 'email') {
            if ($btype == 'email') {
                $user = $this->get('exporter')->get('user');
                $userid = $user->get('id');
                if (!isset($emailcache[$userid])) {
                    $emailcache[$userid] = $user->get('email');
                }

                if ($a->get('title') == $emailcache[$userid]) {
                    return -1;
                }
                else if ($b->get('title') == $emailcache[$userid]) {
                    return 1;
                }
            }
            else {
                return -1;
            }
        }
        else {
            if ($btype == 'email') {
                return 1;
            }
        }
        return $atype > $btype;
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
