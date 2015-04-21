<?php

class ElasticsearchType_block_instance extends ElasticsearchType
{

    public static $mappingconf =    array(
            'mainfacetterm' =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'secfacetterm' =>  array(
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
            'access'        =>  array(
                    'type' => 'object',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE,
                    // public - logged - friends: if block_instance is visible to public or logged-in users
                    // if public or logged, the other properties are ignored
                    'general' =>  array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'include_in_all' => FALSE
                    ),
                    // array of institutions that have access to the block_instance
                    'institutions' =>  array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'index_name' => 'institution',
                            'include_in_all' => FALSE
                    ),
                    // array of groups that have access to the block_instance - empty (all), member, admin
                    'groups' =>  array(
                            'type' => 'object',
                            'index' => 'not_analyzed',
                            'include_in_all' => FALSE,
                            // list of groups for which both members and admins have access to the block_instance
                            'all' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            // list of groups for which only admins have access to the block_instance
                            'admin' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            // list of groups for which only members have access to the block_instance
                            'member' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            // list of groups for which only tutors have access to the block_instance
                            'tutor' =>  array(
                                    'type' => 'int',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                    ),
                    // array of user ids that have access to the block_instance
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

    public static $mainfacetterm = 'Text';
    public static $secfacetterm = 'Document';

    public function __construct($data){

        $this->conditions =     array();

        $this->mapping =        array(
                'mainfacetterm' => NULL,
                'secfacetterm'  => NULL,
                'id'            => NULL,
                'title'         => NULL,
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

    public static function getRecordById($type, $id) {
        $record = parent::getRecordById($type, $id);
        if (!$record) {
            return false;
        }
        // Add the ctime by getting view ctime
        $data = self::getRecordDataById($type, $id);
        if (!$data) {
            return false;
        }
        // As block_instances do not have certain fields we need to get their
        // info either from the view they are on or from their configdata
        $record->ctime = parent::checkctime($data->ctime);
        $record->description = $data->description;
        $record->owner = $data->owner;
        $record->group = $data->group;
        $record->institution = $data->institution;

        // Access: get all the views where the block_instance is included
        $access = self::view_access_records($id);
        $accessObj = self::access_process($access);
        $record->access = $accessObj;
        $record->secfacetterm = self::$secfacetterm;
        // AS the field "sort" is not analyzed, we need to clean it (remove html tags & lowercase)
        $record->sort = strtolower(strip_tags($record->title));

        return $record;
    }

    public static function getRecordDataById($type, $id){

        global $USER;

        $sql = 'SELECT bi.id, bi.view AS view_id, bi.title, bi.configdata, v.owner, v.institution, v.group, v.ctime
        FROM {block_instance} bi
        JOIN {view} v ON v.id = bi.view
        WHERE bi.id = ?';

        $record = get_record_sql($sql, array($id));
        if (!$record) {
            return false;
        }

        require_once(get_config('docroot') . 'blocktype/lib.php');
        $bi = new BlockInstance($id);
        $configdata = $bi->get('configdata');
        // We can only deal with blocktypes that have a 'text' configdata at this point
        if (!is_array($configdata) || !array_key_exists('text', $configdata)) {
            return false;
        }
        $record->title = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->title));
        $record->description = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($configdata['text']));

        // If user is owner
        if ($USER->get('id') == $record->owner) {
            $record->link = 'view/view.php?id=' . $record->view_id;
        }

        // Get the view info the block is on
        $sql = 'SELECT v.id AS id, v.title AS title
        FROM {view} v
        WHERE v.id = ?';

        $views = get_records_sql_array($sql, array($record->view_id));
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
     * Get all access records of the views in which the block_instance is included
     */
    public static function view_access_records($blockid) {

        $records = get_records_sql_array('
                SELECT va.view AS view_id, va.accesstype, va.group, va.role, va.usr, va.institution
                FROM {view_access} va
                JOIN {block_instance} bi ON bi.view = va.view
                WHERE bi.id = ?
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                ',
                array($blockid)
        );

        return $records;
    }
}
