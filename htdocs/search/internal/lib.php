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
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @return array  A data structure containing results looking like ...
     *         $results = array(
     *               count   => integer, // total number of results
     *               limit   => integer, // how many results are returned
     *               offset  => integer, // starting from which result
     *               results => array(   // the result records
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
        if ( is_postgres() ) {
            $data = get_rows_sql("
                SELECT
                    id, username, institution, firstname, lastname, preferredname, email
                FROM
                    " . get_config('dbprefix') . "usr u
                WHERE
                    firstname ILIKE '%' || ? || '%' 
                    OR lastname ILIKE '%' || ? || '%' 
                    OR preferredname ILIKE '%' || ? || '%' 
                    OR email ILIKE '%' || ? || '%' 
                ",
                array($query_string, $query_string, $query_string, $query_string),
                $offset,
                $limit
            );

            $count = get_field_sql("
                SELECT
                    COUNT(*)
                FROM
                    " . get_config('dbprefix') . "usr u
                WHERE
                    firstname ILIKE '%' || ? || '%' 
                    OR lastname ILIKE '%' || ? || '%' 
                    OR preferredname ILIKE '%' || ? || '%' 
                    OR email ILIKE '%' || ? || '%' 
            ",
                array($query_string, $query_string, $query_string, $query_string),
                $offset,
                $limit
            );
        }
        // TODO
        // else if ( is_mysql() ) {
        // }
        else {
            throw new DatalibException('search_user() is not implemented for your database engine (' . get_config('dbtype') . ')');
        }

        return array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'results' => $data,
        );
    }
}

?>
