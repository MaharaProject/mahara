<?php

class ElasticsearchType_artefact extends ElasticsearchType
{

    public static $mappingconf =    array(
            'mainfacetterm' =>  array(  // this is the 2nd level in the hierarchy artefacttype|artefactgroup|mainfacetterm
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'secfacetterm' =>  array(  // this is the 1st level in the hierarchy artefacttype|artefactgroup|mainfacetterm
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'id'        =>  array(
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'artefacttype' =>  array(
                    'type' => 'string',
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
            'tags'     =>  array(
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
            'access'        =>  array(
                    'type' => 'object',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE,
                    // public - logged - friends: if artefact is visible to public or logged-in users
                    // if public or logged, the other properties are ignored
                    'general' =>  array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'include_in_all' => FALSE
                    ),
                    // array of institutions that have access to the artefact
                    'institutions' =>  array(
                            'type' => 'string',
                            'index' => 'not_analyzed',
                            'index_name' => 'institution',
                            'include_in_all' => FALSE
                    ),
                    // array of groups that have access to the artefact - empty (all), member, admin
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
            // artefact_license.name is used to store the license
            'license'     =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
    );

    public static $mainfacetterm = 'Text'; // can be Text or Media depending on artefacttype
    public static $subfacetterm = 'artefacttype';

    public function __construct($data){

        $this->conditions =     array();

        $this->mapping =        array(
                'mainfacetterm' => NULL,
                'secfacetterm'  => NULL,
                'id'            => NULL,
                'artefacttype'  => NULL,
                'title'         => NULL,
                'description'   => NULL,
                'tags'          => NULL,
                'owner'         => NULL,
                'group'         => NULL,
                'institution'   => NULL,
                'access'        => NULL,
                'ctime'         => NULL,
                'sort'          => NULL,
                'license'       => NULL,
        );

        parent::__construct($data);

    }

    /**
     *   set if the record has to be indexed or removed from the index
     */
    public function setisDeleted(){

        parent::setisDeleted();
        // artefacts: if artefacttype is not selected, mark as deleted
        $artefacttypes = explode(',', get_config_plugin('search', 'elasticsearch', 'artefacttypes'));
        if (!in_array($this->mapping['artefacttype'], $artefacttypes)) {
            $this->isDeleted = true;
        }
    }

    public static function getRecordById($type, $id, $artefacttypesmap_array){
        $record = parent::getRecordById($type, $id);
        if (!$record) {
            return false;
        }

        // Tags
        $tags = get_records_array('artefact_tag', 'artefact', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags[] = $tag->tag;
            }
        }
        else {
            $record->tags = null;
        }
        // Access: get all the views where the artefact is included
        $access = self::view_access_records($id);
        $accessObj = self::access_process($access);
        if (!$access) {
            // File access: get viewable group media not attached to a view
            $groupaccess = self::group_artefact_access_records($id);
            if ($groupaccess) {
                foreach ($groupaccess as $access) {
                    $accessObj['groups'][$access->role][] = $access->can_view;
                }
            }
        }
        $record->access = $accessObj;

        // set 'mainfacetterm' & 'artefactgroup'
        $terms = explode("|", $artefacttypesmap_array[$record->artefacttype]);
        $record->mainfacetterm = $terms[2];
        $record->secfacetterm = $terms[1];

        require_once(get_config('docroot') . 'artefact/resume/lib.php');
        if (PluginArtefactResume::is_active()) {
            // If the artefacttype is one of the résumé ones we need to get the description
            // from this artefact's related résumé table. There is a one -> many relationship between
            // the artefact and the items but seen as all resume items are added
            // to a page when choosing One résumé field, rather than selecting them individually,
            // we can just blob together all the info for this résumé artefact into $record->description.

            $resumetypes = ArtefactTypeResumeComposite::get_composite_artefact_types();
            if (in_array($terms[0], $resumetypes)) {
                try {
                    $query = "SELECT * FROM {artefact_resume_" . $terms[0] . "} WHERE artefact = ?";
                    $results = get_records_sql_assoc($query, array($record->id));
                }
                catch (SQLException $e) {
                    // Table doesn't exist
                    $results = array();
                }
                foreach ($results as $result) {
                    $items = get_object_vars($result);
                    foreach ($items as $key => $item) {
                        if (!in_array($key, array('id', 'artefact','displayorder'))) {
                            $record->description .= $item . ' ';
                        }
                    }
                }
            }
        }

        // AS the field "sort" is not analyzed, we need to clean it (remove html tags & lowercase)
        $record->sort = strtolower(strip_tags($record->title));

        return $record;
    }

    public static function getRecordDataById($type, $id){

        global $USER;

        $sql = 'SELECT  a.id, a.artefacttype, a.parent, a.owner, a.title, a.description, a.institution, a.group, a.author,
        p.artefacttype AS parent_artefacttype, p.title AS parent_title, p.description  AS parent_description, a.license,
        afi.width, afi.height, a.note
        FROM {artefact} a
        LEFT OUTER JOIN {artefact} p ON p.id = a.parent
        LEFT OUTER JOIN {artefact_file_image} afi ON afi.artefact = a.id
        WHERE a.id = ?';

        $record = get_record_sql($sql, array($id));
        if (!$record) {
            return false;
        }

        $record->title = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->title));
        $record->description = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->description));
        // If user is owner
        if ($USER->get('id') == $record->owner) {
            switch ($record->artefacttype) {
                case 'image':
                case 'video':
                case 'audio':
                case 'file':
                    $record->link = 'artefact/file';
                    if (isset($record->parent) && intval($record->parent) > 0) {
                        $record->link .= '/index.php?folder=' . $record->parent;
                    }
                    break;
                case 'blogpost':
                    if (isset($record->parent) && intval($record->parent) > 0) {
                        $record->link = 'artefact/blog/view/index.php?id=' . $record->parent;
                    }
                    break;
                case 'blog':
                    $record->link = 'artefact/blog/view/index.php';
                    if ($USER->get_account_preference('multipleblogs')) {
                        $record->link .= '?id=' . $record->id;
                    }
                    break;
                case 'coverletter':
                case 'personalinformation':
                    $record->link = 'artefact/resume/index.php';
                    break;
                case 'educationhistory':
                case 'employmenthistory':
                    $record->link = 'artefact/resume/employment.php';
                    break;
                case 'book':
                case 'certification':
                case 'membership':
                    $record->link = 'artefact/resume/achievements.php';
                    break;
                case 'academicgoal':
                case 'careergoal':
                case 'personalgoal':
                case 'personalinformation':
                case 'academicskill':
                case 'personalskill':
                case 'workskill':
                    $record->link = 'artefact/resume/goalsandskills.php';
                    break;
                case 'interest':
                    $record->link = 'artefact/resume/interests.php';
                    break;
                case 'plan':
                    $record->link = 'artefact/plans/plan.php?id=' . $record->id;
                    break;
                case 'task':
                    if (isset($record->parent) && intval($record->parent) > 0) {
                        $record->link = 'artefact/plans/plan.php?id=' . $record->parent;
                    }
                    break;
                case 'socialprofile':
                    safe_require('artefact', 'internal');
                    $record->note = str_replace(array("\r\n", "\n", "\r"), ' ', strip_tags($record->note));
                    $socialprofile = new ArtefactTypeSocialprofile($record->id);
                    $icons = $socialprofile->get_profile_icons(array($record));
                    if (!empty($icons)) {
                        $record->link = $icons[0]->link;
                        $record->icon = $icons[0]->icon;
                    }
                    break;
            }
        }

        require_once(get_config('docroot') . 'artefact/resume/lib.php');
        if (PluginArtefactResume::is_active()) {
            // If the artefacttype is one of the résumé ones we need to fetch the related item info
            $resumetypes = PluginArtefactResume::composite_tabs();
            if (array_key_exists($record->artefacttype, $resumetypes)) {
                try {
                    $query = "SELECT * FROM {artefact_resume_" . $record->artefacttype . "} WHERE artefact = ?";
                    $results = get_records_sql_assoc($query, array($record->id));
                }
                catch (SQLException $e) {
                    // Table doesn't exist
                    $results = array();
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

        $views = get_records_sql_array($sql, array($id));
        if ($views) {
            $record_views = array();
            foreach($views AS $view){
                if (isset($view->id)) {
                    $record_views[$view->id] = $view->title;
                }
            }

            $record_views = self::views_by_artefact_acl_filter($record_views);
            $record->views = $record_views;
        }

        //  Tags
        $tags = get_records_array('artefact_tag', 'artefact', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags[] = $tag->tag;
            }
        }
        else {
            $record->tags = null;
        }

        //  Created by
        if (intval($record->author) > 0) {
            $record->createdby = get_record('usr', 'id', $record->author);
            $record->createdbyname = display_name($record->createdby);
        }

        //  Thumb
        if ($record->artefacttype == 'image' || $record->artefacttype == 'profileicon') {
            if (isset($record->width) && isset($record->height) && intval($record->width) > 0 && intval($record->height) > 0) {
                if ($record->width > $record->height) {
                    $size = '80x' . intval($record->height * 80 / $record->width);
                }
                else {
                    $size = intval($record->width * 80 / $record->height) . 'x80';
                }
            }
            $record->thumb = ArtefactTypeImage::get_icon(array('id' => $id, 'size' => $size));
        }

        return $record;

    }

    /**
     * Get all access records of the views in which the artefact is included (UNION with parent artefact -> files and blog posts)
     */
    public static function view_access_records($artefactid) {

        $records = get_records_sql_array('
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
                ',
                array($artefactid, $artefactid)
        );

        return $records;
    }

    /**
     * Get all access records of the group artefacts (called if not attached to view)
     */
    public static function group_artefact_access_records($artefactid) {
        $records = get_records_sql_array('
                SELECT role, can_view FROM {artefact_access_role} WHERE artefact = ?
               ',
               array($artefactid)
        );
        return $records;
    }

    /**
     * Get views linked to a particular artefact, applying ACL
     * This is used to display the list of views in an artefact result, because it's faster to retrieve the info
     * from Elastic search that running the SQL query.
     */
    public static function views_by_artefact_acl_filter($views = array()) {

        global $USER;
        $ret = array();

        $elasticaClient = PluginSearchElasticsearch::make_client();
        $elasticaIndex = $elasticaClient->getIndex(get_config_plugin('search', 'elasticsearch', 'indexname'));

        $elasticaQuery = new \Elastica\Query();
        // check user access to the views
        $elasticaFilterAnd  = new \Elastica\Filter\BoolAnd();

        $elasticaFilterType = new \Elastica\Filter\Term(array('_type' => 'view'));
        $elasticaFilterAnd->addFilter($elasticaFilterType);
        $elasticaFilterIds = new \Elastica\Filter\Terms('id', array_keys($views));
        $elasticaFilterAnd->addFilter($elasticaFilterIds);

        // Apply ACL filters
        $elasticaFilterACL   = new ElasticsearchFilterAcl($USER);
        $elasticaFilterAnd->addFilter($elasticaFilterACL);

        $elasticaFilteredQuery = new \Elastica\Query\Filtered(null, $elasticaFilterAnd);
        $elasticaQuery->setQuery($elasticaFilteredQuery);

        $elasticaResultSet  = $elasticaIndex->search($elasticaQuery);
        $elasticaResults    = $elasticaResultSet->getResults();

        foreach ($elasticaResults as $elasticaResult) {
            $data = $elasticaResult->getData();
            $ret[$data['id']] = $views[$data['id']];
        }

        return $ret;
    }

}
