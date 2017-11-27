<?php
class ElasticsearchType_group extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
                    'copy_to' => 'catch_all'
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'name' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            'description' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            'grouptype' => array (
                    'type' => 'keyword',
            ),
            'jointype' => array (
                    'type' => 'keyword',
            ),
            // access to group
            'access' => array (
                    'type' => 'object',
                    'properties' => array (
                            'general' => array (
                                    'type' => 'keyword',
                            ),
                            'groups' => array (
                                    'type' => 'object',
                                    'properties' => array (
                                          'member' => array (
                                             'type' => 'integer',
                                             'copy_to' => 'group',
                                          )
                                     )
                            ),
                            'group' => array (
                                    'type' => 'integer'
                            )
                    )
            ),
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
            ),
            // sort is the field that will be used to sort the results alphabetically
            'sort' => array (
                    'type' => 'keyword',
            )
    );

    public static $mainfacetterm = 'Group';
    public function __construct($data) {
        $this->conditions = array (
                'deleted' => 0,
                'hidden' => 0
        );

        $this->mapping = array (
                'mainfacetterm' => NULL,
                'id' => NULL,
                'name' => NULL,
                'description' => NULL,
                'grouptype' => NULL,
                'jointype' => NULL,
                'access' => NULL,
                'ctime' => NULL,
                'sort' => NULL
        );

        parent::__construct ( $data );
    }
    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record || $record->deleted) {
            return false;
        }
        $record->access ['general'] = ($record->public) ? 'public' : 'loggedin';
        $record->access ['groups'] ['member'] = $record->id;
        $record->sort = strtolower ( strip_tags ( $record->name ) );
        return $record;
    }
    public static function getRecordDataById($type, $id) {
        $record = get_record ( $type, 'id', $id );
        if (! $record || $record->deleted) {
            return false;
        }
        $record->description = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->description ) );
        $record->groupadmins = group_get_admins ( array (
                $id
        ) );
        return $record;
    }
}
