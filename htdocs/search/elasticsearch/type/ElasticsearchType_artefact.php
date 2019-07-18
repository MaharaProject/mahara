<?php
class ElasticsearchType_artefact extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array ( // this is the 2nd level in the hierarchy artefacttype|artefactgroup|mainfacetterm
                    'type' => 'keyword',
            ),
            'secfacetterm' => array ( // this is the 1st level in the hierarchy artefacttype|artefactgroup|mainfacetterm
                    'type' => 'keyword',
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'artefacttype' => array (
                    'type' => 'keyword',
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
                    'copy_to' => ['tag', 'catch_all']
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
                    // public - logged - friends: if artefact is visible to public or logged-in users
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
                            // array of groups that have access to the artefact - empty (all), member, admin
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
            ),
            // artefact_license.name is used to store the license
            'license' => array (
                    'type' => 'keyword',
            )
    );

    public static $mainfacetterm = 'Text'; // can be Text or Media depending on artefacttype
    public static $subfacetterm = 'artefacttype';
    public function __construct($data) {
        $this->conditions = array ();

        $this->mapping = array (
                'mainfacetterm' => NULL,
                'secfacetterm' => NULL,
                'id' => NULL,
                'artefacttype' => NULL,
                'title' => NULL,
                'description' => NULL,
                'tags' => NULL,
                'owner' => NULL,
                'group' => NULL,
                'institution' => NULL,
                'access' => NULL,
                'ctime' => NULL,
                'sort' => NULL,
                'license' => NULL
        );

        parent::__construct ( $data );
    }

    /**
     * set if the record has to be indexed or removed from the index
     */
    public function setisDeleted() {
        parent::setisDeleted ();
        // artefacts: if artefacttype is not selected, mark as deleted
        $artefacttypes = explode ( ',', get_config_plugin ( 'search', 'elasticsearch', 'artefacttypes' ) );
        if (! in_array ( $this->mapping ['artefacttype'], $artefacttypes )) {
            $this->isDeleted = true;
        }
    }
    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record) {
            return false;
        }

        // Tags
        $tags = get_column ( 'tag', 'tag', 'resourcetype', 'artefact', 'resourceid', $id );
        if ( $tags != false ) {
            foreach ( $tags as $tag ) {
                $record->tags [] = $tag;
            }
        }
        else {
            $record->tags = null;
        }
        // Access: get all the views where the artefact is included
        $access = self::view_access_records ( $id );
        $accessObj = self::access_process ( $access );
        if (! $access) {
            // File access: get viewable group media not attached to a view
            $groupaccess = self::group_artefact_access_records ( $id );
            if ($groupaccess) {
                foreach ( $groupaccess as $access ) {
                    $accessObj ['groups'] [$access->role] [] = $access->can_view;
                }
            }
        }
        $record->access = $accessObj;

        // set 'mainfacetterm' & 'artefactgroup'
        if (! empty ( $map ) && isset ( $map [$record->artefacttype] )) {
            $terms = explode ( "|", $map [$record->artefacttype] );
            $record->mainfacetterm = $terms [2];
            $record->secfacetterm = $terms [1];
            if ($record->artefacttype == 'country') {
                // We need to index the actual name and not the iso code
                $record->title = get_string("country.{$record->title}");
            }

            require_once( get_config ( 'docroot' ) . 'artefact/resume/lib.php' );
            if (PluginArtefactResume::is_active ()) {
                // If the artefacttype is one of the résumé ones we need to get the description
                // from this artefact's related résumé table. There is a one -> many relationship between
                // the artefact and the items but seen as all resume items are added
                // to a page when choosing One résumé field, rather than selecting them individually,
                // we can just blob together all the info for this résumé artefact into $record->description.

                $resumetypes = ArtefactTypeResumeComposite::get_composite_artefact_types ();
                if (in_array ( $terms [0], $resumetypes )) {
                    try {
                        $query = "SELECT * FROM {artefact_resume_" . $terms [0] . "} WHERE artefact = ?";
                        $results = get_records_sql_assoc ( $query, array (
                                $record->id
                        ) );
                    }
                    catch ( SQLException $e ) {
                        // Table doesn't exist
                        $results = array ();
                    }
                    foreach ( $results as $result ) {
                        $items = get_object_vars ( $result );
                        foreach ( $items as $key => $item ) {
                            if (! in_array ( $key, array (
                                    'id',
                                    'artefact',
                                    'displayorder'
                            ) )) {
                                $record->description .= $item . ' ';
                            }
                        }
                    }
                }
            }
        }
        // AS the field "sort" is not analyzed, we need to clean it (remove html tags & lowercase)
        $record->sort = strtolower ( strip_tags ( $record->title ) );

        return $record;
    }
    public static function getRecordDataById($type, $id) {
        global $USER;

        $sql = 'SELECT  a.id, a.artefacttype, a.parent, a.owner, a.title, a.description, a.institution, a.group, a.author,
        p.artefacttype AS parent_artefacttype, p.title AS parent_title, p.description  AS parent_description, a.license,
        afi.width, afi.height, a.note
        FROM {artefact} a
        LEFT OUTER JOIN {artefact} p ON p.id = a.parent
        LEFT OUTER JOIN {artefact_file_image} afi ON afi.artefact = a.id
        WHERE a.id = ?';

        $record = get_record_sql ( $sql, array (
                $id
        ) );
        if (! $record) {
            return false;
        }

        $record->title = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->title ) );
        if ($record->artefacttype == 'country') {
            // We need to display the actual name and not the iso code
            $record->title = get_string("country.{$record->title}");
        }
        $record->description = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->description ) );
        // If user is owner
        if ($USER->get ( 'id' ) == $record->owner) {
            switch ($record->artefacttype) {
                case 'image' :
                case 'video' :
                case 'audio' :
                case 'file' :
                    $record->link = 'artefact/file';
                    if (isset ( $record->parent ) && intval ( $record->parent ) > 0) {
                        $record->link .= '/index.php?folder=' . $record->parent;
                    }
                    break;
                case 'blogpost' :
                    if (isset ( $record->parent ) && intval ( $record->parent ) > 0) {
                        $record->link = 'artefact/blog/view/index.php?id=' . $record->parent;
                    }
                    break;
                case 'blog' :
                    $record->link = 'artefact/blog/view/index.php';
                    if ($USER->get_account_preference ( 'multipleblogs' )) {
                        $record->link .= '?id=' . $record->id;
                    }
                    break;
                case 'coverletter' :
                case 'personalinformation' :
                    $record->link = 'artefact/resume/index.php';
                    break;
                case 'educationhistory' :
                case 'employmenthistory' :
                    $record->link = 'artefact/resume/employment.php';
                    break;
                case 'book' :
                case 'certification' :
                case 'membership' :
                    $record->link = 'artefact/resume/achievements.php';
                    break;
                case 'academicgoal' :
                case 'careergoal' :
                case 'personalgoal' :
                case 'personalinformation' :
                case 'academicskill' :
                case 'personalskill' :
                case 'workskill' :
                    $record->link = 'artefact/resume/goalsandskills.php';
                    break;
                case 'interest' :
                    $record->link = 'artefact/resume/interests.php';
                    break;
                case 'plan' :
                    $record->link = 'artefact/plans/plan.php?id=' . $record->id;
                    break;
                case 'task' :
                    if (isset ( $record->parent ) && intval ( $record->parent ) > 0) {
                        $record->link = 'artefact/plans/plan.php?id=' . $record->parent;
                    }
                    break;
            }
        }

        // The socialprofile link is for external sites so we can display it for all users.
        if ($record->artefacttype == 'socialprofile') {
            safe_require ( 'artefact', 'internal' );
            $record->note = str_replace ( array (
                            "\r\n",
                            "\n",
                            "\r"
            ), ' ', strip_tags ( $record->note ) );
            $socialprofile = new ArtefactTypeSocialprofile ( $record->id );
            $icons = $socialprofile->get_profile_icons ( array (
                            $record
            ) );
            if (! empty ( $icons )) {
                $record->link = $icons [0]->link;
                $record->icon = $icons [0]->icon;
            }
            else {
                // Instantiate the attribute used by the template.
                $record->icon = '';
            }
        }

        require_once( get_config ( 'docroot' ) . 'artefact/resume/lib.php' );
        if (PluginArtefactResume::is_active ()) {
            // If the artefacttype is one of the résumé ones we need to fetch the related item info
            $resumetypes = PluginArtefactResume::composite_tabs ();
            if (array_key_exists ( $record->artefacttype, $resumetypes )) {
                try {
                    $query = "SELECT * FROM {artefact_resume_" . $record->artefacttype . "} WHERE artefact = ?";
                    $results = get_records_sql_assoc ( $query, array (
                            $record->id
                    ) );
                }
                catch ( SQLException $e ) {
                    // Table doesn't exist
                    $results = array ();
                }
                $record->resumeitems = $results;
            }
        }

        // VIEWS get all the views the artefact is included into.
        // artefact parents are folder, blog, plan, cpd
        $sql = 'SELECT COALESCE(v.id, vp.id) AS id, COALESCE(v.title, vp.title) AS title
        FROM {artefact} a
        LEFT OUTER JOIN {view_artefact} va ON va.artefact = a.id
        LEFT OUTER JOIN {view} v ON v.id = va.view
        LEFT OUTER JOIN {artefact} parent ON parent.id = a.parent
        LEFT OUTER JOIN {view_artefact} vap ON vap.artefact = parent.id
        LEFT OUTER JOIN {view} vp ON vp.id = vap.view
        WHERE a.id = ?';

        $views = get_records_sql_array ( $sql, array (
                $id
        ) );

        if ($views) {
            $record_views = array ();
            foreach ( $views as $view ) {
                if (isset ( $view->id )) {
                    $record_views[$view->id] = $view->title;
                }
            }
            $record_views = self::views_by_artefact_acl_filter($record_views);
            $record->views = $record_views;
        }

        // Tags
        $tags = get_column ( 'tag', 'tag', 'resourcetype', 'artefact', 'resourceid', $id );
        if ($tags != false) {
            foreach ( $tags as $tag ) {
                $record->tags [] = $tag;
            }
        }
        else {
            $record->tags = null;
        }

        // Created by
        if (intval ( $record->author ) > 0) {
            $record->createdby = get_record ( 'usr', 'id', $record->author );
            $record->createdbyname = display_name ( $record->createdby );
        }

        // Thumb
        if ($record->artefacttype == 'image' || $record->artefacttype == 'profileicon') {
            if (isset ( $record->width ) && isset ( $record->height ) && intval ( $record->width ) > 0 && intval ( $record->height ) > 0) {
                if ($record->width > $record->height) {
                    $size = '80x' . intval ( $record->height * 80 / $record->width );
                }
                else {
                    $size = intval ( $record->width * 80 / $record->height ) . 'x80';
                }
            }
            $vars = array (
                'id' => $id,
                'size' => $size
            );
            if (!empty($record->views)) {
                $vars['viewid'] = key($record->views); // use first view we are can see
            }
            $record->thumb = ArtefactTypeImage::get_icon ($vars);
        }

        return $record;
    }

    /**
     * Get all access records of the views in which the artefact is included (UNION with parent artefact -> files and blog posts)
     */
    public static function view_access_records($artefactid) {
        $records = get_records_sql_array ( '
                SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
                FROM {view_access} vac
                INNER JOIN {view_artefact} vart ON vac.view = vart.view
                WHERE   vart.artefact = ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
                UNION
                SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
                FROM {artefact} art
                INNER JOIN {view_artefact} vart ON art.parent = vart.artefact
                INNER JOIN {view_access} vac ON vac.view = vart.view
                WHERE   art.id = ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
                ', array (
                $artefactid,
                $artefactid
        ) );

        if (is_isolated() && get_field_sql("SELECT v.type FROM {view} v
                                            JOIN {view_artefact} va ON va.view = v.id
                                            JOIN {artefact} a ON a.id = va.artefact
                                            WHERE a.id = ?", array($artefactid)) == 'profile') {
            if ($records) {
                foreach ($records as $k => $access) {
                    if ($access->accesstype == 'loggedin') {
                        unset($records[$k]);
                    }
                }
                $records = array_values($records);
            }
            $viewid = get_field('view_artefact', 'view', 'artefact', $artefactid);
            if (!get_records_sql_array("SELECT a.owner FROM {artefact} a
                                        JOIN {usr_institution} ui ON ui.usr = a.owner
                                        WHERE a.id = ?", array($artefactid))) {
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

    /**
     * Get all access records of the group artefacts (called if not attached to view)
     */
    public static function group_artefact_access_records($artefactid) {
        $records = get_records_sql_array ( '
                SELECT role, can_view FROM {artefact_access_role} WHERE artefact = ?
               ', array (
                $artefactid
        ) );
        return $records;
    }

    /**
     * Get views linked to a particular artefact, applying ACL
     * This is used to display the list of views in an artefact result, because it's faster to retrieve the info
     * from Elastic search that running the SQL query.
     */
    public static function views_by_artefact_acl_filter($views = array()) {
        global $USER;

        $acl = new ElasticsearchFilterAcl($USER);
        $viewmap = function($value) {
            return 'view' . $value;
        };

        $filter = array(
            "bool" => array(
                "must" => array(
                    array (
                        array (
                            'term' => array(
                                '_type' => 'doc'
                            )
                        ),
                        array (
                            'terms' => array(
                                '_id' => array_map($viewmap, array_keys($views))
                            )
                        )
                    )
                ),
                "should" => $acl->get_params() ['should']
            )
        );

        $client = PluginSearchElasticsearch::make_client();
        $params = array (
            'index' => PluginSearchElasticsearch::get_write_indexname(),
            'body' => array (
                'size' => 10,
                'query' => array (
                    'bool' => array (
                        'filter' => $filter
                    )
                )
            )
        );

        $results = $client->search ($params);
        $valid = array();
        if (!empty($results['hits'])) {
            foreach($results['hits']['hits'] as $item) {
                $valid[$item['_source']['id']] = $views[$item['_source']['id']];
            }
        }
        return $valid;
    }
}
