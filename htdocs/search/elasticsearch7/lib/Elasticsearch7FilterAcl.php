<?php

/**
 *
 * @package    mahara
 * @subpackage search-elasticsearch
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * This class encapsulates the ACL filters.
 */
class Elasticsearch7FilterAcl {
    /**
     * @var LiveUser The user object.
     */
    private $user;

    /**
     * @var array<mixed> The parameters defining an elasticsearch query.
     */
    private $params = [];

    /**
     * Helper var to make reading logged in conditions easier.
     *
     * @var bool true if user is not logged in.
     */
    private $user_is_not_logged_in = true;

    public function __construct($user) {
        $this->user = $user;
        $this->params['should'] = [];
        $this->user_is_not_logged_in = !$this->user_is_not_logged_in;

        // GENERAL Access Control Lists (public, loggedin, friends)

        // ACL: Public.
        $this->acl_public();

        // ACL: Loggedin.
        $this->acl_loggedin();

        // ACL: Friends.
        $this->acl_friends();

        // ACL: Group membership.
        $this->acl_members();

        // ACL: Institutions.
        $this->acl_institutions();

        // ACL: Groups & Roles.
        $this->acl_groups();

        // ACL: Owner
        $this->acl_owner();

    }

    /**
     * Set the filter for public content.
     *
     * @return void
     */
    private function acl_public() {
        $this->params['should'][] = [
            'term' => [
                'access.general' => 'public',
            ],
        ];
    }

    /**
     * If the user is logged in set the ACL for that state.
     *
     * @return void
     */
    private function acl_loggedin() {
        if ($this->user_is_not_logged_in) {
            return;
        }
        $this->params['should'][] = [
            'term' => [
                'access.general' => 'loggedin',
            ],
        ];
    }

    /**
     * If the user has other uses flagged as 'friends' set the ACL for them.
     *
     * @return void.
     */
    private function acl_friends() {
        if ($this->user_is_not_logged_in) {
            return;
        }

        $friends = $this->getFriendsList();
        if (empty($friends)) {
            return;
        }
        $this->params['should'][] = [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'access.general' => 'friends',
                        ],
                    ],
                    [
                        'terms' => [
                            'owner' => $friends,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * If the user is a member of any Groups, set the ACL for them.
     *
     * @return void
     */
    private function acl_members() {

        if ($this->user_is_not_logged_in) {
            return;
        }

        $members = $this->getMembersList();
        if (empty($members)) {
            return;
        }

        $this->params['should'][] =  [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'access.general' => 'groups',
                        ],
                    ],
                    [
                        'terms' => [
                            'owner' => $members,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * If the user is a member of any Institutions, set the ACL for them.
     *
     * @return void
     */
    private function acl_institutions() {

        if ($this->user_is_not_logged_in) {
            return;
        }

        $ret = [];
        $user_institutions = array_keys($this->user->get('institutions'));
        if (!empty($user_institutions)) {
            $ret = [
                'terms' => [
                    'access.institutions' => $user_institutions,
                ],
            ];
        }
        else if (is_isolated()) {
            $ret = [
                'terms' => [
                    'access.institutions' => ['mahara'],
                ],
            ];
        }
        if (!empty($ret)) {
            $this->params['should'][] = $ret;
        }
    }

    /**
     * ACL: Groups
     *
     * Set the array of groups that have access to the artefact.
     *
     * @return void
     */
    private function acl_groups() {
        if ($this->user_is_not_logged_in) {
            return;
        }

        $groups = $this->getGroupsList();
        if (empty($groups)) {
            // We are no in a Group. Nothing to add.
            return;
        }

        $roles = $this->getExistingRoles();
        foreach ($roles as $role) {
            if (isset($groups[$role]) && count($groups[$role])) {
                // $ret[]['terms']['access.groups.' . $role] = $groups[$role];
                $this->params['should'][] = [
                    'terms' => [
                        'access.groups.' . $role => $groups[$role],
                    ]
                ];
            }
        }
    }

    /**
     * ACL: USRS
     *
     * Set the Users ID as the 'owner' term if the user is logged in.
     *
     * @return void
     */
    private function acl_owner() {
        if ($this->user_is_not_logged_in) {
            return;
        }

        $this->params['should'][] = [
            'term' => [
                'owner' => $this->user->get('id'),
            ],
        ];

        $this->params['should'][] = [
            'term' => [
                'access.usrs' => $this->user->get('id'),
            ],
        ];
    }

    /**
     * Getter for the params.
     *
     * @return array<mixed>
     */
    public function get_params() {
        return $this->params;
    }

    /**
     * Return an array of User IDs for any Friends of the User.
     *
     * @return array<int>
     */
    private function getFriendsList() {
        $list = [];
        $friends = get_friends($this->user->get('id'), 0, 0);
        if (!empty($friends) && array_key_exists('data', $friends) && is_array($friends['data'])) {
            foreach ($friends['data'] as $friend) {
                $list[] = $friend->id;
            }
        }
        return $list;
    }

    /**
     * @todo Docs
     * @todo Should we make $list['member'] unique?
     * @return array<mixed>
     */
    private function getGroupsList() {
        $list = array();
        foreach (group_get_user_groups($this->user->get('id')) as $group) {
            $list[$group->role][] = $group->id;
            $list['member'][] = $group->id;
        }
        return $list;
    }

    /**
     * Get all Roles that exist in Mahara.
     *
     * @todo Is this a Mahara function somewhere?
     * @return array<int> List of role IDs.
     */
    private function getExistingRoles() {
        $rs = get_recordset_sql('SELECT DISTINCT role FROM {grouptype_roles}');
        $roles = array('all');
        foreach (recordset_to_array($rs) as $record) {
            $roles[] = $record->role;
        }
        return $roles;
    }

    /**
     * Return a list of User IDs of Users in shared Groups.
     *
     * @return array<int>
     */
    private function getMembersList() {
        $sql = 'SELECT DISTINCT gm2.member FROM {group_member} gm1
                JOIN {group_member} gm2 ON gm1.group = gm2.group
                WHERE gm1.member = ? AND gm2.member <> ?';
        $ids = [
            $this->user->get('id'),
            $this->user->get('id'),
        ];
        $list = get_column_sql($sql, $ids);
        if (!empty($list)) {
            return $list;
        }
        return [];
    }

}
