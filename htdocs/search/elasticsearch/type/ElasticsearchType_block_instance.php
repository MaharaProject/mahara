<?php
class ElasticsearchType_block_instance extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                    'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
            ),
            'secfacetterm' => array (
                    'type' => 'keyword',
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'title' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            'description' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            // the owner can be owner (user), group, or institution
            'owner' => array (
                    'type' => 'long',
            ),
            'group' => array (
                    'type' => 'long',
            ),
            'institution' => array (
                    'type' => 'keyword',
            ),
            'access' => array (
                    'type' => 'object',
                    // public - logged - friends: if block_instance is visible to public or logged-in users
                    // if public or logged, the other properties are ignored
                    'properties' => array (
                            'general' => array (
                                    'type' => 'keyword',
                            ),
                            // array of institutions that have access to the artefact
                            'institutions' => array (
                                    'type' => 'keyword',
                                    'copy_to' => 'institution',
                            ),
                            // array of groups that have access to the artefact - empty (all), member, admin, tutor
                            'groups' => array (
                                'type' => 'object',
                                'properties' => array (
                                    'all' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    ),
                                    'admin' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    ),
                                    'member' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    ),
                                    'tutor' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    )
                                )
                            ),
                            'group' => array (
                                    'type' => 'integer'
                            ),
                            'usrs' => array (
                                    'type' => 'integer',
                                    'copy_to' => 'usr',
                            ),
                            'usr' => array (
                                    'type' => 'integer'
                            )
                    )
            )
            ,
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
            ),
            // sort is the field that will be used to sort the results alphabetically
            'sort' => array (
                    'type' => 'keyword',
            )
    );

    public static $mainfacetterm = 'Text';
    public static $secfacetterm = 'Document';
    public function __construct($data) {
        $this->conditions = array ();

        $this->mapping = array (
                'mainfacetterm' => NULL,
                'secfacetterm' => NULL,
                'id' => NULL,
                'title' => NULL,
                'description' => NULL,
                'owner' => NULL,
                'group' => NULL,
                'institution' => NULL,
                'access' => NULL,
                'ctime' => NULL,
                'sort' => NULL
        );

        parent::__construct ( $data );
    }
    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record) {
            return false;
        }
        // Add the ctime by getting view ctime
        $data = self::getRecordDataById ( $type, $id );
        if (! $data) {
            return false;
        }
        // As block_instances do not have certain fields we need to get their
        // info either from the view they are on or from their configdata
        $record->ctime = parent::checkctime ( $data->ctime );
        $record->description = $data->description;
        $record->owner = $data->owner;
        $record->group = $data->group;
        $record->institution = $data->institution;

        // Access: get all the views where the block_instance is included
        $access = self::view_access_records ( $id );
        $accessObj = self::access_process ( $access );
        $record->access = $accessObj;
        $record->secfacetterm = self::$secfacetterm;
        // AS the field "sort" is not analyzed, we need to clean it (remove html tags & lowercase)
        $record->sort = strtolower ( strip_tags ( $record->title ) );

        return $record;
    }
    public static function getRecordDataById($type, $id) {
        global $USER;

        $sql = 'SELECT bi.id, bi.view AS view_id, bi.title, bi.configdata, v.owner, v.institution, v.group, v.ctime
        FROM {block_instance} bi
        JOIN {view} v ON v.id = bi.view
        WHERE bi.id = ?';

        $record = get_record_sql ( $sql, array (
                $id
        ) );
        if (! $record) {
            return false;
        }

        require_once( get_config ( 'docroot' ) . 'blocktype/lib.php' );
        $bi = new BlockInstance ( $id );
        $configdata = $bi->get ( 'configdata' );
        // We can only deal with blocktypes that have a 'text' configdata at this point
        if (! is_array ( $configdata ) || ! array_key_exists ( 'text', $configdata )) {
            return false;
        }
        $record->title = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->title ) );
        $record->description = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $configdata ['text'] ) );

        // If user is owner
        if ($USER->get ( 'id' ) == $record->owner) {
            $record->link = 'view/view.php?id=' . $record->view_id;
        }

        // Get the view info the block is on
        $sql = 'SELECT v.id AS id, v.title AS title
        FROM {view} v
        WHERE v.id = ?';

        $views = get_records_sql_array ( $sql, array (
                $record->view_id
        ) );
        if ($views) {
            $record_views = array ();
            foreach ( $views as $view ) {
                if (isset ( $view->id )) {
                    $record_views [$view->id] = $view->title;
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
        $records = get_records_sql_array ( '
                SELECT va.view AS view_id, va.accesstype, va.group, va.role, va.usr, va.institution
                FROM {view_access} va
                JOIN {block_instance} bi ON bi.view = va.view
                WHERE bi.id = ?
                AND (va.startdate IS NULL OR va.startdate < current_timestamp)
                AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
                ', array (
                $blockid
        ) );

        if (is_isolated() && get_field_sql("SELECT v.type FROM {view} v
                                            JOIN {block_instance} b ON b.view = v.id
                                            WHERE b.id = ?", array($blockid)) == 'profile') {
            if ($records) {
                foreach ($records as $k => $access) {
                    if ($access->accesstype == 'loggedin') {
                        unset($records[$k]);
                    }
                }
                $records = array_values($records);
            }
            $viewid = get_field('block_instance', 'view', 'id', $blockid);
            if (!get_records_sql_array("SELECT v.owner FROM {view} v
                                        JOIN {block_instance} b ON b.view = v.id
                                        JOIN {usr_institution} ui ON ui.usr = v.owner
                                        WHERE b.id = ?", array($blockid))) {
                // Member of no institution so need to add the 'mahara' institution option
                $noinst = new StdClass();
                $noinst->view_id = $viewid;
                $noinst->accesstype = null;
                $noinst->group = null;
                $noinst->role = null;
                $noinst->usr = null;
                $noinst->institution = 'mahara';
                $records[] = $noinst;
            }
            // Need to allow site admins to be able to see profile pages of all users
            foreach (get_column('usr', 'id', 'admin', 1) as $adminid) {
                $admins = new StdClass();
                $admins->view_id = $viewid;
                $admins->accesstype = null;
                $admins->group = null;
                $admins->role = null;
                $admins->usr = $adminid;
                $admins->institution = null;
                $records[] = $admins;
            }
        }

        return $records;
    }
}
