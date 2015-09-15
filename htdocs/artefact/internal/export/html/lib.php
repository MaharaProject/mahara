<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class HtmlExportInternal extends HtmlExportArtefactPlugin {

    private $profileviewexported = false;

    public function dump_export_data() {
        if (($this->exporter->get('viewexportmode') == PluginExport::EXPORT_LIST_OF_VIEWS
            || $this->exporter->get('viewexportmode') == PluginExport::EXPORT_LIST_OF_COLLECTIONS)
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
                $smarty->assign('view', $outputfilter->filter($view->build_rows(false, true)));

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
            'social' => array(),
            'general' => array(),
        );
        $elementlist = call_static_method('ArtefactTypeProfile', 'get_all_fields');
        $elementlistlookup = array_flip(array_keys($elementlist));
        // Export all profile fields except 'socialprofile'
        unset($elementlist['socialprofile']);
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
        // Export all 'socialprofile' entries
        $socialprofiles = get_column_sql('SELECT id FROM {artefact} WHERE "owner" = ? AND artefacttype = ?', array($this->exporter->get('user')->get('id'), 'socialprofile'));
        $profiles = array();
        foreach ($socialprofiles as $id) {
            $artefact = artefact_instance_from_id($id);
            $rendered = $artefact->render_self(array('link' => true));
            $profiles[] = array('label' => $artefact->get('description'), 'link' => $rendered['html']);
        }
        if (!empty($profiles)) {
            $sections[$this->get_category_for_artefacttype($artefact->get('artefacttype'))][$artefact->get('artefacttype')] = array(
                'html' => $profiles,
                'weight' => $elementlistlookup[$artefact->get('artefacttype')],
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
        case 'socialprofile':
            return 'social';
        default:
            return 'general';
        }
    }

}
