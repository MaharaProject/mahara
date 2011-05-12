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
 * @subpackage artefact-internal-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class HtmlExportInternal extends HtmlExportArtefactPlugin {

    private $profileviewexported = false;

    public function dump_export_data() {
        if ($this->exporter->get('viewexportmode') == PluginExport::EXPORT_LIST_OF_VIEWS
            && $this->exporter->get('artefactexportmode') == PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS) {
            // Dont' care about profile information in this case
            return;
        }

        $smarty = $this->exporter->get_smarty('../../', 'internal');
        $smarty->assign('page_heading', get_string('profilepage', 'artefact.internal'));

        // Profile page
        $profileviewid = $this->exporter->get('user')->get_profile_view()->get('id');
        foreach ($this->exporter->get('views') as $viewid => $view) {
            if ($profileviewid == $viewid) {
                $smarty->assign('breadcrumbs', array(array('text' => 'Profile page', 'path' => 'profilepage.html')));
                $view = $this->exporter->get('user')->get_profile_view();
                $outputfilter = new HtmlExportOutputFilter('../../', $this->exporter);
                $smarty->assign('view', $outputfilter->filter($view->build_columns()));

                $content = $smarty->fetch('export:html/internal:profilepage.tpl');
                if (!file_put_contents($this->fileroot . 'profilepage.html', $content)) {
                    throw new SystemException("Unable to write profile page");
                }
                $this->profileviewexported = true;
                break;
            }
        }

        // Generic profile information
        $smarty->assign('page_heading', get_string('profileinformation', 'artefact.internal'));
        $smarty->assign('breadcrumbs', array(array('text' => 'Profile information', 'path' => 'index.html')));

        // Organise profile information by sections, ordered how it's ordered 
        // on the 'edit profile' page
        $sections = array(
            'aboutme' => array(),
            'contact' => array(),
            'messaging' => array(),
            'general' => array(),
        );
        $elementlist = call_static_method('ArtefactTypeProfile', 'get_all_fields');
        $elementlistlookup = array_flip(array_keys($elementlist));
        $profilefields = get_column_sql('SELECT id FROM {artefact} WHERE "owner" = ? AND artefacttype IN ('
            . join(",",array_map(create_function('$a','return db_quote($a);'), array_keys($elementlist)))
            . ")", array($this->exporter->get('user')->get('id')));
        foreach ($profilefields as $id) {
            $artefact = artefact_instance_from_id($id);
            $rendered = $artefact->render_self(array('link' => true));
            if ($artefact->get('artefacttype') == 'introduction') {
                $outputfilter = new HtmlExportOutputFilter('../../', $this->exporter);
                $rendered['html'] = $outputfilter->filter($rendered['html']);
            }
            $sections[$this->get_category_for_artefacttype($artefact->get('artefacttype'))][$artefact->get('artefacttype')] = array(
                'html' => $rendered['html'],
                'weight' => $elementlistlookup[$artefact->get('artefacttype')]
            );
        }

        // Sort the data and then drop the weighting information
        foreach ($sections as &$section) {
            uasort($section, create_function('$a, $b', 'return $a["weight"] > $b["weight"];'));
            foreach ($section as &$data) {
                $data = $data['html'];
            }
        }

        $smarty->assign('sections', $sections);

        $iconid = $this->exporter->get('user')->get('profileicon');
        if ($iconid) {
            $icon = artefact_instance_from_id($iconid);
            $smarty->assign('icon', '<img src="../../static/profileicons/200px-' . PluginExportHtml::sanitise_path($icon->get('title')) . '" alt="Profile Picture">');
        }

        $content = $smarty->fetch('export:html/internal:index.tpl');
        if (!file_put_contents($this->fileroot . 'index.html', $content)) {
            throw new SystemException("Unable to write profile information page");
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $outputfilter = new HtmlExportOutputFilter('.', $this->exporter);
        $smarty->assign('introduction', $outputfilter->filter(get_profile_field($this->exporter->get('user')->get('id'), 'introduction')));
        $smarty->assign('profileviewexported', $this->profileviewexported);
        $iconid = $this->exporter->get('user')->get('profileicon');
        if ($iconid) {
            $icon = artefact_instance_from_id($iconid);
            $smarty->assign('icon', '<img src="static/profileicons/200px-' . PluginExportHtml::sanitise_path($icon->get('title')) . '" alt="Profile Picture">');
        }
        return array(
            'description' => $smarty->fetch('export:html/internal:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 0;
    }

    private function get_category_for_artefacttype($artefacttype) {
        switch ($artefacttype) {
        case 'firstname':
        case 'lastname':
        case 'preferredname':
        case 'studentid':
        case 'introduction':
            return 'aboutme';
        case 'email':
        case 'faxnumber':
        case 'businessnumber':
        case 'homenumber':
        case 'mobilenumber':
        case 'city':
        case 'town':
        case 'address':
        case 'country':
        case 'blogaddress':
        case 'personalwebsite':
        case 'officialwebsite':
            return 'contact';
        case 'jabberusername':
        case 'skypeusername':
        case 'yahoochat':
        case 'aimscreenname':
        case 'msnnumber':
        case 'icqnumber':
            return 'messaging';
        default:
            return 'general';
        }
    }

}
