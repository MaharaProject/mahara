<?php

class ElasticsearchType_usr extends ElasticsearchType
{
    public static $mappingconf =    array(
            'mainfacetterm' =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'id'        =>  array(
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'email'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'username'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'firstname'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'lastname'     =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'preferredname' =>  array(
                    'type' => 'string',
                    'include_in_all' => TRUE
            ),
            'institutions'  =>  array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
                    'index_name' => 'institution',
                    'include_in_all' => FALSE
            ),
            // access to user  - to be able to hide user from public search
            'access' => array(
                'type' => 'object',
                'index' => 'not_analyzed',
                'include_in_all' => FALSE,
                'general' => array(
                    'type' => 'string',
                    'index' => 'not_analyzed',
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

    public static $mainfacetterm = 'User';

    public function __construct($data){

        $this->conditions =     array(
                'active' => 1,
                'deleted' => 0,
        );

        $this->mapping =        array(
                'mainfacetterm' => NULL,
                'id'            => NULL,
                'email'         => NULL,
                'username'      => NULL,
                'firstname'     => NULL,
                'lastname'      => NULL,
                'preferredname' => NULL,
                'institutions'  => NULL,
                'access'        => NULL,
                'ctime'         => NULL,
                'sort'          => NULL,
        );

        parent::__construct($data);

    }

    public static function getRecordById($type, $id){
        $sql = 'SELECT u.id, u.username, u.preferredname, ap.value AS hidenamepref,
        CASE ap.value WHEN \'1\' THEN NULL ELSE u.firstname END AS firstname,
        CASE ap.value WHEN \'1\' THEN NULL ELSE u.lastname END AS lastname,
        u.active, u.deleted, u.email, u.ctime
        FROM {usr} u
        LEFT JOIN {usr_account_preference} ap ON (u.id = ap.usr AND ap.field = \'hiderealname\')
        WHERE u.id = ?';

        $record = get_record_sql($sql, array($id));
        if (!$record || $record->deleted) {
            return false;
        }

        $record->ctime = self::checkctime($record->ctime);

        // institutions
        $institutions = get_records_array('usr_institution', 'usr', $record->id);
        if ($institutions != false) {
            foreach ($institutions as $institution) {
                $record->institutions[] = $institution->institution;
            }
        }
        else {
            $record->institutions = null;
        }
        // extra email addresses. A few users registered several email addresses as artefact.
        $sqlemail = "SELECT a.title AS email FROM {usr} u INNER JOIN {artefact} a ON a.owner = u.id AND artefacttype = 'email'
        WHERE u.email != a.title AND u.id = ? AND a.title != ?";
        $emails = recordset_to_array(get_recordset_sql($sqlemail, array($record->id, $record->email)));
        if ($emails != false) {
            // the email property will hold an array instead of just a string
            $email = $record->email;
            unset($record->email);
            $record->email[] = $email;
            foreach ($emails as $email) {
                $record->email[] = $email->email;
            }
        }
        // check to see if the user's profile page is viewable and which is the most 'open' access
        $accessrank = array('loggedin','friends');
        if (get_config('searchuserspublic')) {
            array_unshift($accessrank, 'public');
        }

        // get all accesses of user's profile page ordered by the $accessrank array
        // so that the first result will be the most 'open' access allowed
        if (is_postgres()) {
            $join = '';
            $count = 0;
            foreach ($accessrank as $key => $access) {
                $count++;
                $join .= "('" . $access . "'," . $key . ")";
                if ($count != sizeof($accessrank)) {
                    $join .= ",";
                }
            }
            $sql = "SELECT va.accesstype FROM {view} v, {view_access} va
                    JOIN (VALUES" . $join . ") AS x (access_type, ordering) ON va.accesstype = x.access_type
                    WHERE v.id = va.view AND v.type = 'profile' AND v.owner = ? ORDER BY x.ordering";

        }
        else {
            $join = "'" . join('\',\'', $accessrank) . "'";
            $sql = "SELECT va.accesstype FROM {view} v, {view_access} va
                    WHERE v.id = va.view AND v.type = 'profile' AND v.owner = ?
                    AND accesstype IN (" . $join . ") ORDER BY FIELD(va.accesstype, " . $join . ")";
        }
        $profileviewaccess = recordset_to_array(get_recordset_sql($sql, array($record->id)));
        $record->access['general'] = (!empty($profileviewaccess)) ? $profileviewaccess[0]->accesstype : 'none';
        // always allow user to search themselves for vanity reasons
        $record->access['usrs'] = $record->id;

        $record->mainfacetterm = self::$mainfacetterm;
        $allowhidename = get_config('userscanhiderealnames');
        $showusername = !get_config('nousernames');
        $record->sort = strtolower(strip_tags(display_name($record, null, false, !$allowhidename || !$record->hidenamepref, $showusername)));

        return $record;
    }


    public static function getRecordDataById($type, $id){
        $record = get_record('usr', 'id', $id);
        if (!$record || $record->deleted) {
            return false;
        }

        $record->display_name = display_name($record);
        $record->introduction = get_field('artefact', 'title', 'owner', $id, 'artefacttype', 'introduction');

        return $record;
    }


}
