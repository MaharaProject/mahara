<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage search-internal
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * The internal search plugin which searches against the
 * Mahara database.
 */
class PluginSearchInternal extends PluginSearch {

    /**
     * Implement user searching with SQL
     *
     * NOTE: user with ID zero should never be returned
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
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
    public static function search_user($query_string, $limit, $offset = 0) {
        safe_require('artefact', 'internal');
        $publicfields = array_keys(ArtefactTypeProfile::get_public_fields());
        if (empty($publicfields)) {
            $publicfields = array('preferredname');
        }
        $prefix = get_config('dbprefix');
        if ( is_postgres() ) {
            $fieldlist = "('" . join("','", $publicfields) . "')";

            $count = get_field_sql('
                SELECT 
                    COUNT(DISTINCT owner)
                FROM
                    ' . $prefix . 'artefact
                WHERE
                    owner <> 0
                    AND artefacttype IN ' . $fieldlist . "
                    AND ( title ILIKE '%' || ? || '%')",
                array($query_string));

            if ($count > 0) {

                $users = get_records_sql_assoc('
                    SELECT DISTINCT ON (u.firstname, u.lastname, u.id)
                        u.id, u.username, u.institution, u.firstname, u.lastname, u.preferredname
                    FROM ' . $prefix . 'artefact a
                        INNER JOIN ' . $prefix .'usr u ON u.id = a.owner
                    WHERE
                        u.id <> 0
                        AND a.artefacttype IN ' . $fieldlist . "
                        AND (a.title ILIKE '%' || ? || '%')
                    ORDER BY u.firstname, u.lastname, u.id",
                array($query_string),
                $offset,
                $limit);

                $userlist = '('.join(',', array_map(create_function('$u','return $u->id;'), $users)).')';

                $data = get_records_sql_array('
                    SELECT 
                        u.id, a.artefacttype, a.title
                    FROM
                        ' . $prefix . 'artefact a
                        INNER JOIN ' . $prefix . 'usr u ON u.id = a.owner
                    WHERE
                        a.artefacttype IN ' . $fieldlist . '
                        AND u.id IN ' . $userlist . '
                    ORDER BY u.firstname, u.lastname, u.id, a.artefacttype',
                    array());

                if (!empty($data)) {
                    foreach ($users as &$user) {
                        $user->name = display_name($user);
                        unset($user->username);
                        unset($user->institution);
                        unset($user->firstname);
                        unset($user->lastname);
                        unset($user->preferredname);
                    }
                    foreach ($data as $rec) {
                        if ($rec->artefacttype == 'email') {
                            $users[$rec->id]->email[] = $rec->title;
                        }
                        else {
                            $users[$rec->id]->{$rec->artefacttype} = $rec->title;
                        }
                    }
                    $data = array_values($users);
                }
            }
            else {
                $data = false;
            }
        }
        // TODO
        // else if ( is_mysql() ) {
        // }
        else {
            throw new SQLException('search_user() is not implemented for your database engine (' . get_config('dbtype') . ')');
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $data,
        );
    }
    
    /**
     * Implement group searching with SQL
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @return array  A data structure containing results looking like ...
     *         $results = array(
     *               count   => integer, // total number of results
     *               limit   => integer, // how many results are returned
     *               offset  => integer, // starting from which result
     *               data    => array(   // the result records
     *                   array(
     *                       id          => integer,
     *                       name        => string,
     *                       owner       => integer,
     *                       description => string,
     *                       ctime       => string,
     *                       mtime       => string,
     *                   ),
     *                   array(
     *                       id          => integer,
     *                       name        => string,
     *                       owner       => integer,
     *                       description => string,
     *                       ctime       => string,
     *                       mtime       => string,
     *                   ),
     *                   array(...),
     *               ),
     *           );
     */
    public static function search_group($query_string, $limit, $offset = 0) {
        global $USER;
        if ( is_postgres() ) {
            $data = get_records_sql_array("
                SELECT
                    id, name, owner, description, ctime, mtime
                FROM
                    " . get_config('dbprefix') . "usr_group u
                WHERE
                    owner = ?
                    AND (
                        name ILIKE '%' || ? || '%' 
                        OR description ILIKE '%' || ? || '%' 
                    )
                ",
                array($USER->get('id'), $query_string, $query_string),
                $offset,
                $limit
            );

            $count = get_field_sql("
                SELECT
                    COUNT(*)
                FROM
                    " . get_config('dbprefix') . "usr_group u
                WHERE
                    owner = ?
                    AND (
                        name ILIKE '%' || ? || '%' 
                        OR description ILIKE '%' || ? || '%' 
                    )
            ",
                array($USER->get('id'), $query_string, $query_string)
            );
        }
        // TODO
        // else if ( is_mysql() ) {
        // }
        else {
            throw new SQLException('search_group() is not implemented for your database engine (' . get_config('dbtype') . ')');
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $data,
        );
    }
    
    /**
     * Implement community searching with SQL
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
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
    public static function search_community($query_string, $limit, $offset=0, $all=false) {
        global $USER;
        if ( is_postgres() ) {
            $sql = "
                SELECT
                    id, name, description, jointype, owner, ctime, mtime
                FROM
                    " . get_config('dbprefix') . "community
                WHERE (
                    name ILIKE '%' || ? || '%' 
                    OR description ILIKE '%' || ? || '%' 
                )";
            $values = array($query_string, $query_string);
            if (!$all) {
                $sql .=  "AND ( 
                    owner = ? OR id IN (
                        SELECT community FROM " . get_config('dbprefix') . "community_member WHERE member = ?
                    )
                )";
                $values[] = $USER->get('id');
                $values[] = $USER->get('id');
            }
            $data = get_records_sql_array($sql, $values, $offset, $limit);

            $sql = "
                SELECT
                    COUNT(*)
                FROM
                    " . get_config('dbprefix') . "community u
                WHERE (
                    name ILIKE '%' || ? || '%' 
                    OR description ILIKE '%' || ? || '%' 
                )";
            if (!$all) {
                $sql .= "AND ( 
                        owner = ? OR id IN (
                            SELECT community FROM " . get_config('dbprefix') . "community_member WHERE member = ?
                        )
                    )
                ";
            }
            $count = get_field_sql($sql, $values);
        }
        // TODO
        // else if ( is_mysql() ) {
        // }
        else {
            throw new SQLException('search_community() is not implemented for your database engine (' . get_config('dbtype') . ')');
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $data,
        );
    }
}

?>
