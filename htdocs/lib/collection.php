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
    private $navigation;
    private $views;

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
        if ($field == 'views') {
            return $this->views();
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
        $viewids = get_column('collection_view', 'view', 'collection', $this->id);
        db_begin();
        delete_records('collection_view','collection',$this->id);
        delete_records('collection','id',$this->id);

        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        if ($viewids) {
            delete_records_select('view_access', 'view IN (' . join(',', $viewids) . ') AND token IS NOT NULL');
        }

        db_commit();
    }

    /**
     * This method updates the contents of the collection table only.
     */
    public function commit() {

        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime')) && !empty($v)) {
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
            'navigation' => array(
                'type'  => 'checkbox',
                'title' => get_string('viewnavigation','collection'),
                'description' => get_string('viewnavigationdesc','collection'),
                'defaultvalue' => 1,
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
     * Returns array of views in the current collection
     *
     * @return array views 
     */
    public function views() {

        if (!isset($this->views)) {

            $sql = "SELECT cv.*, v.title
                FROM {collection_view} cv JOIN {view} v ON cv.view = v.id
                WHERE cv.collection = ?
                ORDER BY cv.displayorder, v.title, v.ctime ASC";

            $result = get_records_sql_array($sql, array($this->get('id')));

            if (!empty($result)) {
                $this->views = array(
                    'views'     => $result,
                    'count'     => count($result),
                    'max'       => get_field('collection_view', 'MAX(displayorder)', 'collection', $this->get('id')),
                    'min'       => get_field('collection_view', 'MIN(displayorder)', 'collection', $this->get('id')),
                );
            }
            else {
                $this->views = array();
            }

        }

        return $this->views;
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
                LEFT JOIN {collection_view} cv ON cv.view = v.id
                WHERE v.owner = ?
                AND cv.view IS NULL
                AND v.type NOT IN ('dashboard','grouphomepage','profile')
                GROUP BY v.id, v.title
                ORDER BY v.title ASC
                ", array($userid)))
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
        require_once(get_config('libroot') . 'view.php');

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

        $viewids = get_column('collection_view', 'view', 'collection', $this->id);

        // Set the most permissive access records on all views
        View::combine_access($viewids, true);

        // Copy the whole view config from the first view to all the others
        if (count($viewids)) {
            $firstview = new View($viewids[0]);
            $viewconfig = array(
                'startdate'       => $firstview->get('startdate'),
                'stopdate'        => $firstview->get('stopdate'),
                'template'        => $firstview->get('template'),
                'allowcomments'   => $firstview->get('allowcomments'),
                'approvecomments' => (int) ($firstview->get('allowcomments') && $firstview->get('approvecomments')),
                'accesslist'      => $firstview->get_access(),
            );
            View::update_view_access($viewconfig, $viewids);
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

        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        delete_records_select('view_access', 'view = ? AND token IS NOT NULL', array($view));

        db_commit();
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
            $redirecturl = '/collection/index.php';
        }
        redirect($redirecturl);
    }

    public static function search_by_view_id($viewid) {
        $record = get_record_sql('
            SELECT c.*
            FROM {collection} c JOIN {collection_view} cv ON c.id = cv.collection
            WHERE cv.view = ?',
            array($viewid)
        );
        if ($record) {
            return new Collection(0, $record);
        }
        return false;
    }

}
