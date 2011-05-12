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
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once('view.php');
require_once(get_config('docroot') . '/artefact/lib.php');

/**
 * Base class for all Export plugins.
 *
 * This class does some basic setup for export plugins, as well as interfacing 
 * with the Mahara Plugin API. Mostly, the work of generating exports is 
 * delegated to the plugins themselves.
 *
 * TODO: split generation of an archive file from the export() method, 
 * implement zipping the export in a method in this class to reduce 
 * duplication.
 */
abstract class PluginExport extends Plugin {

    /**
     * Export all views owned by this user
     */
    const EXPORT_ALL_VIEWS = -1;

    /**
     * Export only certain views - used internally when a list of views is 
     * passed to the constructor
     */
    const EXPORT_LIST_OF_VIEWS = -2;

    /**
     * Export all artefacts owned by this user
     */
    const EXPORT_ALL_ARTEFACTS = -3;

    /**
     * Export artefacts that are part of the views to be exported
     */
    const EXPORT_ARTEFACTS_FOR_VIEWS = -4;

    /**
     * Export only certain artefacts - used internally when a list of artefacts 
     * is passed to the constructor
     */
    const EXPORT_LIST_OF_ARTEFACTS = -5;

    /**
     * A human-readable title for the export
     */
    abstract public static function get_title();

    /**
     * A human-readable description for the export
     */
    abstract public static function get_description();

    /**
     * Perform the export and return the path to the resulting file.
     *
     * @return string path to the resulting file (relative to dataroot)
     */
    abstract public function export();

    //  MAIN CLASS DEFINITION

    /**
     * List of artefacts to export. Set up by constructor.
     */
    public $artefacts = array();

    /**
     * List of views to export. Set up by constructor.
     */
    public $views = array();

    /**
     * User object for the user being exported.
     */
    protected $user;

    /**
     * Represents the mode for exporting views - one of the class consts 
     * defined above
     */
    protected $viewexportmode;

    /**
     * Represents the mode for exporting artefacts - one of the class consts 
     * defined above
     */
    protected $artefactexportmode;

    /**
     * The time the export was generated.
     *
     * Technically, this is the time at which the export object was created, 
     * not the time at which export() was called.
     */
    protected $exporttime;

    /**
     * Callback to notify when progress is made
     */
    private $progresscallback = null;

    /**
     * Establishes exactly what views and artefacts are to be exported, and
     * sets up temporary export directories
     *
     * Subclasses can override this if they need to do anything else, but
     * they must call parent::__construct.
     *
     * @param User $user       The user to export data for
     * @param mixed $views     can be:
     *                         - PluginExport::EXPORT_ALL_VIEWS
     *                         - array, containing:
     *                             - int - view ids
     *                             - stdclass objects - db rows
     *                             - View objects
     * @param mixed $artefacts can be:
     *                         - PluginExport::EXPORT_ALL_ARTEFACTS
     *                         - PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS
     *                         - array, containing:
     *                             - int - artefact ids
     *                             - stdclass objects - db rows
     *                             - ArtefactType subclasses
     */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null) {
        if (!is_null($progresscallback)) {
            if (is_callable($progresscallback)) {
                $this->progresscallback = $progresscallback;
            }
            else {
                throw new SystemException("The specified progress callback isn't callable");
            }
        }
        $this->notify_progress_callback(0, 'Starting');

        $this->exporttime = time();
        $this->user = $user;

        $userid = $this->user->get('id');
        $tmpviews = array();
        $tmpartefacts = array();

        // Get the list of views to export
        if ($views == self::EXPORT_ALL_VIEWS) {
            $tmpviews = get_column_sql('SELECT id FROM {view} WHERE owner = ? ORDER BY id', array($userid));
            $this->viewexportmode = $views;
        }
        else if (is_array($views)) {
            $tmpviews = $views;
            $this->viewexportmode = self::EXPORT_LIST_OF_VIEWS;
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
            if ($view->get('owner') != $userid) {
                throw new UserException("User $userid does not own view " . $view->get('id'));
            }
            $this->views[$view->get('id')] = $view;
        }

        // Get the list of artefacts to export
        if ($artefacts == self::EXPORT_ALL_ARTEFACTS) {
            $tmpartefacts = get_column_sql('SELECT id
                FROM {artefact}
                WHERE "owner" = ?
            UNION
                SELECT artefact
                FROM {view_artefact}
                WHERE "view" IN (SELECT id FROM {view} WHERE "owner" = ?)
                ORDER BY id', array($userid, $userid));
            $this->artefactexportmode = $artefacts;
        }
        else {
            if ($tmpviews) {
                $sql = "SELECT DISTINCT va.artefact
                    FROM {view_artefact} va
                    LEFT JOIN {view} v ON v.id = va.view
                    WHERE v.owner = ?
                    AND va.view IN ( " . implode(',', array_keys($this->views)) . ")
                    ORDER BY va.artefact";
                $tmpartefacts = (array)get_column_sql($sql, array($userid));

                // Some artefacts are not inside the view, but still need to be exported with it
                $tmpartefacts = array_unique(array_merge($tmpartefacts, $this->get_view_extra_artefacts()));
                $tmpartefacts = artefact_get_descendants($tmpartefacts);
                $tmpartefacts = array_unique(array_merge($tmpartefacts, $this->get_artefact_extra_artefacts($tmpartefacts)));
            }
            if ($artefacts == self::EXPORT_ARTEFACTS_FOR_VIEWS) {
                $this->artefactexportmode = $artefacts;
            }
            else {
                $tmpartefacts = array_unique(array_merge($tmpartefacts, $artefacts));
                $this->artefactexportmode = self::EXPORT_LIST_OF_ARTEFACTS;
            }
        }
        $typestoplugins = get_records_assoc('artefact_installed_type');
        foreach ($tmpartefacts as $a) {
            $artefact = null;
            if ($a instanceof ArefactType) {
                $artefact = $a;
            }
            else if (is_object($a) && isset($a->id)) {
                $artefact = artefact_instance_from_id($a->id);
            }
            else if (is_numeric($a)) {
                $artefact = artefact_instance_from_id($a);
            }
            if (is_null($artefact)) {
                throw new ParamOutOfRangeException("Invalid artefact $a");
            }
            // This check won't work, at the _least_ because at the time of
            // writing, can_view_artefact does not support normal users viewing
            // site files. This check is also pretty damn slow. So think twice
            // before uncommenting it. I presume if you _are_ uncommenting it,
            // it's because you're trying to isloate a security vulnerability
            // where a user can export another user's files or something. In
            // which case you'll be being careful anyway, I hope.
            //if (!$this->user->can_view_artefact($artefact)) {
            //    throw new SystemException("User $userid does not own artefact " . $artefact->get('id'));
            //}
            if ($artefact->exportable()) {
                $this->artefacts[$artefact->get('id')] = $artefact;
            }
        }

        $this->collections = array();
        $collections = get_records_sql_assoc('
            SELECT * FROM {collection} WHERE id IN (
                SELECT collection
                FROM {collection_view}
                WHERE view IN (' . join(',', array_keys($this->views)) . ')
            )',
            array()
        );
        if ($collections) {
            require_once('collection.php');
            foreach ($collections as &$c) {
                $this->collections[$c->id] = new Collection(0, $c);
            }
        }

        // Now set up the temporary export directories
        $this->exportdir = get_config('dataroot')
            . 'export/'
            . $this->user->get('id')  . '/'
            . $this->exporttime .  '/';
        if (!check_dir_exists($this->exportdir)) {
            throw new SystemException("Couldn't create the temporary export directory $this->exportdir");
        }

        $this->notify_progress_callback(10, 'Setup');
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

    /**
     * Notifies the registered progress callback about the progress in generating the export.
     *
     * This is provided as exports can take a long time to generate. Export 
     * plugins are encouraged to call this at least after performing some major 
     * operation, and should always call it saying when the execution of 
     * export() is done. However, it is unnecessary to call it too often.
     *
     * For testing purposes, you may find it useful to register a progress 
     * callback that simply log_debug()s the data, so you can check that the 
     * percentage is always increasing, for example.
     *
     * @param int $percent   The total percentage of the way through generating 
     *                       the export. The base class constructor hands over 
     *                       control claiming 10% of the work is done.
     * @param string $status A string describing the current status of the 
     *                       export - e.g. 'Exporting Artefact (20/75)'
     */
    protected function notify_progress_callback($percent, $status) {
        if ($this->progresscallback) {
            call_user_func_array($this->progresscallback, array(
                $percent, $status
            ));
        }
    }

    /**
     * Artefact plugins can specify additional artefacts required for view export
     */
    protected function get_view_extra_artefacts() {
        $extra = array();
        $plugins = plugins_installed('artefact');
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin->name);
            $classname = generate_class_name('artefact', $plugin->name);
            if (is_callable($classname . '::view_export_extra_artefacts')) {
                if ($artefacts = call_static_method($classname, 'view_export_extra_artefacts', array_keys($this->views))) {
                    $extra = array_unique(array_merge($extra, $artefacts));
                }
            }
        }
        return $extra;
    }

    protected function get_artefact_extra_artefacts(&$artefactids) {
        if (empty($artefactids)) {
            return array();
        }
        $extra = array();
        $plugins = plugins_installed('artefact');
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin->name);
            $classname = generate_class_name('artefact', $plugin->name);
            if (is_callable($classname . '::artefact_export_extra_artefacts')) {
                if ($artefacts = call_static_method($classname, 'artefact_export_extra_artefacts', $artefactids)) {
                    $extra = array_unique(array_merge($extra, $artefacts));
                }
            }
        }
        return $extra;
    }
}

/**
 * Looks in the export staging area in dataroot and deletes old, unneeded 
 * exports.
 */
function export_cleanup_old_exports() {
    require_once('file.php');
    $basedir = get_config('dataroot') . 'export/';

    // If the export directory hasn't been created yet, there's no point
    // running the cron.
    if(!is_dir($basedir)) {
        return;
    }

    $exportdir = new DirectoryIterator($basedir);
    $mintime = time() - (12 * 60 * 60); // delete exports older than 12 hours

    // The export dir contains one directory for each user who has created 
    // an export, named after their UID
    foreach ($exportdir as $userdir) {
        if ($userdir->isDot()) continue;

        // Each user's directory contains one directory for each export 
        // they made, named as the unix timestamp of the time they 
        // generated it
        $udir = new DirectoryIterator($basedir . $userdir->getFilename());
        foreach ($udir as $dir) {
            if ($dir->isDot()) continue;
            if ($dir->getCTime() < $mintime) {
                rmdirr($basedir . $userdir->getFilename() . '/' . $dir->getFilename());
            }
        }
    }
}
