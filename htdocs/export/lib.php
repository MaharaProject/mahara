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
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
* export all views belonging to the user
*/
define('EXPORT_ALL_VIEWS',     'allviews');

/**
* export all artefacts belonging to the user
*/
define('EXPORT_ALL_ARTEFACTS', 'allartefacts');

/**
* just exporting those artefacts that belong to views we selected
*/
define('EXPORT_ARTEFACTS_FOR_VIEWS', 'artefactsforviews');

require_once('view.php');
require_once(get_config('docroot') . '/artefact/lib.php');

/**
* Base class for all Export plugins.
* This is the class that performs all the work for Exports,
* as well as interfacing with the Mahara Plugin API
*/
abstract class PluginExport extends Plugin {

    /**
    * Perform the export and return the path to the resulting file.
    *
    * @return String path to the resulting file (relative to dataroot)
    */
    abstract public function export();

    /**
    *
    * Clean up after yourself - removing any temp files etc
    */
    abstract public function cleanup();

    //  MAIN CLASS DEFINITION

    /**
    * Array of artefacts to export. Set up by constructor.
    */
    protected $artefacts = array();

    /**
    * Array of views to export. Set up by constructor.
    */
    protected $views = array();

    /**
    * User object for the user being exported.
    */
    protected $user;

    /**
     * The time the export was generated.
     *
     * Technically, this is the time at which the export object was created.
     */
    protected $export_time;

    /**
    * Constructor. Sets up all the artefacts and views correctly
    * Also sets up temporary export directories
    *
    * Subclasses can override this if they need to do anything else
    * But must call parent::__construct.
    *
    * For example, the LEAP export plugin sets up smarty
    *
    * @param User $user user the exporting content belongs to
    * @param mixed $views     can be:
    *                         - EXPORT_ALL_VIES
    *                         - array, containing:
    *                             - int - view ids
    *                             - stdclass objects - db rows
    *                             - View objects
    * @param mixed $artefacts can be:
    *                         - EXPORT_ALL_ARTEFACTS
    *                         - EXPORT_ARTEFACTS_FOR_VIEWS
    *                             - int - artefact ids
    *                             - stdclass objects - db rows
    *                             - ArtefactType subclasses
    */
    public function __construct(User $user, $views, $artefacts) {
        $this->export_time = time();
        $this->user = $user;

        $vaextra = '';
        $userid = $this->user->get('id');
        $tmpviews = array();
        $tmpartefacts = array();

        // Get the list of views to export
        if ($views == EXPORT_ALL_VIEWS) {
            $tmpviews = get_column('view', 'id', 'owner', $userid);
        }
        else if (is_array($views)) {
            $vaextra = 'AND va.view IN ( ' . implode(',', array_keys($views)) . ')';
            $tmpviews = $views;
        }
        foreach ($tmpviews as $v) {
            $view = null;
            if ($v instanceof View) {
                $view = $v;
            }
            else if (is_object($v)) {
                $view = new View($v->id, $v);
            }
            else if (is_numeric($v)) {
                $view = new View($v);
            }
            if (is_null($view)) {
                throw new ParamOutOfRangeException("Invalid view $v");
            }
            $this->views[$view->get('id')] = $view;
        }

        // Get the list of artefacts to export
        if ($artefacts == EXPORT_ALL_ARTEFACTS) {
            $tmpartefacts = get_records_array('artefact', 'owner', $userid);
        }
        else if ($artefacts == EXPORT_ARTEFACTS_FOR_VIEWS) {
            $sql = "SELECT va.* from {view_artefact} va
                LEFT JOIN {view} v ON v.id = va.view
                WHERE v.owner = ? $vaextra ORDER BY va.view";
            if ($tmp = get_records_sql_array($sql, array($userid))) {
                foreach ($tmp as $varecord) {
                    $tmpartefacts[] = $varecord->artefact;
                }
            }
        }
        else {
            $tmpartefacts = $artefacts;
        }
        $typestoplugins = get_records_assoc('artefact_installed_type');
        foreach ($tmpartefacts as $a) {
            $artefact = null;
            if ($a instanceof ArefactType) {
                $artefact = $a;
            }
            else if (is_object($a)) {
                $class = generate_artefact_class_name($a->artefacttype);
                safe_require('artefact', $typestoplugins[$a->artefacttype]->plugin);
                $artefact = new $class($a->id, $a);
            }
            else if (is_numeric($a)) {
                $artefact = artefact_instance_from_id($a);
            }
            if (is_null($artefact)) {
                throw new ParamOutOfRangeException("Invalid artefact $a");
            }
            $this->artefacts[$artefact->get('id')] = $artefact;
        }

        // Now set up the temporary export directories
        $this->exportdir = get_config('dataroot')
            . 'export/temporary/'
            . $this->user->get('id')  . '/'
            . time() .  '/';
        if (!check_dir_exists($this->exportdir)) {
            throw new SystemException("Couldn't create the temporary export directory $this->exportdir");
        }
    }

    /**
     * Accessor
     *
     * @param string $field The field to get (see the class definition to find 
     *                      which fields are available)
     */
    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

}

?>
