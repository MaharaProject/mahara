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
            return search_user_pg($query_string, $limit, $offset, $prefix, $publicfields);
        } else if ( is_mysql() ) {
            return search_user_my($query_string, $limit, $offset, $prefix, $publicfields);
        } else {
            throw new SQLException('search_user() is not implemented for your database engine (' . get_config('dbtype') . ')');
        }
    }

    public static function search_user_pg($query_string, $limit, $offset, $prefix, $publicfields) {
        $fieldlist = "('" . join("','", $publicfields) . "')";

        $count = get_field_sql('
            SELECT 
                COUNT(DISTINCT u.id)
            FROM
                ' . $prefix . 'usr u
                LEFT JOIN ' . $prefix . 'artefact a ON u.id=a.owner
            WHERE
                u.id <> 0 AND u.active = 1
                AND ((
                        u.preferredname IS NULL
                        AND (
                            u.firstname ILIKE \'%\' || ? || \'%\'
                            OR u.lastname ILIKE \'%\' || ? || \'%\'
                        )
                    )
                    OR (
                        a.artefacttype IN ' . $fieldlist . '
                        AND ( a.title ILIKE \'%\' || ? || \'%\')
                    )
                )
            ',
            array($query_string, $query_string, $query_string)
        );

        if ($count > 0) {
            $data = get_records_sql_array('
                SELECT DISTINCT ON (u.firstname, u.lastname, u.id)
                    u.id, u.username, u.institution, u.firstname, u.lastname, u.preferredname, u.email, u.staff
                FROM ' . $prefix . 'artefact a
                    INNER JOIN ' . $prefix .'usr u ON u.id = a.owner
                WHERE
                    u.id <> 0 AND u.active = 1
                    AND ((
                            u.preferredname IS NULL
                            AND (
                                u.firstname ILIKE \'%\' || ? || \'%\'
                                OR u.lastname ILIKE \'%\' || ? || \'%\'
                            )
                        )
                        OR (
                            a.artefacttype IN ' . $fieldlist . '
                            AND ( a.title ILIKE \'%\' || ? || \'%\')
                        )
                    )
                ORDER BY u.firstname, u.lastname, u.id',
            array($query_string, $query_string, $query_string),
            $offset,
            $limit);

            if ($data) {
                foreach ($data as &$item) {
                    $item = (array)$item;
                }
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

    public static function search_user_my($query_string, $limit, $offset, $prefix, $publicfields) {
        $fieldlist = "('" . join("','", $publicfields) . "')";

        $count = get_field_sql('
            SELECT 
                COUNT(DISTINCT u.id)
            FROM
                ' . $prefix . 'usr u
                LEFT JOIN ' . $prefix . 'artefact a ON u.id=a.owner
            WHERE
                u.id <> 0 AND u.active = 1
                AND ((
                        u.preferredname IS NULL
                        AND (
                            u.firstname LIKE \'%\' || ? || \'%\'
                            OR u.lastname LIKE \'%\' || ? || \'%\'
                        )
                    )
                    OR (
                        a.artefacttype IN ' . $fieldlist . '
                        AND ( a.title LIKE \'%\' || ? || \'%\')
                    )
                )
            ',
            array($query_string, $query_string, $query_string)
        );

        if ($count > 0) {
            $data = get_records_sql_array('
                SELECT DISTINCT ON (u.firstname, u.lastname, u.id)
                    u.id, u.username, u.institution, u.firstname, u.lastname, u.preferredname, u.email, u.staff
                FROM ' . $prefix . 'artefact a
                    INNER JOIN ' . $prefix .'usr u ON u.id = a.owner
                WHERE
                    u.id <> 0 AND u.active = 1
                    AND ((
                            u.preferredname IS NULL
                            AND (
                                u.firstname LIKE \'%\' || ? || \'%\'
                                OR u.lastname LIKE \'%\' || ? || \'%\'
                            )
                        )
                        OR (
                            a.artefacttype IN ' . $fieldlist . '
                            AND ( a.title LIKE \'%\' || ? || \'%\')
                        )
                    )
                ORDER BY u.firstname, u.lastname, u.id',
            array($query_string, $query_string, $query_string),
            $offset,
            $limit);

            if ($data) {
                foreach ($data as &$item) {
                    $item = (array)$item;
                }
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
        if ( is_postgres() ) {
            return search_group_pg($query_string, $limit, $offset);
        } else {
            throw new SQLException('search_group() is not implemented for your database engine (' . get_config('dbtype') . ')');
        }
    }

    public static function search_group_pg($query_string, $limit, $offset) {
        global $USER;
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
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Type to search for (either 'all' or one of the types above).
     * 
     */
    public static function self_search($query_string, $limit, $offset, $type = 'all') {
        throw new Exception('TODO: implement me!');
    }
}

?>
