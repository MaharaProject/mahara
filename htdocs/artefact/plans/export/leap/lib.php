<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class LeapExportElementPlan extends LeapExportElement {

    public function get_leap_type() {
        return 'plan';
    }

    public function get_template_path() {
        return 'export:leap/plans:plan.tpl';
    }
}

class LeapExportElementTask extends LeapExportElementPlan {

    public function assign_smarty_vars() {
        parent::assign_smarty_vars();
        $this->smarty->assign('completion', $this->artefact->get('completed') ? 'completed' : 'planned');
    }

    public function get_dates() {
        return array(
            array(
                'point' => 'target',
                'date'  => format_date($this->artefact->get('completiondate'), 'strftimew3cdate'),
            ),
        );
    }
}
