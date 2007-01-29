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
        $prefix = get_config('dbprefix');
        if ( is_postgres() ) {
            if (!empty($publicfields)) {
                $fieldclauses = array();
                $values = array();
                foreach ($publicfields as $fieldname) {
                    array_push($fieldclauses, "(a.artefacttype = ? AND (a.title ILIKE '%' || ? || '%'))");
                    array_push($values, $fieldname);
                    array_push($values, $query_string);
                }
                $querysql = join(' OR ', $fieldclauses);
                $data = get_records_sql_array('
                    SELECT DISTINCT
                        u.id, u.username, u.institution, u.firstname, 
                        u.lastname, u.preferredname, u.email
                    FROM
                        ' . $prefix . 'usr u 
                        INNER JOIN ' . $prefix . 'artefact a ON a.owner = u.id
                    WHERE
                        u.id <> 0
                        AND ( ' . $querysql . ')',
                    $values,
                    $offset,
                    $limit
                );

                $count = get_field_sql('
                    SELECT 
                        COUNT(DISTINCT u.id)
                    FROM
                        ' . $prefix . 'usr u 
                        INNER JOIN ' . $prefix . 'artefact a ON a.owner = u.id
                    WHERE
                        u.id <> 0
                        AND ( ' . $querysql . ')',
                    $values
                );
            }
            else {
                $data = false;
                $count = 0;
            }
            log_debug($data);
            log_debug($count);
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
