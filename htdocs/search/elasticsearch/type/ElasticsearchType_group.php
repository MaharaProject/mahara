<?php

class ElasticsearchType_group extends ElasticsearchType
{

    public static $mappingconf =    array(
            'mainfacetterm' =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => TRUE
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
            'grouptype'     =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'jointype'     =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            // access to group
            'access'        =>  array(
                'type' => 'object',
                'index' => 'not_analyzed',
                'include_in_all' => FALSE,
                'general' =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
                ),
                'groups' =>  array(
                    'member' =>  array(
                        'type' => 'int',
                        'index' => 'not_analyzed',
                        'include_in_all' => FALSE
                    ),
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

    public static $mainfacetterm = 'Group';

    public function __construct($data){

        $this->conditions =     array(
                'deleted' => 0,
                'hidden' => 0,
        );

        $this->mapping =        array(
                'mainfacetterm' => NULL,
                'id'            => NULL,
                'name'          => NULL,
                'description'   => NULL,
                'grouptype'     => NULL,
                'jointype'      => NULL,
                'access'        => NULL,
                'ctime'         => NULL,
                'sort'         => NULL,
        );

        parent::__construct($data);

    }

    public static function getRecordById($type, $id){
        $record = parent::getRecordById($type, $id);
        if (!$record || $record->deleted) {
            return false;
        }
        $record->access['general'] = ($record->public) ? 'public' : 'loggedin';
        $record->access['groups']['member'] = $record->id;
        $record->sort = strtolower(strip_tags($record->name));
        return $record;
    }

    public static function getRecordDataById($type, $id){
        $record = get_record($type, 'id', $id);
        if (!$record || $record->deleted) {
            return false;
        }
        $record->description = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->description));
        $record->groupadmins = group_get_admins(array($id));
        return $record;
    }
}
