<?php

class ElasticsearchType_view extends ElasticsearchType
{

    public static $mappingconf =    array(
            'mainfacetterm' =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'secfacetterm' =>  array(  // set to Collection - used in 2nd facet
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'id'        =>  array(
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'title'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'description'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'tags'      =>  array(
                    'type' => 'string',
                    'index_name' => 'tag',
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
    public static $secfacetterm = 'Page';

    public function __construct($data){

        $this->conditions =     array();

        $this->mapping =        array(
                'mainfacetterm' => NULL,
                'secfacetterm'  => NULL,
                'id'            => NULL,
                'title'         => NULL,
                'description'   => NULL,
                'tags'          => NULL,
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

        $tags = get_records_array('view_tag', 'view', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags[] = $tag->tag;
            }
        }
        else {
            $record->tags = null;
        }
        // Access: get view_access info
        $access = self::view_access_records($id);
        $accessObj = self::access_process($access);
        $record->access = $accessObj;
        $record->sort = strtolower(strip_tags($record->title));
        $record->secfacetterm = self::$secfacetterm;
        return $record;
    }

    public static function getRecordDataById($type, $id){
        $record = parent::getRecordDataById($type, $id);
        if (!$record) {
            return false;
        }

        //  Created by
        if (intval($record->owner) > 0) {
            $record->createdby = get_record('usr', 'id', $record->owner);
            $record->createdbyname = display_name($record->createdby);
        }
        //  Tags
        $tags = get_records_array('view_tag', 'view', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags[] = $tag->tag;
            }
        }
        else {
            $record->tags = null;
        }
        return $record;
    }


    /**
     * Get all view access records relevant at the data of the indexing
     */
    public static function view_access_records($viewid) {

        $records = get_records_sql_array('
                SELECT va.view AS view_id, va.accesstype, va.group, va.role, va.usr, va.institution
                FROM {view_access} va
                WHERE va.view = ?
                    AND (startdate IS NULL OR startdate < current_timestamp)
                    AND (stopdate IS NULL OR stopdate > current_timestamp)',
                array($viewid)
        );

        return $records;
    }

}
