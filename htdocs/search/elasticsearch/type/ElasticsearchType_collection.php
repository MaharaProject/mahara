<?php

class ElasticsearchType_collection extends ElasticsearchType
{

    public static $mappingconf =    array(
            'mainfacetterm' =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'secfacetterm' =>  array(  // set to Page - used in 2nd facet
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'id'        =>  array(
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'name'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'description'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            // the owner can be owner (user), group, or institution
            'owner'         =>  array(
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'group'         =>  array(
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'institution'   =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'access'    =>  array(
                    'type' => 'object',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE,
                    // public - loggedin - friends: if artefact is visible to public or logged-in users
                    // if public or logged, the other properties are ignored
                    'general' =>  array(
                            'type' => 'string',
                            'include_in_all' => FALSE
                    ),
                    // array of institutions that have access to the artefact
                    'institutions' =>  array(
                            'type' => 'string',
                            'index_name' => 'institution',
                            'index' => 'not_analyzed',
                            'include_in_all' => FALSE
                    ),
                    // array of groups that have access to the artefact - empty (all), member, admin, tutor
                    'groups' =>  array(
                            'type' => 'object',
                            'index' => 'not_analyzed',
                            'include_in_all' => FALSE,
                            // list of groups for which both members and admins have access to the artefact
                            'all' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            // list of groups for which only admins have access to the artefact
                            'admin' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            // list of groups for which only members have access to the artefact
                            'member' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            // list of groups for which only tutors have access to the artefact
                            'tutor' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                    ),
                    // array of user ids that have access to the artefact
                    'usrs'     =>  array(
                            'type' => 'int',
                            'index' => 'not_analyzed',
                            'index_name' => 'usr',
                            'include_in_all' => FALSE
                    ),
            ),
            'ctime'  =>  array(
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
                    'include_in_all' => FALSE
            ),
            // sort is the field that will be used to sort the results alphabetically
            'sort'     =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
    );

    public static $mainfacetterm = 'Portfolio';
    public static $secfacetterm = 'Collection';

    public function __construct($data){

        $this->conditions =     array();

        $this->mapping =        array(
                'mainfacetterm' => NULL,
                'secfacetterm'  => NULL,
                'id'            => NULL,
                'name'          => NULL,
                'description'   => NULL,
                'owner'         => NULL,
                'group'         => NULL,
                'institution'   => NULL,
                'access'        => NULL,
                'ctime'         => NULL,
                'sort'          => NULL,
        );

        parent::__construct($data);

    }

    public static function getRecordById($type, $id){
        $record = parent::getRecordById($type, $id);
        if (!$record) {
            return false;
        }

        // Access: get view_access info
        $access = self::collection_access_records($id);
        $accessObj = self::access_process($access);
        $record->access = $accessObj;
        $record->sort = strtolower(strip_tags($record->name));
        $record->secfacetterm = self::$secfacetterm;
        return $record;
    }

    public static function getRecordDataById($type, $id){

        $sql = 'SELECT c.id, c.name, c.ctime, c.description, cv.view AS viewid, c.owner
        FROM {collection} c
        LEFT OUTER JOIN {collection_view} cv ON cv.collection = c.id
        WHERE id = ? ORDER BY cv.displayorder asc LIMIT 1;';

        $record = get_record_sql($sql, array($id));
        if (!$record) {
            return false;
        }

        $record->name = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->name));
        $record->description = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->description));

        //  Created by
        if (intval($record->owner) > 0) {
            $record->createdby = get_record('usr', 'id', $record->owner);
            $record->createdbyname = display_name($record->createdby);
        }

        // Get all views included in that collection
        $sql = 'SELECT v.id, v.title
        FROM {view} v
        LEFT OUTER JOIN {collection_view} cv ON cv.view = v.id
        WHERE cv.collection = ?';

        $views = recordset_to_array(get_recordset_sql($sql, array($id)));
        if ($views) {
            $record_views = array();
            foreach($views AS $view){
                if (isset($view->id)) {
                    $record_views[$view->id] = $view->title;
                }
            }
            $record->views = $record_views;
        }

        return $record;

    }


    /**
     * Get all view access records relevant at the data of the indexing
     */
    public static function collection_access_records($id) {

        $records = get_records_sql_array('
                SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
                FROM {view_access} vac
                INNER JOIN {collection_view} vcol ON vac.view = vcol.view
                WHERE   vcol.collection = ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)',
                array($id)
        );

        return $records;
    }

}
