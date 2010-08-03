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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class Collection {

    private $id;
    private $name;
    private $description;
    private $owner;
    private $mtime;
    private $ctime;

    public function __construct($id=0, $data=null) {
        global $USER;
        $userid = $USER->get('id');

        if (!empty($id)) {
            $tempdata = get_record('collection','id',$id);
            if (empty($tempdata)) {
                throw new CollectionNotFoundException("Collection with id $id not found");
            }
            if (!empty($data)) {
                $data = array_merge((array)$tempdata, $data);
            }
            else {
                $data = $tempdata; // use what the database has
            }
            $this->id = $id;
        }
        else {
            $this->ctime = time();
            $this->mtime = time();
            $this->owner = $userid;
        }

        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            $this->{$field} = $value;
            $this->mtime = time();
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * Creates a new Collection for the given user.
     *
     * @param array $data
     * @return collection           The newly created Collection
     */
    public static function save($data) {
        $collection = new Collection(0, $data);
        $collection->commit();

        return $collection; // return newly created Collections id
    }

    /**
     * Deletes a Collection
     *
     */
    public function delete() {
        db_begin();
        delete_records('collection_view','collection',$this->id);
        delete_records('collection','id',$this->id);
        db_commit();
    }

    /**
     * This method updates the contents of the collection table only.
     */
    public function commit() {

        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mti.me', 'ctime')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }

        db_begin();

        // if id is not empty we are editing an existing collection
        if (!empty($this->id)) {
            update_record('collection', $fordb, 'id');
        }
        else {
            $id = insert_record('collection', $fordb, 'id', true);
            if ($id) {
                $this->set('id', $id);
            }
        }

        db_commit();
    }

    /**
     * Checks if a Collection has views
     *
     * @return bool
     */
    public function has_views() {
        if (count_records('collection_view','collection',$this->get('id'))) {
            return true;
        }
        return false;
    }

    /**
     * Returns a list of the current users collections
     *
     * @param offset current page to display
     * @param limit how many collections to display per page
     * @return array (count: integer, data: array, offset: integer, limit: integer)
     */
    public static function get_mycollections_data($offset=0, $limit=10) {
        global $USER;

        ($data = get_records_sql_array("
            SELECT c.id, c.description, c.name
                FROM {collection} c
                WHERE c.owner = ?
            ORDER BY c.name, c.ctime ASC
            LIMIT ? OFFSET ?", array($USER->get('id'), $limit, $offset)))
            || ($data = array());

        // ToDo: use a faster less intensive way to do this
        if (!empty($data)) {
            foreach ($data as $d) {
                $master = get_record_sql('SELECT v.id, v.title FROM {view} v JOIN {collection_view} cv ON v.id = cv.view WHERE cv.collection = ? AND cv.master = 1',array($d->id));
                if ($master) {
                    $d->masterid = $master->id;
                    $d->mastertitle = $master->title;
                }
            }
        }

        $result = (object) array(
            'count'  => count_records('collection', 'owner', $USER->get('id')),
            'data'   => $data,
            'offset' => $offset,
            'limit'  => $limit,
        );

        return $result;
    }

    /**
    * Gets the fields for the new/edit collection form
    * - populates the fields with collection data if it is an edit
    *
    * @param array collection
    * @return array $elements
    */
    public static function get_collectionform_elements($data=null) {
        $elements = array(
            'name' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('name', 'collection'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'collection'),
            ),
        );

        // populate the fields with the existing values if any
        if (!empty($data)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $data->$k;
            }
            $elements['id'] = array(
                'type' => 'hidden',
                'value' => $data->id,
            );
            $elements['owner'] = array(
                'type' => 'hidden',
                'value' => $data->owner,
            );
        }

        return $elements;
    }

    /**
     * Returns the current collection
     *  - called by lib/web.php for displaying sub page navigation
     *
     * @return object $collection
     */
    public static function current_collection() {
        static $collection;
        static $dying;

        if (defined('COLLECTION') AND !$dying) {
            $id = COLLECTION;
            $data = get_record_select('collection', 'id = ?', array($id), '*, ' . db_format_tsfield('ctime'));
            $collection = new Collection($id,(array)$data);
            if (!$collection) {
                $dying = 1;
                throw new CollectionNotFoundException("Collection with id $id not found");
            }
        }
        else {
            $collection = null;
        }

        return $collection;
    }

    /**
     * Returns a datastructure describing the tabs that appear on a collection sub page navigation
     *
     * @return array $menu
     */
    public function get_menu_tabs() {
        static $menu;

        $menu = array(
            'info' => array(
                'path' => 'myportfolio/collection/info',
                'url' => 'collection/about.php?id='.$this->get('id'),
                'title' => get_string('about', 'collection'),
                'weight' => 20
            ),
            'views' => array(
                'path' => 'myportfolio/collection/views',
                'url' => 'collection/views.php?id='.$this->get('id'),
                'title' => get_string('views', 'collection'),
                'weight' => 30
            ),
            'access' => array(
                'path' => 'myportfolio/collection/access',
                'url' => 'collection/access.php?id='.$this->get('id'),
                'title' => get_string('access', 'collection'),
                'weight' => 40
            ),
        );

        if (defined('MENUITEM')) {
            $key = substr(MENUITEM, strlen('myportfolio/collection/'));
            if ($key && isset($menu[$key])) {
                $menu[$key]['selected'] = true;
            }
        }

        return $menu;
    }

    /**
     * Returns if the view is master
     *
     * @return bool
     */
    public static function is_master($id) {
        if (record_exists('collection_view','master',1,'view',$id)) {
            return true;
        }
        return false;
    }
    /**
     * Returns the current master view
     *
     * @return array master
     */
    public function master() {
        global $USER;

        if ($master = get_records_sql_array("
                SELECT cv.*, v.title
                FROM {collection} c
                    LEFT JOIN {collection_view} cv on cv.collection = c.id
                    LEFT JOIN {view} v ON cv.view = v.id
                WHERE c.id = ? AND c.owner = ? AND cv.master = 1
            ", array($this->get('id'), $USER->get('id')))) {
            return $master[0];
        }
        return null;
    }

    /**
     * Returns array of views in the current collection
     *
     * @param bool master (optional) whether or not to include master view in results
     * @return array views 
     */
    public function views($master=true) {
        global $USER;

        $mastersql = $master ? '' : "AND cv.master = 0";
        $sql = "SELECT cv.*, v.title
                FROM {collection_view} cv
                    LEFT JOIN {collection} c ON cv.collection = c.id
                    JOIN {view} v ON cv.view = v.id
                WHERE c.id = ? AND c.owner = ? " . $mastersql . "
                ORDER BY cv.displayorder, v.title, v.ctime ASC";

        $result = get_records_sql_array($sql, array($this->get('id'), $USER->get('id')));

        if (!empty($result)) {
            $views = array(
                'views'     => $result,
                'count'     => count($result),
                'max'       => get_field('collection_view', 'MAX(displayorder)', 'collection', $this->get('id')),
                'min'       => get_field('collection_view', 'MIN(displayorder)', 'collection', $this->get('id')),
            );

            return $views;
        }

        return array();
    }

    /**
     * Get the available views the current user can choose from
     * - currently dashboard, group and profile views are ignored to solve access issues
     * - each view can only belong to one collection
     *
     * @return array $views
     */
    public static function available_views() {
        global $USER;

        $userid = $USER->get('id');
        ($views = get_records_sql_array("SELECT v.id, v.title
                  FROM {view} v
                WHERE v.owner = ? AND v.type NOT IN ('dashboard','grouphomepage','profile')
                AND v.id NOT IN (
                    SELECT cv.view
                      FROM {collection_view} cv
                )
                GROUP BY v.id, v.title", array($userid)))
                || ($views = array());

        return $views;
    }

    /**
     * Submits the selected views to the collection
     *
     * @param array values selected views
     * @return integer count so we know what SESSION message to display
     */
    public function add_views($values) {

        $count = 0; // how many views we are adding
        db_begin();

        // each view was marked with a key of view_<id> in order to identify the correct items
        // from the form values
        foreach ($values as $key => $value) {
            if (substr($key,0,5) === 'view_' AND $value == true) {
                $cv = array();
                $cv['view'] = substr($key,5);
                $cv['collection'] = $this->get('id');

                // set displayorder value
                $max = get_field('collection_view', 'MAX(displayorder)', 'collection', $this->get('id'));
                $cv['displayorder'] = is_numeric($max) ? $max + 1 : 0;

                insert_record('collection_view', (object)$cv);
                $count++;
            }
        }

        db_commit();

        return $count;
    }

    /**
     * Removes the selected views from the collection
     *
     * @param integer $view the view to remove
     */
    public function remove_view($view) {
        db_begin();
        delete_records('collection_view','view',$view,'collection',$this->get('id'));
        db_commit();
    }

    /**
     * Set master view
     *
     * @param integer $newmaster the view to clone access from
     * @return array $validaccess
     */
    public function set_master($newmaster) {
        require_once('view.php');

        // no master selected; set no override
        if (!$newmaster) {
            db_begin();
            // clear previous master
            update_record(
                'collection_view',
                (object) array(
                    'collection'       => $this->get('id'),
                    'master'            => 0,
                ),
                'collection'
            );
            db_commit();

            return true;
        }

        // master view selected
        if ($master = new View($newmaster)) {

            $access = $master->get_access();
            $validaccess = $this->validate_access_types($access); // for reporting purposes

            db_begin();
            // clear previous master
            update_record(
                'collection_view',
                (object) array(
                    'collection'       => $this->get('id'),
                    'master'            => 0,
                ),
                'collection'
            );

            // set new master
            update_record(
                'collection_view',
                (object) array(
                    'view'             => $newmaster,
                    'collection'       => $this->get('id'),
                    'master'            => 1,
                ),
                array('collection','view')
            );
            db_commit();

            // update the access for all other views
            $this->update_access($validaccess);

            return $validaccess;
        }

        // something went wrong and master could not be set
        return false;
    }

    /**
     * Update access in collection
     *
     */
    private function update_access($validaccess) {
        require_once('view.php');
        db_begin();
        // update all other views access records
        if ($views = $this->views(false)) {
            foreach ($views['views'] as $view) {
                if ($v = new View($view->view)) {
                    delete_records('view_access','view',$view->view); // clear all current access
                    if (!empty($validaccess['valid'])) {
                        $v->set_access($validaccess['valid']);
                    }
                }
            }
        }
        db_commit();
    }

    /**
     * Checks the access types for cloning master access 
     *
     * @param array access chosen masters access 
     * @return array validaccess (valid: array, secreturl: bool)
     */
    private function validate_access_types($access) {
        $valid = array();
        $secretURL = false;
        foreach ($access as $a) {
            if (empty($a['token'])) { // as long as it isn't a secret URL access type
                $valid[] = $a;
            }
            else {
                $secretURL = true; // keep a record if any secret URLs to be ignored
            }
        }

        return array(
            'valid' => $valid,
            'secreturl' => $secretURL,
        );
    }

    /**
     * Sets the displayorder for a view
     *
     * @param integer view
     * @param string direction
     *
     */
    public function set_viewdisplayorder($id, $direction) {

        $ids = get_column_sql('
            SELECT view FROM {collection_view}
            WHERE collection = ?
            ORDER BY displayorder', array($this->get('id')));

        foreach ($ids as $k => $v) {
            if ($v == $id) {
                $oldorder = $k;
                break;
            }
        }

        if ($direction == 'up' && $oldorder > 0) {
            $neworder = array_merge(array_slice($ids, 0, $oldorder - 1),
                                    array($id, $ids[$oldorder-1]),
                                    array_slice($ids, $oldorder+1));
        }
        else if ($direction == 'down' && ($oldorder + 1 < count($ids))) {
            $neworder = array_merge(array_slice($ids, 0, $oldorder),
                                    array($ids[$oldorder+1], $id),
                                    array_slice($ids, $oldorder+2));
        }

        if (isset($neworder)) {
            foreach ($neworder as $k => $v) {
                set_field('collection_view', 'displayorder', $k, 'view', $v, 'collection',$this->get('id'));
            }
            $this->set('mtime', time());
            $this->commit();
        }
    }

    /**
     * after editing the collection, redirect back to the appropriate place
     */
    public function post_edit_redirect($new=false) {
        if ($new) {
            $redirecturl = '/collection/views.php?id=' . $this->get('id') . '&new=1';
        }
        else {
            $redirecturl = '/collection/about.php?id='.$this->get('id');
        }
        redirect($redirecturl);
    }

    /**
     * after editing the collection access redirect back to the appropriate place
     */
    public function post_access_redirect($success, $new=false) {
        global $SESSION;

        $newurl = $new ? '&new=1' : '';
        $master = $this->master();

        // access and master not set
        if (!$success) {
            $SESSION->add_error_msg(get_string('masternotset','collection'));
            $SESSION->add_error_msg(get_string('accessnotset','collection'));
            redirect('/collection/access.php?id='.$this->get('id').$newurl);
        }
        else if (empty($success['valid'])) {
            if ($success['secreturl']) {
                $SESSION->add_error_msg(get_string('invalidaccess', 'collection'));
                redirect('/collection/access.php?id='.$this->get('id').$newurl);
            }
            else if (!$master) {
                $SESSION->add_ok_msg(get_string('nooverridesaved', 'collection'));
            }
            else {
                $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
            }
        }
        else if (!empty($success['valid'])) {
            if (!$success['secreturl']) {
                $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
            }
            else {
                $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
            }
        }

        if (!$new) {
            redirect('/collection/access.php?id=' . $this->get('id'));
        }
        else {
            redirect('/collection/about.php?id=' . $this->get('id'));
        }
    }

}
