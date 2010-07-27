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
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_plans_upgrade($oldversion=0) {

    if ($oldversion < 2010072300) {
        if (field_exists(new XMLDBTable('artefact_plan'), new XMLDBField('plan'))) {
            execute_sql("ALTER TABLE {artefact_plan} RENAME TO {artefact_task}");
            if (is_postgres()) {
                execute_sql('
                    ALTER TABLE {artefact_task} RENAME COLUMN plan TO task;
                    ALTER TABLE {artefact_task} DROP CONSTRAINT {arteplan_pla_fk};
                    ALTER TABLE {artefact_task} ADD CONSTRAINT {artetask_tas_fk} FOREIGN KEY (task) REFERENCES {artefact}(id);
                    ALTER INDEX {arteplan_pla_pk} RENAME TO {artetask_tas_pk};
                    ALTER INDEX {arteplan_pla_ix} RENAME TO {artetask_tas_ix};
                ');
            }
            else if (is_mysql()) {
                execute_sql('ALTER TABLE {artefact_task} CHANGE plan task TEXT');
                execute_sql("ALTER TABLE {artefact_task} DROP FOREIGN KEY {arteplan_pla_fk}");
                execute_sql("ALTER TABLE {artefact_task} DROP INDEX {arteplan_pla_ix}");
                execute_sql("ALTER TABLE {artefact_task} CHANGE plan task BIGINT(10) DEFAULT NULL");
                execute_sql("ALTER TABLE {artefact_task} ADD CONSTRAINT {artetask_tas_fk} FOREIGN KEY {artetask_tas_ix} (task) REFERENCES {artefact}(id)");
            }
        }
    }

    if ($oldversion < 2010072301) {
        execute_sql("INSERT INTO {artefact_installed_type} (name,plugin) VALUES ('task','plans')");
        execute_sql("UPDATE {artefact} SET artefacttype = 'task' WHERE artefacttype = 'plan'");
    }

    return true;
}

?>
