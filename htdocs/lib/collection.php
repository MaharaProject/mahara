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

/**
 * returns a list of the given user's collections
 *
 * @param limit how many collections to display per page
 * @param offset current page to display
 * @return array (count: integer, data: array)
 */
function collection_get_user_collections($offset=0, $limit=10) {
    global $USER;

    ($results = get_records_sql_array("
        SELECT c.*
            FROM {collection} c
        WHERE c.owner = ?
        ORDER BY c.name, c.ctime ASC
        LIMIT ? OFFSET ?", array($USER->get('id'), $limit, $offset)))
        || ($results = array());

    $result = array(
        'count'  => count_records('collection', 'owner', $USER->get('id')),
        'data'   => $results,
        'offset' => $offset,
        'limit'  => $limit,
    );

    return $result;
}

/**
 * Builds the My Collections list table
 *
 * @param array collections (reference)
 */
function collection_build_list_html(&$collections) {
    $smarty = smarty_core();
    $smarty->assign_by_ref('collections', $collections);
    $collections['tablerows'] = $smarty->fetch('collection/collectionslist.tpl');
    $pagination = build_pagination(array(
        'id' => 'collectionslist_pagination',
        'class' => 'center',
        'url' => get_config('wwwroot') . 'collection/index.php',
        'jsonscript' => 'collection/collections.json.php',
        'datatable' => 'collectionslist',
        'count' => $collections['count'],
        'limit' => $collections['limit'],
        'offset' => $collections['offset'],
        'firsttext' => '',
        'previoustext' => '',
        'nexttext' => '',
        'lasttext' => '',
        'numbersincludefirstlast' => false,
        'resultcounttextsingular' => get_string('collection', 'collection'),
        'resultcounttextplural' => get_string('collections', 'collection'),
    ));
    $collections['pagination'] = $pagination['html'];
    $collections['pagination_js'] = $pagination['javascript'];
}

/**
 * Builds the new/edit collection pieform
 *
 * @param array collection
 * @return Pieform $collectionform
 */
function collection_get_form($collection=null) {
    require_once(get_config('libroot') . 'pieforms/pieform.php');
    $elements = get_collectionform_elements($collection);
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('savecollection','collection'), get_string('cancel')),
        'goto' => get_config('wwwroot') . 'collection/',
    );
    $collectionform = array(
        'name' => empty($collection->id) ? 'collection_addcollection' : 'collection_editcollection',
        'plugintype' => 'artefact',
        'pluginname' => 'collection',
        'successcallback' => 'collection_submit',
        'elements' => $elements,
    );

    return pieform($collectionform);
}

/**
* Gets the fields for the new/edit collection form
* - populates the fields with collection data if it is an edit
*
* @param array collection
* @return array $elements
*/
function get_collectionform_elements($collection=null) {
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
    if (!empty($collection)) {
        foreach ($elements as $k => $element) {
            $elements[$k]['defaultvalue'] = $collection->$k;
        }
        $elements['id'] = array(
            'type' => 'hidden',
            'value' => $collection->id,
        );
        $elements['owner'] = array(
            'type' => 'hidden',
            'value' => $collection->owner,
        );
    }

    return $elements;
}

/**
* Submit the form for new/edit collection 
*
* @param Pieform $form
* @param array $values (values to submit)
*/
function collection_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    // if id is not empty we are editing a collection
    if (!empty($values['id'])) {
        db_begin();

        $now = db_format_timestamp(time());

        update_record(
            'collection',
            (object) array(
                'id'             => $values['id'],
                'name'           => $values['name'],
                'description'    => $values['description'],
                'mtime'          => $now,
            ),
            'id'
        );

        db_commit();

        $SESSION->add_ok_msg(get_string('collectionsaved', 'collection'));

        redirect('/collection/about.php?id=' . $values['id']);
    } 
    else {
        $collection = array();
        $collection['owner'] = $USER->get('id');
        $collection['ctime'] = db_format_timestamp(time());
        $collection['mtime'] = db_format_timestamp(time());
        $collection['name'] = $values['name'];
        $collection['description'] = $values['description'];

        db_begin();
        insert_record('collection', (object)$collection,'id', true);
        db_commit();
    
        $SESSION->add_ok_msg(get_string('collectionsaved', 'collection'));

        redirect('/collection/');
    }
}

/**
 * Deletes a collection
 *
 * All collection deleting should be done through this function, even though it is
 * simple. What is required to perform collection deletion may change over time.
 *
 * @param int $collection (collection to delete)
 *
 */
function collection_delete($collection) {
    global $SESSION;

    // first delete all collection_view records for this collection
    // then delete the actual collection itself
    db_begin();
    delete_records('collection_view','collection',$collection);
    delete_records('collection','id',$collection);
    db_commit();

    $SESSION->add_ok_msg(get_string('collectiondeleted', 'collection'));

    redirect('/collection/');
}

/**
 * Deletes a view from a collection
 *
 * All collectoin deleting should be done through this function, even though it is
 * simple. What is required to perform collection deletion may change over time.
 *
 * @param int $view (view to remove)
 * @param int $collection (collection to remove from)
 *
 */
function collection_view_delete($view, $collection) {
    global $SESSION;

    db_begin();
    delete_records('collection_view','collection',$collection,'view',$view);
    db_commit();

    $SESSION->add_ok_msg(get_string('collectiondeleted', 'collection'));

    redirect('/collection/views.php?id='.$collection);
}

/**
 * Returns a datastructure describing the tabs that appear on a collection page
 *
 * @return array $menu
 */
function collection_get_menu_tabs() {
    static $menu;

    $collection = collection_current_collection();
    if (!$collection) {
        return null;
    }
    $menu = array(
        'info' => array(
            'path' => 'myportfolio/collection/info',
            'url' => 'collection/about.php?id='.$collection->id,
            'title' => get_string('about', 'collection'),
            'weight' => 20
        ),
        'views' => array(
            'path' => 'myportfolio/collection/views',
            'url' => 'collection/views.php?id='.$collection->id,
            'title' => get_string('views', 'collection'),
            'weight' => 30
        ),
        'access' => array(
            'path' => 'myportfolio/collection/access',
            'url' => 'collection/access.php?id='.$collection->id,
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
 * Returns the current collection
 *
 * @return array $collection
 */
function collection_current_collection() {
    static $collection;
    static $dying;

    if (defined('COLLECTION') && !$dying) {
        $collection = get_record_select('collection', 'id = ?', array(COLLECTION), '*, ' . db_format_tsfield('ctime'));
        if (!$collection) {
            $dying = 1;
            throw new CollectionNotFoundException(get_string('collectionnotfound', 'collection', COLLECTION));
        }
    }
    else {
        $collection = null;
    }

    return $collection;
}

/**
 * Returns the current collections view list
 *
 * @return int $collection
 * @return array $results
 */
function collection_get_views($collection=null) {
    global $USER;

    ($results = get_records_sql_array("
        SELECT cv.*, v.title
            FROM {collection} c
            LEFT JOIN {collection_view} cv ON cv.collection = c.id
            JOIN {view} v ON cv.view = v.id
        WHERE c.id = ? AND c.owner = ?
        ORDER BY v.title, v.ctime ASC", array($collection, $USER->get('id'))))
        || ($results = array());

        return $results;
}

/**
 * Builds the view list table
 *
 * @param array $currentviews (reference)
 */
function collection_build_view_list_html(&$currentviews) {
    if (!empty($currentviews)) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('currentviews', $currentviews);
        $currentviews['tablerows'] = $smarty->fetch('collection/viewlist.tpl');
    }
}

/**
 * Submits the addviews form
 *
 * @param Pieform $form
 * @param array $values
 */
function addviews_submit(Pieform $form, $values) {
    global $SESSION;

    $count = 0;
    db_begin();
    // first we need to grab the id of the selected view/s to add from the checkbox option key
    // then submit all selected views
    foreach ($values as $key => $value) {
        if (substr($key,0,5) === 'view_' AND $value == true) {
            $cv = array();
            $cv['view'] = substr($key,5);
            $cv['collection'] = $values['id'];
            insert_record('collection_view', (object)$cv);
            $count++;
        }
    }
    db_commit();

    if ($count > 1) {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection', 'collection'));
    }
    else {
        $SESSION->add_ok_msg(get_string('viewaddedtocollection', 'collection'));
    }

    redirect('/collection/views.php?id=' . $values['id']);
}

/**
 * Get the possible views the current user can choose from
 * - currently dashboard, group and profile views are ignored to solve access issues
 *
 * @return array $views
 */
function collection_get_user_views() {
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
 * Set access for all views in the collection based on the selected master view
 *
 * @param $collection
 * @param $master
 */
function collection_set_access($collection, $master) {
    global $SESSION;
    require_once('view.php');

    // return if setting no override on views
    if (!$master) {
        $SESSION->add_ok_msg(get_string('nooverridesavedcorrectly', 'collection'));
        redirect('/collection/access.php?id=' . $collection);
    }

    if ($collection AND $masterview = new View($master)) {

        db_begin();
        // clear previous master for collection
        update_record(
            'collection_view',
            (object) array(
                'collection'       => $collection,
                'master'            => 0,
            ),
            'collection'
        );

        // update new master for collection
        update_record(
            'collection_view',
            (object) array(
                'view'             => $master,
                'collection'       => $collection,
                'master'            => 1,
            ),
            array('collection','view')
        );

        db_commit();

        $access = $masterview->get_access();
        $views = collection_get_views($collection);

        // if the selected master only has 1 access type and it is secret URL: ignore
        if (count($access) == 1 AND !empty($access[0]['token'])) {
            $SESSION->add_info_msg(get_string('incorrectaccesstype', 'collection'));
            redirect('/collection/access.php?id=' . $collection);
        }

        // sort out what the new access first
        $newaccess = array();
        foreach ($access as $a) {
            if (empty($a['token'])) {
                $newaccess[] = $a;
            }
            else {
                // alert user about irnored secret url accesses
                $SESSION->add_info_msg(get_string('incorrectaccesstype', 'collection'));
            }
        }

        // update each other views access in the collection
        foreach ($views as $view) {
            if ($view->view != $master AND $v = new View($view->view)) {
                delete_records('view_access','view',$view->view); // try deleting the current access first ...
                $v->set_access($newaccess);
            }
        }

        $SESSION->add_ok_msg(get_string('accesssaved', 'collection'));
        redirect('/collection/access.php?id=' . $collection);
    }
}

function collection_get_master($collection) {

    if ($master = get_record_sql("
        SELECT v.id, v.title
            FROM {view} v
            JOIN {collection_view} cv ON v.id = cv.view
        WHERE cv.collection = ? AND master = 1",
        array($collection)))
    {
        return $master;
    }
}

function collection_get_name($collection) {
    if ($name = get_column('collection','name','id',$collection)) {
        return $name[0];
    }
    return '';
}

?>
