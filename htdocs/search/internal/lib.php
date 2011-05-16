<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage search-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * The internal search plugin which searches against the
 * Mahara database.
 */
class PluginSearchInternal extends PluginSearch {

    public static function can_be_disabled() {
        return false;
    }

    /**
     * Implement user searching with SQL
     *
     * NOTE: user with ID zero should never be returned
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param data    Filters the users searched by
     *                can contain:
     *             'group' => integer, // only users in this group
     *             'owner' => boolean  // include the group ownwer (only if group is set)
     *             'exclude'=> int     // excludes a user
     * @return array  A data structure containing results looking like ...
     *         $results = array(
     *               count   => integer, // total number of results
     *               limit   => integer, // how many results are returned
     *               offset  => integer, // starting from which result
     *               data    => array(   // the result records
     *                   array(
     *                       id            => integer,
     *                       username      => string,
     *                       institution   => string,
     *                       firstname     => string,
     *                       lastname      => string,
     *                       preferredname => string,
     *                       email         => string,
     *                   ),
     *                   array(
     *                       id            => integer,
     *                       username      => string,
     *                       institution   => string,
     *                       firstname     => string,
     *                       lastname      => string,
     *                       preferredname => string,
     *                       email         => string,
     *                   ),
     *                   array(...),
     *               ),
     *           );
     */
    public static function search_user($query_string, $limit, $offset = 0, $data=array()) {
        safe_require('artefact', 'internal');
        $publicfields = array_keys(ArtefactTypeProfile::get_public_fields());
        if (empty($publicfields)) {
            $publicfields = array('preferredname');
        }
        $fieldlist = '(' . join(',', array_map('db_quote', $publicfields)) . ')';
        $ilike = db_ilike();
        $data = self::prepare_search_user_options($data);
        $sql = '
            SELECT
                COUNT(DISTINCT u.id)
            FROM {artefact} a
                INNER JOIN {usr} u ON u.id = a.owner';
        if (isset($data['group'])) {
            $groupadminsql = '';
            if (isset($data['includeadmins']) and !$data['includeadmins']) {
                $groupadminsql = " AND gm.role != 'admin'";
            }
            $groupjoin = '
                INNER JOIN {group_member} gm ON
                    (gm.member = u.id AND gm.group = ' . (int)$data['group'] . $groupadminsql . ")\n";
            $sql .= $groupjoin;
        }
        $sql .= "
                LEFT OUTER JOIN {usr_account_preference} h ON (u.id = h.usr AND h.field = 'hiderealname')";
        $querydata = split(' ', preg_replace('/\s\s+/', ' ', strtolower(trim($query_string))));
        $hidenameallowed = get_config('userscanhiderealnames') ? 'TRUE' : 'FALSE';
        $searchusernamesallowed = get_config('searchusernames') ? 'TRUE' : 'FALSE';
        $namesql = "(u.preferredname $ilike '%' || ? || '%')
                    OR ((u.preferredname IS NULL OR u.preferredname = '' OR NOT $hidenameallowed OR h.value != '1')
                        AND (u.firstname $ilike '%' || ? || '%' OR u.lastname $ilike '%' || ? || '%'))
                    OR ($searchusernamesallowed AND u.username $ilike '%' || ? || '%')
                    OR (a.artefacttype IN $fieldlist
                        AND ( a.title $ilike '%' || ? || '%'))";
        $namesql = join('
                    OR ', array_fill(0, count($querydata), $namesql));
        $values = array();
        foreach ($querydata as $w) {
            $values = array_pad($values, count($values) + 5, $w);
        }

        $where = '
            WHERE
                u.id <> 0 AND u.active = 1 AND u.deleted = 0
                AND (
                    ' . $namesql . '
                )
                ' . (isset($data['exclude']) ? 'AND u.id != ' . $data['exclude'] : '') . '
            ';

        $sql .= $where;
        $count = count_records_sql($sql, $values);

        $result = array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => false,
        );

        if ($count < 1) {
            return $result;
        }

        if (is_postgres()) {
            $sql = '
            SELECT DISTINCT ON (' . $data['orderby'] . ')
                u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.staff
            FROM {artefact} a
                INNER JOIN {usr} u ON u.id = a.owner';
        }
        else if (is_mysql()) {
            // @todo This is quite possibly not correct. See the postgres 
            // query. It should be DISTINCT ON the fields as specified by the 
            // postgres query. There is a workaround by using SELECT * followed 
            // by GROUP BY 1, 2, 3, but it's not really valid SQL. The other 
            // way is to rewrite the query using a self join on the usr table.
            $sql = '
            SELECT DISTINCT
                u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.staff
            FROM {artefact} a
                INNER JOIN {usr} u ON u.id = a.owner';
        }
        else {
            throw new SQLException('search_user() not implemented for your database engine (' . get_config('dbtype') . ')');
        }

        if (isset($data['group'])) {
            $sql .= $groupjoin;
        }
        $sql .= "
                LEFT OUTER JOIN {usr_account_preference} h ON (u.id = h.usr AND h.field = 'hiderealname')";

        $sql .= $where . '
            ORDER BY ' . $data['orderby'];

        $result['data'] = get_records_sql_array($sql, $values, $offset, $limit);

        if ($result['data']) {
            foreach ($result['data'] as &$item) {
                $item = (array)$item;
            }
        }

        return $result;
    }

    private static function prepare_search_user_options($options) {
        if (isset($options['group'])) {
            $options['group'] = intval($options['group']);
        }
        if (isset($options['includeadmins'])) {
            $options['includeadmins'] = (bool)$options['includeadmins'];
        }
        if (isset($options['orderby']) && $options['orderby'] == 'lastname') {
            $options['orderby'] = 'u.lastname, u.firstname, u.id';
        }
        else {
            $options['orderby'] = 'u.firstname, u.lastname, u.id';
        }
        if (isset($options['exclude'])) {
            $options['exclude'] = intval($options['exclude']);
        }
        return $options;
    }
    

    private static function match_expression($op, $string, &$values, $ilike) {
        switch ($op) {
        case 'starts':
            $values[] = $string;
            return ' ' . $ilike . ' ? || \'%\'';
        case 'equals':
            $values[] = $string;
            return ' = ? ';
        case 'contains':
            $values[] = $string;
            return ' ' . $ilike . ' \'%\' || ? || \'%\'';
        case 'in':
            return ' IN (' . join(',', array_map('db_quote',$string)) . ')';
        }
    }


    public static function admin_search_user($queries, $constraints, $offset, $limit, 
                                             $sortfield, $sortdir) {
        $sort = 'TRUE';
        if (preg_match('/^[a-zA-Z_0-9"]+$/', $sortfield)) {
            $sort = $sortfield;
            if (strtoupper($sortdir) != 'DESC') {
                $sort .= ' ASC';
            }
            else {
                $sort .= ' DESC';
            }
        }
        $where = 'WHERE u.id <> 0 AND u.deleted = 0';
        $values = array();

        // Get the correct keyword for case insensitive LIKE
        $ilike = db_ilike();

        // Only handle OR/AND expressions at the top level.  Eventually we may need subexpressions.

        if (!empty($queries)) {
            $where .= ' AND ( ';
            $str = array();
            foreach ($queries as $f) {
                if (!preg_match('/^[a-zA-Z_0-9"]+$/', $f['field'])) {
                    continue; // skip this field as it fails validation
                }
                $str[] = 'u.' . $f['field'] 
                    . PluginSearchInternal::match_expression($f['type'], $f['string'], $values, $ilike);
            }
            $where .= join(' OR ', $str) . ') ';
        } 

        // @todo: Institution stuff is messy and will probably need to
        // be rewritten when we get multiple institutions per user

        $requested = false;
        $institutionsearch = '';
        if (!empty($constraints)) {
            foreach ($constraints as $f) {
                if (strpos($f['field'], 'institution') === 0) {
                    $institutionsearch .= ' 
                        LEFT OUTER JOIN {usr_institution} i ON i.usr = u.id ';
                    if (strpos($f['field'], 'requested') > 0) {
                        $institutionsearch .= ' 
                            LEFT OUTER JOIN {usr_institution_request} ir ON ir.usr = u.id ';
                    }
                    if ($f['field'] == 'institution_requested') {
                        $where .= ' AND ( i.institution ' .
                            PluginSearchInternal::match_expression($f['type'], $f['string'], $values, $ilike) .
                            ' OR ir.institution ' .
                            PluginSearchInternal::match_expression($f['type'], $f['string'], $values, $ilike) . ')';
                        $requested = true;
                    } else {
                        if ($f['string'] == 'mahara') {
                            $where .= ' AND i.institution IS NULL';
                        } else {
                            $where .= ' AND i.institution'
                                . PluginSearchInternal::match_expression($f['type'], $f['string'], $values, $ilike);
                        }
                    }
                } else {
                    $where .= ' AND u.' . $f['field'] 
                        . PluginSearchInternal::match_expression($f['type'], $f['string'], $values, $ilike);
                }
            }
        }
        if (strpos($sort, 'institution') === 0) {
            $sort = 'i.' . $sort;
            if ($institutionsearch == '') {
                $institutionsearch = ' LEFT OUTER JOIN {usr_institution} i ON i.usr = u.id ';
            }
        }

        $count = get_field_sql('SELECT COUNT(*) FROM {usr} u ' . $institutionsearch . $where, $values);

        if ($count > 0) {
            $data = get_records_sql_assoc('
                SELECT 
                    u.id, u.firstname, u.lastname, u.username, u.email, u.staff, u.profileicon,
                    u.active, NOT u.suspendedcusr IS NULL as suspended
                FROM
                    {usr} u ' . $institutionsearch . $where . '
                ORDER BY ' . $sort,
                $values,
                $offset,
                $limit);

            if ($data) {
                $inst = get_records_select_array('usr_institution', 
                                                 'usr IN (' . join(',', array_keys($data)) . ')',
                                                 null, '', 'usr,institution');
                if ($inst) {
                    foreach ($inst as $i) {
                        $data[$i->usr]->institutions[] = $i->institution;
                    }
                }
                if ($requested) {
                    $inst = get_records_select_array('usr_institution_request', 
                                                     'usr IN (' . join(',', array_keys($data)) . ')',
                                                     null, '', 'usr,institution,confirmedusr,confirmedinstitution');
                    if ($inst) {
                        foreach ($inst as $i) {
                            if ($i->confirmedusr) {
                                $data[$i->usr]->requested[] = $i->institution;
                            }
                            if ($i->confirmedinstitution) {
                                $data[$i->usr]->invitedby[] = $i->institution;
                            }
                        }
                    }
                }
                foreach ($data as &$item) {
                    $item->username = display_username($item);
                    $item = (array)$item;
                }
                $data = array_values($data);
            }
        }
        else {
            $data = false;
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $data,
        );
    }


    public static function group_search_user($group, $queries, $constraints, $offset, $limit, $membershiptype, $order=null) {
        // Only handle OR/AND expressions at the top level.  Eventually we may need subexpressions.
        $searchsql = '';
        $values = array();
        if (!empty($queries)) {
            $ilike = db_ilike();
            $searchsql .= ' AND ( ';
            $str = array();
            foreach ($queries as $f) {
                if (!preg_match('/^[a-zA-Z_0-9"]+$/', $f['field'])) {
                    continue; // skip this field as it fails validation
                }
                $str[] = 'u.' . $f['field'] 
                    . PluginSearchInternal::match_expression($f['type'], $f['string'], $values, $ilike);
            }
            $searchsql .= join(' OR ', $str) . ') ';
        }

        if ($membershiptype == 'nonmember') {
            $select = '
                    u.id, u.firstname, u.lastname, u.username, u.email, u.profileicon, u.staff';
            $from = '
                FROM {usr} u
                WHERE u.id > 0 AND u.deleted = 0 ' . $searchsql . '
                    AND NOT u.id IN (SELECT member FROM {group_member} gm WHERE gm.group = ?)';
            $values[] = $group;
            $orderby = 'u.firstname, u.lastname, u.id';
        }
        else if ($membershiptype == 'notinvited') {
            $select = '
                    u.id, u.firstname, u.lastname, u.username, u.email, u.profileicon, u.staff';
            $from = '
                FROM {usr} u
                WHERE u.id > 0 AND u.deleted = 0 ' . $searchsql . '
                    AND NOT u.id IN (SELECT member FROM {group_member} gm WHERE gm.group = ?)
                    AND NOT u.id IN (SELECT member FROM {group_member_invite} gmi WHERE gmi.group = ?)';
            $values[] = $group;
            $values[] = $group;
            $orderby = 'u.firstname, u.lastname, u.id';
        }
        else if ($membershiptype == 'request') {
            $select = '
                    u.id, u.firstname, u.lastname, u.username, u.email, u.profileicon,
                    u.staff, ' . db_format_tsfield('gmr.ctime', 'jointime');
            $from = '
                FROM {usr} u
                    INNER JOIN {group_member_request} gmr ON (gmr.member = u.id)
                WHERE u.id > 0 AND u.deleted = 0 ' . $searchsql . '
                    AND gmr.group = ?';
            $values[] = $group;
            $orderby = 'u.firstname, u.lastname, gmr.ctime, u.id';
        }
        else if ($membershiptype == 'invite') {
            $select = '
                    u.id, u.firstname, u.lastname, u.username, u.email, u.profileicon,
                    u.staff, ' . db_format_tsfield('gmi.ctime', 'jointime');
            $from = '
                FROM {usr} u
                    INNER JOIN {group_member_invite} gmi ON (gmi.member = u.id)
                WHERE u.id > 0 AND u.deleted = 0 ' . $searchsql . '
                    AND gmi.group = ?';
            $values[] = $group;
            $orderby = 'u.firstname, u.lastname, gmi.ctime, u.id';
        }
        else { // All group members
            $select = '
                    u.id, u.firstname, u.lastname, u.username, u.email, u.profileicon,
                    u.staff, ' . db_format_tsfield('gm.ctime', 'jointime') . ', gm.role';
            $from = '
                FROM {usr} u
                    INNER JOIN {group_member} gm ON (gm.member = u.id)
                WHERE u.id > 0 AND u.deleted = 0 ' . $searchsql . '
                    AND gm.group = ?';
            $values[] = $group;
            $orderby = "gm.role = 'admin' DESC, u.firstname, u.lastname, gm.ctime, u.id";
            if ($order == 'latest') {
                $orderby = 'gm.ctime DESC, u.firstname, u.lastname, u.id';
            }
        }

        if ($order == 'random') {
            $orderby = db_random();
        }

        $count = get_field_sql('SELECT COUNT(*)' . $from, $values);

        if ($count > 0) {
            $data = get_records_sql_assoc('
                SELECT ' . $select . $from . ' ORDER BY ' . $orderby,
                $values,
                $offset,
                $limit);

            if ($data) {
                foreach ($data as &$item) {
                    $item = (array)$item;
                }
                $data = array_values($data);
            }
        }
        else {
            $data = array();
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $data,
        );
    }


    public static function institutional_admin_search_user($query, $institution, $limit) {
        $sql = '
            FROM {usr} u ';

        $where = '
            WHERE u.id <> 0 AND u.deleted = 0 ';

        $values = array();
        if (!empty($query)) {
            $query = preg_replace('/\s\s+/', ' ', $query);
            $words = explode(' ', $query);
            foreach ($words as &$word) {
                $values[] = $word;
                $values[] = $word;
                $word = 'u.firstname ' . db_ilike() . ' \'%\' || ? || \'%\' OR u.lastname ' . db_ilike() . ' \'%\' || ? || \'%\'';
            }
            $where .= '
                AND (' . join(' OR ', $words) . ') ';
        }

        $studentid = '';
        if (!is_null($institution->member)) {
            $sql .= '
                LEFT OUTER JOIN {usr_institution} member ON (member.usr = u.id
                    AND member.institution = ' . db_quote($institution->name) . ')';
            $where .= '
                AND ' . ($institution->member ? ' NOT ' : '') . ' member.usr IS NULL';
            $studentid = ', member.studentid';
        }
        if (!is_null($institution->requested) || !is_null($institution->invitedby)) {
            $sql .= '
                LEFT OUTER JOIN {usr_institution_request} req ON (req.usr = u.id
                    AND req.institution = ' . db_quote($institution->name) . ')';
            if (!is_null($institution->requested)) {
                if ($institution->requested == 1) {
                    $where .= ' AND req.confirmedusr = 1';
                } else {
                    $where .= ' AND (req.confirmedusr = 0 OR req.confirmedusr IS NULL)';
                }
                $studentid = ', req.studentid';
            }
            if (!is_null($institution->invitedby)) {
                if ($institution->requested == 1) {
                    $where .= ' AND req.confirmedinstitution = 1';
                } else {
                    $where .= ' AND (req.confirmedinstitution = 0 OR req.confirmedinstitution IS NULL)';
                }
            }
        }

        $count = get_field_sql('SELECT COUNT(*) ' . $sql . $where, $values);

        if ($count > 0) {
            $data = get_records_sql_array('
                SELECT 
                    u.id, u.firstname, u.lastname, u.username, u.preferredname, 
                    u.admin, u.staff' . $studentid . $sql . $where . '
                ORDER BY u.firstname ASC',
                $values,
                0,
                $limit);
            foreach ($data as &$item) {
                $item = (array)$item;
            }
        }
        else {
            $data = false;
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => 0,
            'data'    => $data,
        );
    }





    /**
     * Implement group searching with SQL
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Which groups to search (all, member, notmember)
     * @return array  A data structure containing results looking like ...
     *         $results = array(
     *               count   => integer, // total number of results
     *               limit   => integer, // how many results are returned
     *               offset  => integer, // starting from which result
     *               data    => array(   // the result records
     *                   array(
     *                       id            => integer,
     *                       name          => string,
     *                       description   => string,
     *                       jointype      => string,
     *                       owner         => string,
     *                       ctime         => string,
     *                       mtime         => string,
     *                   ),
     *                   array(
     *                       id            => integer,
     *                       name          => string,
     *                       description   => string,
     *                       jointype      => string,
     *                       owner         => string,
     *                       ctime         => string,
     *                       mtime         => string,
     *                   ),
     *                   array(...),
     *               ),
     *           );
     */
    public static function search_group($query_string, $limit, $offset=0, $type='member', $category='') {
        global $USER;
        $data = array();

        $sql = "
            FROM
                {group}
            WHERE (
                name " . db_ilike() . " '%' || ? || '%'
                OR description " . db_ilike() . " '%' || ? || '%'
            ) AND deleted = 0 ";
        $values = array($query_string, $query_string);

        if (!$grouproles = join(',', array_keys($USER->get('grouproles')))) {
            $grouproles = '-1';
        }

       if ($type == 'member') {
            $sql .=  'AND id IN (' . $grouproles . ')';
        }
        else if ($type == 'notmember') {
            $sql .= 'AND id NOT IN (' . $grouproles . ')';
        }
        if (!empty($category)) {
            if ($category == -1) { //find unassigned groups
                $sql .= " AND category IS NULL";
            } else {
                $sql .= ' AND category = ?';
                $values[] = $category;
            }
        }

        $count = get_field_sql('SELECT COUNT(*) '.$sql, $values);

        if ($count > 0) {
            $sql = 'SELECT id, name, description, grouptype, jointype, public, ctime, mtime, category ' . $sql . ' ORDER BY name';
            $data = get_records_sql_array($sql, $values, $offset, $limit);
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $data,
        );
    }

    /**
     * Given a query string and limits, return an array of matching objects
     * owned by the current user.  Possible return types are ...
     *   - artefact
     *   - view
     *
     * Implementations of this search should search across tags for artefacts
     * and views at a minimum. Ideally the search would also index
     * title/description and other metadata for these objects.
     *
     * NOTE: This implementation of internal search only does artefacts, and
     * badly at that. See the bug tracker for more information.
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Type to search for (either 'all' or one of the types above).
     * 
     */
    public static function self_search($querystring, $limit, $offset, $type = 'all') {
        global $USER;
        if (trim($querystring) == '') {
            return false;
        }

        // Tokenise the search
        $querydata = self::search_parse_query($querystring);

        $sql = "
            SELECT
                a.id, a.artefacttype, a.title, a.description
            FROM
                {artefact} a
            LEFT JOIN {artefact_tag} at ON (at.artefact = a.id)
            WHERE
                a.owner = ?
            AND (
                ($querydata[0])
                OR
                (LOWER(at.tag) = ?)
            )";
        $count_sql = "
            SELECT
                COUNT(*)
            FROM
                {artefact} a
            LEFT JOIN {artefact_tag} at ON (at.artefact = a.id)
            WHERE
                a.owner = ?
            AND (
                ($querydata[0])
                OR
                (LOWER(at.tag) = ?)
            )";
        array_unshift($querydata[1], $USER->get('id'));
        array_push($querydata[1], $querystring);

        $results = array(
            'data'   => get_records_sql_array($sql, $querydata[1], $offset, $limit),
            'offset' => $offset,
            'limit'  => $limit,
            'count'  => get_field_sql($count_sql, $querydata[1])
        );

        if ($results['data']) {
            foreach ($results['data'] as &$result) {
                $newresult = array();
                foreach ($result as $key => &$value) {
                    if ($key == 'id' || $key == 'artefacttype' || $key == 'title' || $key == 'description') {
                        $newresult[$key] = $value;
                    }
                }
                $newresult['type'] = 'artefact';
                $artefactplugin = get_field('artefact_installed_type', 'plugin', 'name', $newresult['artefacttype']);
                if ($artefactplugin == 'internal') {
                    $newresult['summary'] = $newresult['title'];
                    $newresult['title'] = get_string($newresult['artefacttype'], 'artefact.' . $artefactplugin);
                }
                else {
                    $newresult['summary'] = $newresult['description'];
                }
                $newresult['summary'] = clean_html($newresult['summary']);
                $result = $newresult;
            }

            self::self_search_make_links($results);
        }

        return $results;
    }


    /**
     * Returns portfolio items (artefacts, views) owned by $owner and tagged
     * with $tag.
     *
     * @param string   $tag Tag
     * @param object   $owner: owner type (user,group,institution), and id
     * @param integer  $limit
     * @param integer  $offset
     * @param string   $sort
     * @param array    $types view/artefacttype filters
     * @param boolean  $returntags Return all the tags that have been attached to each result
     */
    public static function portfolio_search_by_tag($tag, $owner, $limit, $offset, $sort, $types, $returntags) {
        $viewfilter = is_null($types) || $types['view'] == true ? 'AND TRUE' : 'AND FALSE';

        if (is_null($types)) {
            $artefacttypefilter = '';
        }
        else if (!empty($types['artefact'])) {
            $artefacttypefilter = ' AND a.artefacttype IN (' . join(',', array_map('db_quote', $types['artefact'])) . ')';
        }
        else {
            $artefacttypefilter = ' AND FALSE';
        }

        if (!is_null($tag)) {
            $artefacttypefilter .= ' AND at.tag = ?';
            $viewfilter .= ' AND vt.tag = ?';
            $values = array($owner->id, $tag, $owner->id, $tag);
        }
        else {
            $values = array($owner->id, $owner->id);
        }

        $from = "FROM (
           (SELECT a.id, a.title, a.description, 'artefact' AS type, a.artefacttype, " . db_format_tsfield('a.ctime', 'ctime') . "
            FROM {artefact} a JOIN {artefact_tag} at ON (a.id = at.artefact)
            WHERE a.owner = ?" . $artefacttypefilter . ")
           UNION
           (SELECT v.id, v.title, v.description, 'view' AS type, NULL AS artefacttype, " . db_format_tsfield('v.ctime', 'ctime') . "
            FROM {view} v JOIN {view_tag} vt ON (v.id = vt.view)
            WHERE v.owner = ? " . $viewfilter . ")
        ) p";

        $result = (object) array(
            'tag'    => $tag,
            'owner'  => $owner,
            'offset' => $offset,
            'limit'  => $limit,
            'sort'   => $sort,
            'count'  => 0,
            'data'   => array(),
        );

        if ($count = count_records_sql('SELECT COUNT(*) ' . $from, $values, $offset, $limit)) {
            $result->count = $count;
            $sort = $sort == 'date' ? 'ctime DESC' : 'title ASC';
            if ($data = get_records_sql_assoc("SELECT type || ':' || id AS tid, p.* " . $from . ' ORDER BY ' . $sort, $values, $offset, $limit)) {
                if ($returntags) {
                    $ids = array('view' => array(), 'artefact' => array());
                    foreach ($data as &$d) {
                        $ids[$d->type][$d->id] = 1;
                    }
                    if (!empty($ids['view'])) {
                        if ($viewtags = get_records_select_array('view_tag', 'view IN (' . join(',', array_keys($ids['view'])) . ')')) {
                            foreach ($viewtags as &$vt) {
                                $data['view:' . $vt->view]->tags[] = $vt->tag;
                            }
                        }
                    }
                    if (!empty($ids['artefact'])) {
                        if ($artefacttags = get_records_select_array('artefact_tag', 'artefact IN (' . join(',', array_keys($ids['artefact'])) . ')')) {
                            foreach ($artefacttags as &$at) {
                                $data['artefact:' . $at->artefact]->tags[] = $at->tag;
                            }
                        }
                    }
                }
                $result->data = $data;
            }
        }
        return $result;
    }


    /**
     * Parses a query string into SQL fragments for searching. Supports 
     * phrases, AND/OR etc.
     *
     * Lifted from drupal 5.1, (C) 2007 Drupal
     *
     * This function comes from Drupal's search module, with some small changes.
     */
    private static function search_parse_query($text) {
        $keys = array('positive' => array(), 'negative' => array());

        // Tokenize query string
        preg_match_all('/ (-?)("[^"]+"|[^" ]+)/i', ' '. $text, $matches, PREG_SET_ORDER);

        if (count($matches) < 1) {
          return NULL;
        }

        // Classify tokens
        $or = FALSE;
        foreach ($matches as $match) {
          $phrase = FALSE;
          // Strip off phrase quotes
          if ($match[2]{0} == '"') {
            $match[2] = substr($match[2], 1, -1);
            $phrase = TRUE;
          }
          // Simplify keyword according to indexing rules and external preprocessors
          $words = self::search_simplify($match[2]);
          // Re-explode in case simplification added more words, except when matching a phrase
          $words = $phrase ? array($words) : preg_split('/ /', $words, -1, PREG_SPLIT_NO_EMPTY);
          // Negative matches
          if ($match[1] == '-') {
            $keys['negative'] = array_merge($keys['negative'], $words);
          }
          // OR operator: instead of a single keyword, we store an array of all
          // OR'd keywords.
          elseif ($match[2] == 'OR' && count($keys['positive'])) {
            $last = array_pop($keys['positive']);
            // Starting a new OR?
            if (!is_array($last)) {
              $last = array($last);
            }
            $keys['positive'][] = $last;
            $or = TRUE;
            continue;
          }
          // Plain keyword
          else {
            if ($or) {
              // Add to last element (which is an array)
              $keys['positive'][count($keys['positive']) - 1] = array_merge($keys['positive'][count($keys['positive']) - 1], $words);
            }
            else {
              $keys['positive'] = array_merge($keys['positive'], $words);
            }
          }
          $or = FALSE;
        }

        // Convert keywords into SQL statements.
        $query = array();
        //$query2 = array();
        $arguments = array();
        $arguments2 = array();
        //$matches = 0; 
        // Positive matches
        foreach ($keys['positive'] as $key) {
          // Group of ORed terms
          if (is_array($key) && count($key)) {
            $queryor = array();
            $any = FALSE;
            foreach ($key as $or) {
              list($q, $count) = self::_search_parse_query($or, $arguments2);
              $any |= $count;
              if ($q) {
                $queryor[] = $q;
                $arguments[] = $or;
                $arguments[] = $or;
              }
            }

            if (count($queryor)) {
              $query[] = '('. implode(' OR ', $queryor) .')';
              // A group of OR keywords only needs to match once
              //$matches += ($any > 0);
            }
          }
          // Single ANDed term
          else {
            list($q, $count) = self::_search_parse_query($key, $arguments2);
            if ($q) {
              $query[] = $q;
              $arguments[] = $key;
              $arguments[] = $key;
              // Each AND keyword needs to match at least once
              //$matches += $count;
            }
          }
        }
        // Negative matches
        foreach ($keys['negative'] as $key) {
          list($q) = self::_search_parse_query($key, $arguments2, TRUE);
          if ($q) {
            $query[] = $q;
            $arguments[] = $key;
            $arguments[] = $key;
          }
        }
        $query = implode(' AND ', $query);

        // Build word-index conditions for the first pass
        //$query2 = substr(str_repeat("i.word = '%s' OR ", count($arguments2)), 0, -4);

        return array($query, $arguments, /*$query2, $arguments2, $matches*/);
    }

    /**
     * Helper function for search_parse_query();
     */
    private static function _search_parse_query(&$word, &$scores, $not = FALSE) {
      $count = 0;
      // Determine the scorewords of this word/phrase
      if (!$not) {
        $split = explode(' ', $word);
        foreach ($split as $s) {
          $num = is_numeric($s);
          if ($num || strlen($s) >= /*variable_get('minimum_word_size', 3)*/3) {
            $s = $num ? ((int)ltrim($s, '-0')) : $s;
            if (!isset($scores[$s])) {
              $scores[$s] = $s;
              $count++;
            }
          }
        }
      }
      // Return matching snippet and number of added words
      return array("a.title ". ($not ? 'NOT ' : '') ."LIKE '%' || ? || '%'" . ($not ? ' AND ' : ' OR ') . 'a.description ' . ($not ? 'NOT ' : '') . "LIKE '%' || ? || '%'", $count);
    }

    /**
     * Simplifies a string according to indexing rules.
     */
    private static function search_simplify($text) {
      // Decode entities to UTF-8
      $text = self::decode_entities($text);
    
      // Lowercase
      $text = strtolower($text);
    
      // Call an external processor for word handling.
      //search_preprocess($text);
    
      // Simple CJK handling
      //if (variable_get('overlap_cjk', TRUE)) {
      //  $text = preg_replace_callback('/['. PREG_CLASS_CJK .']+/u', 'search_expand_cjk', $text);
      //}
    
      // To improve searching for numerical data such as dates, IP addresses
      // or version numbers, we consider a group of numerical characters
      // separated only by punctuation characters to be one piece.
      // This also means that searching for e.g. '20/03/1984' also returns
      // results with '20-03-1984' in them.
      // Readable regexp: ([number]+)[punctuation]+(?=[number])
      $text = preg_replace('/(['. PREG_CLASS_NUMBERS .']+)['. PREG_CLASS_PUNCTUATION .']+(?=['. PREG_CLASS_NUMBERS .'])/u', '\1', $text);
    
      // The dot, underscore and dash are simply removed. This allows meaningful
      // search behaviour with acronyms and URLs.
      $text = preg_replace('/[._-]+/', '', $text);
    
      // With the exception of the rules above, we consider all punctuation,
      // marks, spacers, etc, to be a word boundary.
      $text = preg_replace('/['. PREG_CLASS_SEARCH_EXCLUDE . ']+/u', ' ', $text);
    
      return $text;
    }

    /**
     * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes.
     * Double-escaped entities will only be decoded once ("&amp;lt;" becomes "&lt;", not "<").
     *
     * @param $text
     *   The text to decode entities in.
     * @param $exclude 
     *   An array of characters which should not be decoded. For example,
     *   array('<', '&', '"'). This affects both named and numerical entities.
     */
    function decode_entities($text, $exclude = array()) {
      static $table;
      // We store named entities in a table for quick processing.
      if (!isset($table)) { 
        // Get all named HTML entities. 
        $table = array_flip(get_html_translation_table(HTML_ENTITIES));
        // PHP gives us ISO-8859-1 data, we need UTF-8.
        $table = array_map('utf8_encode', $table);
        // Add apostrophe (XML)
        $table['&apos;'] = "'";
      }
      $newtable = array_diff($table, $exclude);

      // Use a regexp to select all entities in one pass, to avoid decoding double-escaped entities twice.
      return preg_replace('/&(#x?)?([A-Za-z0-9]+);/e', '_decode_entities("$1", "$2", "$0", $newtable, $exclude)', $text);
    }

    /**
     * Helper function for decode_entities
     */
    function _decode_entities($prefix, $codepoint, $original, &$table, &$exclude) {
      // Named entity
      if (!$prefix) {
        if (isset($table[$original])) {
          return $table[$original];
        }
        else {
          return $original;
        } 
      }   
      // Hexadecimal numerical entity
      if ($prefix == '#x') {
        $codepoint = base_convert($codepoint, 16, 10);
      } 
      // Decimal numerical entity (strip leading zeros to avoid PHP octal notation)
      else {
        $codepoint = preg_replace('/^0+/', '', $codepoint);
      }
      // Encode codepoint as UTF-8 bytes
      if ($codepoint < 0x80) {
        $str = chr($codepoint);
      }
      else if ($codepoint < 0x800) {
        $str = chr(0xC0 | ($codepoint >> 6))
             . chr(0x80 | ($codepoint & 0x3F));
      }
      else if ($codepoint < 0x10000) {
        $str = chr(0xE0 | ( $codepoint >> 12))
             . chr(0x80 | (($codepoint >> 6) & 0x3F))
             . chr(0x80 | ( $codepoint       & 0x3F));
      }
      else if ($codepoint < 0x200000) {
        $str = chr(0xF0 | ( $codepoint >> 18))
             . chr(0x80 | (($codepoint >> 12) & 0x3F))
             . chr(0x80 | (($codepoint >> 6)  & 0x3F))
             . chr(0x80 | ( $codepoint        & 0x3F));
      }
      // Check for excluded characters
      if (in_array($str, $exclude)) {
        return $original;
      }
      else { 
        return $str;
      }
    }

}

/**
 * Matches Unicode character classes to exclude from the search index.
 *
 * See: http://www.unicode.org/Public/UNIDATA/UCD.html#General_Category_Values
 *
 * The index only contains the following character classes:
 * Lu     Letter, Uppercase
 * Ll     Letter, Lowercase
 * Lt     Letter, Titlecase
 * Lo     Letter, Other
 * Nd     Number, Decimal Digit
 * No     Number, Other
 */
define('PREG_CLASS_SEARCH_EXCLUDE',
'\x{0}-\x{2f}\x{3a}-\x{40}\x{5b}-\x{60}\x{7b}-\x{bf}\x{d7}\x{f7}\x{2b0}-'.
'\x{385}\x{387}\x{3f6}\x{482}-\x{489}\x{559}-\x{55f}\x{589}-\x{5c7}\x{5f3}-'.
'\x{61f}\x{640}\x{64b}-\x{65e}\x{66a}-\x{66d}\x{670}\x{6d4}\x{6d6}-\x{6ed}'.
'\x{6fd}\x{6fe}\x{700}-\x{70f}\x{711}\x{730}-\x{74a}\x{7a6}-\x{7b0}\x{901}-'.
'\x{903}\x{93c}\x{93e}-\x{94d}\x{951}-\x{954}\x{962}-\x{965}\x{970}\x{981}-'.
'\x{983}\x{9bc}\x{9be}-\x{9cd}\x{9d7}\x{9e2}\x{9e3}\x{9f2}-\x{a03}\x{a3c}-'.
'\x{a4d}\x{a70}\x{a71}\x{a81}-\x{a83}\x{abc}\x{abe}-\x{acd}\x{ae2}\x{ae3}'.
'\x{af1}-\x{b03}\x{b3c}\x{b3e}-\x{b57}\x{b70}\x{b82}\x{bbe}-\x{bd7}\x{bf0}-'.
'\x{c03}\x{c3e}-\x{c56}\x{c82}\x{c83}\x{cbc}\x{cbe}-\x{cd6}\x{d02}\x{d03}'.
'\x{d3e}-\x{d57}\x{d82}\x{d83}\x{dca}-\x{df4}\x{e31}\x{e34}-\x{e3f}\x{e46}-'.
'\x{e4f}\x{e5a}\x{e5b}\x{eb1}\x{eb4}-\x{ebc}\x{ec6}-\x{ecd}\x{f01}-\x{f1f}'.
'\x{f2a}-\x{f3f}\x{f71}-\x{f87}\x{f90}-\x{fd1}\x{102c}-\x{1039}\x{104a}-'.
'\x{104f}\x{1056}-\x{1059}\x{10fb}\x{10fc}\x{135f}-\x{137c}\x{1390}-\x{1399}'.
'\x{166d}\x{166e}\x{1680}\x{169b}\x{169c}\x{16eb}-\x{16f0}\x{1712}-\x{1714}'.
'\x{1732}-\x{1736}\x{1752}\x{1753}\x{1772}\x{1773}\x{17b4}-\x{17db}\x{17dd}'.
'\x{17f0}-\x{180e}\x{1843}\x{18a9}\x{1920}-\x{1945}\x{19b0}-\x{19c0}\x{19c8}'.
'\x{19c9}\x{19de}-\x{19ff}\x{1a17}-\x{1a1f}\x{1d2c}-\x{1d61}\x{1d78}\x{1d9b}-'.
'\x{1dc3}\x{1fbd}\x{1fbf}-\x{1fc1}\x{1fcd}-\x{1fcf}\x{1fdd}-\x{1fdf}\x{1fed}-'.
'\x{1fef}\x{1ffd}-\x{2070}\x{2074}-\x{207e}\x{2080}-\x{2101}\x{2103}-\x{2106}'.
'\x{2108}\x{2109}\x{2114}\x{2116}-\x{2118}\x{211e}-\x{2123}\x{2125}\x{2127}'.
'\x{2129}\x{212e}\x{2132}\x{213a}\x{213b}\x{2140}-\x{2144}\x{214a}-\x{2b13}'.
'\x{2ce5}-\x{2cff}\x{2d6f}\x{2e00}-\x{3005}\x{3007}-\x{303b}\x{303d}-\x{303f}'.
'\x{3099}-\x{309e}\x{30a0}\x{30fb}-\x{30fe}\x{3190}-\x{319f}\x{31c0}-\x{31cf}'.
'\x{3200}-\x{33ff}\x{4dc0}-\x{4dff}\x{a015}\x{a490}-\x{a716}\x{a802}\x{a806}'.
'\x{a80b}\x{a823}-\x{a82b}\x{d800}-\x{f8ff}\x{fb1e}\x{fb29}\x{fd3e}\x{fd3f}'.
'\x{fdfc}-\x{fe6b}\x{feff}-\x{ff0f}\x{ff1a}-\x{ff20}\x{ff3b}-\x{ff40}\x{ff5b}-'.
'\x{ff65}\x{ff70}\x{ff9e}\x{ff9f}\x{ffe0}-\x{fffd}');

/**
 * Matches all 'N' Unicode character classes (numbers)
 */
define('PREG_CLASS_NUMBERS',
'\x{30}-\x{39}\x{b2}\x{b3}\x{b9}\x{bc}-\x{be}\x{660}-\x{669}\x{6f0}-\x{6f9}'.
'\x{966}-\x{96f}\x{9e6}-\x{9ef}\x{9f4}-\x{9f9}\x{a66}-\x{a6f}\x{ae6}-\x{aef}'.
'\x{b66}-\x{b6f}\x{be7}-\x{bf2}\x{c66}-\x{c6f}\x{ce6}-\x{cef}\x{d66}-\x{d6f}'.
'\x{e50}-\x{e59}\x{ed0}-\x{ed9}\x{f20}-\x{f33}\x{1040}-\x{1049}\x{1369}-'.
'\x{137c}\x{16ee}-\x{16f0}\x{17e0}-\x{17e9}\x{17f0}-\x{17f9}\x{1810}-\x{1819}'.
'\x{1946}-\x{194f}\x{2070}\x{2074}-\x{2079}\x{2080}-\x{2089}\x{2153}-\x{2183}'.
'\x{2460}-\x{249b}\x{24ea}-\x{24ff}\x{2776}-\x{2793}\x{3007}\x{3021}-\x{3029}'.
'\x{3038}-\x{303a}\x{3192}-\x{3195}\x{3220}-\x{3229}\x{3251}-\x{325f}\x{3280}-'.
'\x{3289}\x{32b1}-\x{32bf}\x{ff10}-\x{ff19}');

/**
 * Matches all 'P' Unicode character classes (punctuation)
 */
define('PREG_CLASS_PUNCTUATION',
'\x{21}-\x{23}\x{25}-\x{2a}\x{2c}-\x{2f}\x{3a}\x{3b}\x{3f}\x{40}\x{5b}-\x{5d}'.
'\x{5f}\x{7b}\x{7d}\x{a1}\x{ab}\x{b7}\x{bb}\x{bf}\x{37e}\x{387}\x{55a}-\x{55f}'.
'\x{589}\x{58a}\x{5be}\x{5c0}\x{5c3}\x{5f3}\x{5f4}\x{60c}\x{60d}\x{61b}\x{61f}'.
'\x{66a}-\x{66d}\x{6d4}\x{700}-\x{70d}\x{964}\x{965}\x{970}\x{df4}\x{e4f}'.
'\x{e5a}\x{e5b}\x{f04}-\x{f12}\x{f3a}-\x{f3d}\x{f85}\x{104a}-\x{104f}\x{10fb}'.
'\x{1361}-\x{1368}\x{166d}\x{166e}\x{169b}\x{169c}\x{16eb}-\x{16ed}\x{1735}'.
'\x{1736}\x{17d4}-\x{17d6}\x{17d8}-\x{17da}\x{1800}-\x{180a}\x{1944}\x{1945}'.
'\x{2010}-\x{2027}\x{2030}-\x{2043}\x{2045}-\x{2051}\x{2053}\x{2054}\x{2057}'.
'\x{207d}\x{207e}\x{208d}\x{208e}\x{2329}\x{232a}\x{23b4}-\x{23b6}\x{2768}-'.
'\x{2775}\x{27e6}-\x{27eb}\x{2983}-\x{2998}\x{29d8}-\x{29db}\x{29fc}\x{29fd}'.
'\x{3001}-\x{3003}\x{3008}-\x{3011}\x{3014}-\x{301f}\x{3030}\x{303d}\x{30a0}'.
'\x{30fb}\x{fd3e}\x{fd3f}\x{fe30}-\x{fe52}\x{fe54}-\x{fe61}\x{fe63}\x{fe68}'.
'\x{fe6a}\x{fe6b}\x{ff01}-\x{ff03}\x{ff05}-\x{ff0a}\x{ff0c}-\x{ff0f}\x{ff1a}'.
'\x{ff1b}\x{ff1f}\x{ff20}\x{ff3b}-\x{ff3d}\x{ff3f}\x{ff5b}\x{ff5d}\x{ff5f}-'.
'\x{ff65}');

/**
 * Matches all CJK characters that are candidates for auto-splitting
 * (Chinese, Japanese, Korean).
 * Contains kana and BMP ideographs.
 */
define('PREG_CLASS_CJK', '\x{3041}-\x{30ff}\x{31f0}-\x{31ff}\x{3400}-\x{4db5}'.
'\x{4e00}-\x{9fbb}\x{f900}-\x{fad9}');
