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
        $record->mainfacetterm = self::$mainfacetterm;
        $allowhidename = get_config('userscanhiderealnames');
        $showusername = get_config('searchusernames');
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
