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
 * @subpackage artefact-plans-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
