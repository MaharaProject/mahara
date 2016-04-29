<?php

/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 */

/**
 * OAuth v1 Data Store
 *
 * @package   webservice
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Piers Harding
 */

require_once(dirname(__FILE__) . '/OAuthStoreAbstract.class.php');

class OAuthStoreMahara extends OAuthStoreAbstract {

    private $session;
    /**
     * Maximum delta a timestamp may be off from a previous timestamp.
     * Allows multiple consumers with some clock skew to work with the same token.
     * Unit is seconds, default max skew is 10 minutes.
     */
    protected $max_timestamp_skew = 600;

    /**
     * Default ttl for request tokens
     */
    protected $max_request_token_ttl = 3600;

	/*
	 * Takes two options: consumer_key and consumer_secret
	 */
	public function __construct( $options = array() ) {
	}
	public function getSecretsForSignature ( $uri, $user_id ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function getServerTokenSecrets ( $consumer_key, $token, $token_type, $user_id, $name = '' ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function addServerToken ( $consumer_key, $token_type, $token, $token_secret, $user_id, $options = array() ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function getServer( $consumer_key, $user_id, $user_is_admin = false ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function getServerForUri ( $uri, $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function listServerTokens ( $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function countServerTokens ( $consumer_key ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function getServerToken ( $consumer_key, $token, $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function deleteServerToken ( $consumer_key, $token, $userid, $user_is_admin = false ) {
		// TODO
	}
	public function listServers ( $q = '', $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function updateServer ( $server, $userid, $user_is_admin = false ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function deleteConsumer ( $consumer_key, $userid, $user_is_admin = false ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function getConsumerStatic () { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function countConsumerAccessTokens ( $consumer_key ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function getConsumerAccessToken ( $token, $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function setConsumerAccessTokenTtl ( $token, $ttl ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function listConsumerApplications( $begin = 0, $total = 25 )  { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function listConsumerTokens ( $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function addLog ( $keys, $received, $sent, $base_string, $notes, $userid = null ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function listLog ( $options, $userid ) { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }
	public function install () { throw new OAuthException2("OAuthStoreMahara doesn't support " . __METHOD__); }

    /**
     * Find stored credentials for the consumer key and token. Used by an OAuth server
     * when verifying an OAuth request.
     *
     * @param string consumer_key
     * @param string token
     * @param string token_type     false, 'request' or 'access'
     * @exception OAuthException2 when no secrets where found
     * @return array    assoc (consumer_secret, token_secret, osr_id, ost_id, userid)
     */
    public function getSecretsForVerify ($consumer_key, $token, $token_type = 'access') {
        if ($token_type === false) {
            $rs = get_records_sql_assoc('
                        SELECT  id,
                                userid              as user_id,
                                userid              as service_user,
                                externalserviceid   as externalserviceid,
                                institution         as institution,
                                consumer_key        as consumer_key,
                                consumer_secret     as consumer_secret
                        FROM {oauth_server_registry}
                        WHERE consumer_key  = ?
                          AND enabled       = ?
                        ',
                        array($consumer_key, 1));

            if (!empty($rs)) {
                $rs = (array) array_shift($rs);
                $rs['token']        = false;
                $rs['token_secret'] = false;
                // $rs['user_id']      = false;
                $rs['osr_id']       = false;
            }
        }
        else {
            $rs = get_records_sql_assoc('
                        SELECT  osr.id                as osr_id,
                                osr_id_ref,
                                ost.userid            as user_id,
                                osr.userid            as service_user,
                                osr.externalserviceid as externalserviceid,
                                osr.institution       as institution,
                                consumer_key          as consumer_key,
                                consumer_secret       as consumer_secret,
                                token                 as token,
                                token_secret          as token_secret
                        FROM {oauth_server_registry} osr
                                JOIN {oauth_server_token} ost
                                ON osr_id_ref = osr.id
                        WHERE token_type    = ?
                          AND consumer_key  = ?
                          AND token         = ?
                          AND enabled       = 1
                          AND token_ttl     >= NOW()
                        ',
                        array($token_type, $consumer_key, $token));
            if (!empty($rs)) {
                $rs = (array) array_shift($rs);
            }
        }

        if (empty($rs)) {
            throw new OAuthException2('The consumer_key "'.$consumer_key.'" token "'.$token.'" combination does not exist or is not enabled.');
        }
        return $rs;
    }

    /**
     * Delete a server key.  This removes access to that site.
     *
     * @param string consumer_key
     * @param int userid   user registering this server
     */
    public function deleteServer($consumer_key, $userid, $user_is_admin = false) {
        if ($user_is_admin) {
            delete_records_sql('
                    DELETE FROM {oauth_server_registry}
                    WHERE consumer_key = ?
                      AND (userid = ? OR userid IS NULL)
                    ', array($consumer_key, $userid));
        }
        else {
            delete_records_sql('
                    DELETE FROM {oauth_server_registry}
                    WHERE consumer_key = ?
                      AND userid   = ?
                    ', array($consumer_key, $userid));
        }
    }

    /**
     * Insert/update a new consumer with this server (we will be the server)
     * When this is a new consumer, then also generate the consumer key and secret.
     * Never updates the consumer key and secret.
     * When the id is set, then the key and secret must correspond to the entry
     * being updated.
     *
     * (This is the registry at the server, registering consumers ;-) )
     *
     * @param array consumer
     * @param int userid   user registering this consumer
     * @param boolean user_is_admin
     * @return string consumer key
     */
    public function updateConsumer($consumer, $userid, $user_is_admin = false) {
        if (!$user_is_admin) {
            foreach (array('requester_name', 'requester_email') as $f) {
                if (empty($consumer[$f])) {
                    throw new OAuthException2('The field "'.$f.'" must be set and non empty');
                }
            }
        }

        if (!empty($consumer['id'])) {
            if (empty($consumer['consumer_key'])) {
                throw new OAuthException2('The field "consumer_key" must be set and non empty');
            }
            if (!$user_is_admin && empty($consumer['consumer_secret'])) {
                throw new OAuthException2('The field "consumer_secret" must be set and non empty');
            }

            // Check if the current user can update this server definition
            if (!$user_is_admin) {
                $osr_usa_id_ref = get_field_sql('
                                    SELECT userid
                                    FROM {oauth_server_registry}
                                    WHERE id = ?
                                    ', array($consumer['id']));

                if ($osr_usa_id_ref != $userid) {
                    throw new OAuthException2('The user "'.$userid.'" is not allowed to update this consumer');
                }
            }
            else {
                // User is an admin, allow a key owner to be changed or key to be shared
                if (array_key_exists('userid',$consumer)) {
                    if (is_null($consumer['userid'])) {
                        execute_sql('
                            UPDATE {oauth_server_registry}
                            SET userid = NULL, mtime = NOW(),
                            WHERE id = ?
                            ', array($consumer['id']));
                    }
                    else {
                        execute_sql('
                            UPDATE {oauth_server_registry}
                            SET userid = ?, mtime = NOW(),
                            WHERE id = ?
                            ', array($consumer['userid'], $consumer['id']));
                    }
                }
            }

            execute_sql('
                UPDATE {oauth_server_registry}
                SET requester_name      = ?,
                    requester_email     = ?,
                    callback_uri        = ?,
                    application_uri     = ?,
                    application_title   = ?,
                    application_descr   = ?,
                    application_notes   = ?,
                    application_type    = ?,
                    mtime               = NOW(),
                    institution         = ?,
                    externalserviceid   = ?
                WHERE id              = ?
                  AND consumer_key    = ?
                  AND consumer_secret = ?
                ',
                array($consumer['requester_name'],
                $consumer['requester_email'],
                (isset($consumer['callback_uri'])        ? $consumer['callback_uri']              : ''),
                (isset($consumer['application_uri'])     ? $consumer['application_uri']           : ''),
                (isset($consumer['application_title'])   ? $consumer['application_title']         : ''),
                (isset($consumer['application_descr'])   ? $consumer['application_descr']         : ''),
                (isset($consumer['application_notes'])   ? $consumer['application_notes']         : ''),
                (isset($consumer['application_type'])    ? $consumer['application_type']          : ''),
                $consumer['institution'],
                $consumer['externalserviceid'],
                $consumer['id'],
                $consumer['consumer_key'],
                $consumer['consumer_secret'])
                );

            $consumer_key = $consumer['consumer_key'];
        }
        else {
            $consumer_key   = $this->generateKey(true);
            $consumer_secret= $this->generateKey();

            // When the user is an admin, then the user can be forced to something else that the user
            if ($user_is_admin && array_key_exists('userid',$consumer)) {
                if (is_null($consumer['userid'])) {
                    $owner_id = 'NULL';
                }
                else {
                    $owner_id = intval($consumer['userid']);
                }
            }
            else {
                // No admin, take the user id as the owner id.
                $owner_id = intval($userid);
            }

            execute_sql('
                INSERT INTO {oauth_server_registry}
                   (enabled,
                    status,
                    userid,
                    institution,
                    externalserviceid,
                    consumer_key,
                    consumer_secret,
                    requester_name,
                    requester_email,
                    callback_uri,
                    application_uri,
                    application_title,
                    application_descr,
                    application_notes,
                    application_type,
                    mtime,
                    ctime)
                VALUES(?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       ?,
                       NOW(),
                       NOW())
                ',
                array(1,
                'active',
                $owner_id,
                $consumer['institution'],
                $consumer['externalserviceid'],
                $consumer_key,
                $consumer_secret,
                $consumer['requester_name'],
                $consumer['requester_email'],
                (isset($consumer['callback_uri'])        ? $consumer['callback_uri']              : ''),
                (isset($consumer['application_uri'])     ? $consumer['application_uri']           : ''),
                (isset($consumer['application_title'])   ? $consumer['application_title']         : ''),
                (isset($consumer['application_descr'])   ? $consumer['application_descr']         : ''),
                (isset($consumer['application_notes'])   ? $consumer['application_notes']         : ''),
                (isset($consumer['application_type'])    ? $consumer['application_type']          : ''),
                    )
                );
        }
        return $consumer_key;

    }

    /**
     * Fetch a consumer of this server, by consumer_key.
     *
     * @param string consumer_key
     * @param int userid
     * @param boolean user_is_admin (optional)
     * @exception OAuthException2 when consumer not found
     * @return array
     */
    public function getConsumer($consumer_key, $userid, $user_is_admin = false) {
        $consumer = get_records_sql_assoc('
                        SELECT  *
                        FROM {oauth_server_registry}
                        WHERE consumer_key = ?
                        ', array($consumer_key));

        if (empty($consumer)) {
            throw new OAuthException2('No consumer with consumer_key "'.$consumer_key.'"');
        }

        $consumer = (array) array_shift($consumer);

        if (!$user_is_admin && !empty($consumer['userid']) && $consumer['userid'] != $userid) {
            throw new OAuthException2('No access to the consumer information for consumer_key "'.$consumer_key.'"');
        }
        return $consumer;
    }

    /**
     * Add an unautorized request token to our server.
     *
     * @param string consumer_key
     * @param array options     (eg. token_ttl)
     * @return array (token, token_secret)
     */
    public function addConsumerRequestToken($consumer_key, $options = array()) {
        $token  = $this->generateKey(true);
        $secret = $this->generateKey();
        $osr_id = get_field_sql('
                        SELECT id
                        FROM {oauth_server_registry}
                        WHERE consumer_key = ?
                          AND enabled      = ?
                        ', array($consumer_key, 1));

        if (!$osr_id) {
            throw new OAuthException2('No server with consumer_key "'.$consumer_key.'" or consumer_key is disabled');
        }

        $callback = get_field_sql('
                        SELECT callback_uri
                        FROM {oauth_server_registry}
                        WHERE consumer_key = ?
                          AND enabled      = ?
                        ', array($consumer_key, 1));

        if (isset($options['token_ttl']) && is_numeric($options['token_ttl'])) {
            $ttl = intval($options['token_ttl']);
        }
        else {
            $ttl = $this->max_request_token_ttl;
        }

        if (!isset($options['oauth_callback'])) {
            // 1.0a Compatibility : store callback url associated with request token
            if (!empty($callback)) {
                $options['oauth_callback'] = $callback;
            }
            else {
                $options['oauth_callback'] = 'oob';
            }
        }

        $ttl = db_format_timestamp(time() + $ttl);
        $ts = db_format_timestamp(time());
        execute_sql('
                INSERT INTO {oauth_server_token}
                   (osr_id_ref,
                     userid,
                     token,
                     token_secret,
                     token_type,
                     token_ttl,
                     ctime,
                     referrer_host,
                     verifier,
                     callback_uri)
                VALUES
                   (?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?)
                ', array($osr_id, 1, $token, $secret, 'request', $ttl, $ts, getremoteaddr(), 1, $options['oauth_callback']));

        return array('token'=>$token, 'token_secret'=>$secret, 'token_ttl'=>$ttl);
    }

    /**
     * Fetch the consumer request token, by request token.
     *
     * @param string token
     * @return array  token and consumer details
     */
    public function getConsumerRequestToken($token) {
        $rs = get_records_sql_assoc('
                SELECT  token               as token,
                        token_secret        as token_secret,
                        consumer_key        as consumer_key,
                        consumer_secret     as consumer_secret,
                        token_type          as token_type,
                        ost.callback_uri    as callback_url,
                        application_title   as application_title,
                        application_descr   as application_descr,
                        application_uri     as application_uri
                FROM {oauth_server_token} ost
                        JOIN {oauth_server_registry} osr
                        ON osr_id_ref = osr.id
                WHERE token_type = ?
                  AND token      = ?
                  AND token_ttl  >= NOW()
                ', array('request', $token));

        !empty($rs) && $rs = (array) array_shift($rs);
        return $rs;
    }

    /**
     * Delete a consumer token.  The token must be a request or authorized token.
     *
     * @param string token
     */
    public function deleteConsumerRequestToken($token) {
        delete_records_sql('
                    DELETE FROM {oauth_server_token}
                    WHERE  token      = ?
                      AND  token_type = ?
                    ', array($token, 'request'));
    }

    /**
     * Upgrade a request token to be an authorized request token.
     *
     * @param string token
     * @param int    userid  user authorizing the token
     * @param string referrer_host used to set the referrer host for this token, for user feedback
     */
    public function authorizeConsumerRequestToken($token, $userid, $referrer_host = '') {
        // 1.0a Compatibility : create a token verifier
        global $USER;

        $verifier = substr(md5(rand()),0,10);
        execute_sql('
                    UPDATE {oauth_server_token}
                    SET authorized    = ?,
                        userid        = ?,
                        ctime         = NOW(),
                        referrer_host = ?,
                        verifier      = ?
                    WHERE token      = ?
                      AND token_type = ?
                    ', array(1, $userid, $referrer_host, $verifier, $token, 'request'));
        return $verifier;
    }

    /**
     * Exchange an authorized request token for new access token.
     *
     * @param string token
     * @param array options     options for the token, token_ttl
     * @exception OAuthException2 when token could not be exchanged
     * @return array (token, token_secret)
     */
    public function exchangeConsumerRequestForAccessToken($token, $options = array()) {
        $new_token  = $this->generateKey(true);
        $new_secret = $this->generateKey();

        // Maximum time to live for this token
        if (isset($options['token_ttl']) && is_numeric($options['token_ttl'])) {
            $ttl_sql = db_format_timestamp(time() + intval($options['token_ttl']));
        }
        else {
            $ttl_sql = '9999-12-31';
        }

        if (isset($options['verifier'])) {
            $verifier = $options['verifier'];

            // 1.0a Compatibility : check token against oauth_verifier
            $rs = get_records_sql_assoc('SELECT * FROM {oauth_server_token}
                                            WHERE token      = ?
                          AND token_type = ?
                          AND authorized = ?
                          AND token_ttl  >= NOW()
                          AND verifier = ?', array($token, 'request', 1, $verifier));
        }
        else {

            // 1.0
            $rs = get_records_sql_assoc('SELECT * FROM {oauth_server_token}
                                            WHERE token      = ?
                                          AND token_type = ?
                                          AND authorized = ?
                                          AND token_ttl  >= NOW()', array($token, 'request', 1));
        }
        if (empty($rs)) {
            throw new OAuthException2('Can\'t exchange request token "'.$token.'" for access token. No such token or not authorized');
        }
        $db_token = array_shift($rs);
        $db_token->token = $new_token;
        $db_token->token_secret = $new_secret;
        $db_token->token_type = 'access';
        $db_token->token_ttl = $ttl_sql;
        $db_token->ctime = db_format_timestamp(time());
        $result = update_record('oauth_server_token', $db_token);

        if (!$result) {
            throw new OAuthException2('Can\'t exchange request token "'.$token.'" for access token. No such token or not authorized');
        }

        $ret = array('token' => $new_token, 'token_secret' => $new_secret);
        $ttl = get_field_sql('
                    SELECT token_ttl as token_ttl
                    FROM {oauth_server_token}
                    WHERE token_ttl < ? AND
                          token = ?', array('9999-12-31', $new_token));
        if ($ttl) {
            $ret['token_ttl'] = strtotime($ttl) - time();
        }
        return $ret;
    }

    /**
     * Delete a consumer access token.
     *
     * @param string token
     * @param int userid
     * @param boolean user_is_admin
     */
    public function deleteConsumerAccessToken($token, $userid, $user_is_admin = false) {
        if ($user_is_admin) {
            delete_records_sql('
                        DELETE FROM {oauth_server_token}
                        WHERE  token      = ?
                          AND  token_type = ?
                        ', array($token, 'access'));
        }
        else {
            delete_records_sql('
                        DELETE FROM {oauth_server_token}
                        WHERE  token      = ?
                          AND  token_type = ?
                          AND  userid = ?
                        ', array($token, 'access', $userid));
        }
    }

    /**
     * Fetch a list of all consumer keys, secrets etc.
     * Returns the public (userid is null) and the keys owned by the user
     *
     * @param int userid
     * @return array
     */
    public function listConsumers($userid) {
        $rs = get_records_sql_assoc('
                SELECT  osr.id              as id,
                        userid              as userid,
                        institution         as institution,
                        externalserviceid   as externalserviceid,
                        u.username          as username,
                        u.email             as email,
                        consumer_key        as consumer_key,
                        consumer_secret     as consumer_secret,
                        enabled             as enabled,
                        status              as status,
                        osr.ctime           as issue_date,
                        application_uri     as application_uri,
                        application_title   as application_title,
                        application_descr   as application_descr,
                        requester_name      as requester_name,
                        requester_email     as requester_email,
                        callback_uri        as callback_uri
                FROM {oauth_server_registry} osr
                JOIN {usr} u
                ON osr.userid = u.id
                WHERE (userid = ? OR userid IS NULL)
                ORDER BY application_title
                ', array($userid));
        return $rs;
    }

    /**
     * Check an nonce/timestamp combination.  Clears any nonce combinations
     * that are older than the one received.
     *
     * @param string    consumer_key
     * @param string    token
     * @param int       timestamp
     * @param string    nonce
     * @exception OAuthException2   thrown when the timestamp is not in sequence or nonce is not unique
     */
    public function checkServerNonce($consumer_key, $token, $timestamp, $nonce) {
        $high_water = db_format_timestamp($timestamp + $this->max_timestamp_skew);
        $r = get_records_sql_assoc('
                            SELECT MAX(ctime) AS max_stamp, MAX(ctime) > ? AS max_highwater
                            FROM {oauth_server_nonce}
                            WHERE consumer_key = ?
                              AND token        = ?
                            ', array($high_water, $consumer_key, $token));

        $r = (array) array_shift($r);
        if (!empty($r) && $r['max_highwater'] != 'f' && $r['max_highwater'] == 1) {
            throw new OAuthException2('Timestamp is out of sequence. Request rejected. Got '.$timestamp.' last max is '.$r['max_stamp'].' allowed skew is '.$this->max_timestamp_skew);
        }

        // Insert the new combination
        $timestamp_fmt = db_format_timestamp($timestamp);
        try {
            $result = execute_sql('
                    INSERT INTO {oauth_server_nonce}
                      ( consumer_key,
                        token,
                        ctime,
                        nonce )
                        VALUES (?, ?, ?, ?)
                    ', array($consumer_key, $token, $timestamp_fmt, $nonce));
        }
        catch (Exception $e) {
            $result = false;
        }

        if (!$result) {
            throw new OAuthException2('Duplicate timestamp/nonce combination, possible replay attack.  Request rejected.');
        }

        // Clean up all timestamps older than the one we just received
        $low_water = db_format_timestamp($timestamp - $this->max_timestamp_skew);
        delete_records_sql('
                DELETE FROM {oauth_server_nonce}
                WHERE consumer_key  = ?
                  AND token         = ?
                  AND ctime         < ?
                ', array($consumer_key, $token, $low_water));
    }
}
