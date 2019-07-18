<?php
class ElasticsearchType_view extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
            ),
            'secfacetterm' => array ( // set to Collection - used in 2nd facet
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
            'tags' => array (
                    'type' => 'keyword',
                    'copy_to' => [ 'tag', 'catch_all' ]
            ),
            'tag' => array (
                    'type' => 'keyword'
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
                    // public - loggedin - friends: if artefact is visible to public or logged-in users
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
                            'institution' => array (
                                    'type' => 'keyword',
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
                            // array of user ids that have access to the artefact
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

    public static $mainfacetterm = 'Portfolio';
    public static $secfacetterm = 'Page';
    public function __construct($data) {
        $this->conditions = array ();

        $this->mapping = array (
                'mainfacetterm' => NULL,
                'secfacetterm' => NULL,
                'id' => NULL,
                'title' => NULL,
                'description' => NULL,
                'tags' => NULL,
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

        $tags = get_column ( 'tag', 'tag', 'resourcetype', 'view', 'resourceid', $id );
        if ($tags != false) {
            foreach ( $tags as $tag ) {
                $record->tags [] = $tag;
            }
        }
        else {
            $record->tags = null;
        }
        // Access: get view_access info
        $access = self::view_access_records ( $id );
        $accessObj = self::access_process ( $access );
        $record->access = $accessObj;
        $record->sort = strtolower ( strip_tags ( $record->title ) );
        $record->secfacetterm = self::$secfacetterm;
        return $record;
    }
    public static function getRecordDataById($type, $id) {
        $record = parent::getRecordDataById ( $type, $id );
        if (! $record) {
            return false;
        }

        // Created by
        if (intval ( $record->owner ) > 0) {
            $record->createdby = get_record ( 'usr', 'id', $record->owner );
            $record->createdbyname = display_name ( $record->createdby );
        }
        // Tags
        $tags = get_column ( 'tag', 'tag', 'resourcetype', 'view', 'resourceid', $id );
        if ($tags != false) {
            foreach ( $tags as $tag ) {
                $record->tags [] = $tag;
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
        $records = get_records_sql_array ( '
                SELECT va.view AS view_id, va.accesstype, va.group, va.role, va.usr, va.institution
                FROM {view_access} va
                WHERE va.view = ?
                    AND (startdate IS NULL OR startdate < current_timestamp)
                    AND (stopdate IS NULL OR stopdate > current_timestamp)', array (
                $viewid
        ) );

        if (is_isolated() && get_field('view', 'type', 'id', $viewid) == 'profile') {
            if ($records) {
                foreach ($records as $k => $access) {
                    if ($access->accesstype == 'loggedin') {
                        unset($records[$k]);
                    }
                }
                $records = array_values($records);
            }

            if (!get_records_sql_array("SELECT v.owner FROM {view} v
                                        JOIN {usr_institution} ui ON ui.usr = v.owner
                                        WHERE v.id = ?", array($viewid))) {
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
