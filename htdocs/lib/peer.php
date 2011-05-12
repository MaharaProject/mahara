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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */


class Peer {

    const   UNINITIALIZED           = 0;
    const   INITIALIZED             = 1;
    const   PERSISTENT              = 2;

    private $initialized            = self::UNINITIALIZED;
    private $changed                = false;
    private $application;
    private $oldwwwroot;
    private $members = array(/* host table */
                             'wwwroot' => '',
                             'name' => '',
                             'institution' => '',
                             'ipaddress' => '',
                             'portno' => 80,
                             'publickey' => '',
                             'publickeyexpires' => 0,
                             'deleted' => 0,
                             'lastconnecttime' => 0,
                             'appname' => ''
                             );

    public function __construct($result = null) {

        if (null == $result) {
            require_once(get_config('libroot') . 'application.php');
            $this->application = new Application();
            return;
        }
        $this->populate($result);
        $this->initialized = self::PERSISTENT;
    }

    protected function populate($result) {
        $values = get_object_vars($result);
        foreach ($values as $key => $value) {
            $this->__set($key, $value);
        }
        $this->oldwwwroot = $result->wwwroot;
    }

    public function __set($name, $value) {
        if (!array_key_exists($name, $this->members)) {
            throw new ParamOutOfRangeException(addslashes($name) .' is not a member of Peer.');
        }

        if (is_scalar($value) != is_scalar($this->members[$name]) || $value != $this->members[$name]) {
            if ($name == 'appname') {
                $this->application = Application::findByName($value);
            } elseif ($name == 'wwwroot') {
                if (!empty($this->appname) && is_object($this->application) && !empty($this->application->xmlrpcserverurl)) {
                    
                }
            } elseif ($name == 'appname') {
                
            }
            $this->members[$name] = $value;
            $this->changed = true;
        }

        if (!empty($this->wwwroot) &&
            !empty($this->name) &&
            !empty($this->institution) &&
            !empty($this->ipaddress) &&
            !empty($this->portno) &&
            !empty($this->appname) &&
            !empty($this->publickey) &&
            !empty($this->publickeyexpires)) {

            $this->initialized = self::INITIALIZED;
        }
        return $this;
    }

    public function findByWwwroot($wwwroot) {

        $wwwroot = dropslash($wwwroot);
        $result = get_record('host', 'wwwroot', $wwwroot);

        if ($result != false) {
            $this->populate($result);
            $this->initialized = self::PERSISTENT;
            $this->members['publickey'] = new PublicKey($this->members['publickey'], $this->members['wwwroot']);
            return $this;
        }
        return false;
    }

    public function __get($name) {
        if ($name == 'certificate') {
            return $this->members['publickey']->certificate;
        } elseif ($name == 'application') {
            return $this->application;
        } elseif ($name == 'publickeyexpires') {
            return $this->publickey->expires;
        }
        return $this->members[$name];
    }

    public function delete() {
        $this->deleted = 1;
        $this->changed = true;
    }

    public function commit() {
        if ($this->initialized == self::UNINITIALIZED) return false;
        if (false == $this->changed) return true;
        $host = new stdClass();
        $host->wwwroot          = $this->wwwroot;
        $host->deleted          = $this->deleted;
        $host->ipaddress        = $this->ipaddress;
        $host->name             = $this->name;
        $host->publickey        = $this->certificate;
        $host->publickeyexpires = $this->publickeyexpires;
        $host->portno           = $this->portno;
        $host->lastconnecttime  = $this->lastconnecttime;
        $host->appname          = $this->appname;
        $host->institution      = $this->institution;

        if ($this->initialized == self::INITIALIZED) {
            $this->initialized = self::PERSISTENT;
            $exists = get_record('host', 'wwwroot', $host->wwwroot);
            if (false == $exists) {
                return insert_record('host',$host);
            }
            return true;
        }

        return update_record('host',$host,array('wwwroot' => $host->wwwroot));
    }

    public function bootstrap($wwwroot, $pubkey, $appname = 'moodle', $institution = null) {

        $wwwroot = dropslash($wwwroot);

        if ( ! $this->findByWwwroot($wwwroot) ) {

            $hostname = get_hostname_from_uri($wwwroot);

            // Get the IP address for that host - if this fails, it will
            // return the hostname string
            $ipaddress = gethostbyname($hostname);

            // Couldn't find the IP address?
            if ($ipaddress === $hostname && !preg_match('/^\d+\.\d+\.\d+.\d+$/',$hostname)) {
                throw new ParamOutOfRangeException('Could not find IP address for host: '.addslashes($hostname));
                return false;
            }

            // Default the name to the wwwroot
            $this->name = $wwwroot;

            // Get a page from the remote host, and check its title.
            $homepage = file_get_contents($wwwroot);
            if (!empty($homepage) && $count = preg_match("@<title>(.*)</title>@siU", $homepage, $matches)) {
                $this->name = $matches[1];
            }

            $exists = get_record('application', 'name', $appname);

            if (empty($exists)) {
                throw new ParamOutOfRangeException('Application '.addslashes($appname) .' does not exist.');
            }

            $this->appname             = $appname;
            $this->application         = Application::findByName($this->appname);
            $this->wwwroot             = $wwwroot;
            $this->ipaddress           = $ipaddress;

            require_once(get_config('libroot') .'institution.php');

            if (null == $institution) {
                $institution = new Institution;
                $institution->name = preg_replace('/[^a-zA-Z]/', '', $this->name);

                // Check that the institution name has not already been taken. 
                // If it has, we change it until we find a name that works
                $existinginstitutionnames = get_column('institution', 'name');
                if (in_array($institution->name, $existinginstitutionnames)) {
                    $success = false;
                    foreach (range('a', 'z') as $character) {
                        $testname = $institution->name . $character;
                        if (!in_array($testname, $existinginstitutionnames)) {
                            $success = true;
                            $institution->name = $testname;
                            break;
                        }
                    }

                    if (!$success) {
                        // We couldn't find a unique name. Noes!
                        throw new RemoteServerException('Could not create a unique institution name');
                    }
                }

                $institution->displayname = $this->name;
                $institution->commit();
                $this->institution = $institution->name;
            } else {
                $this->institution = $institution;
            }

            if (empty($pubkey)) {
                try {
                    $somekey = get_public_key($this->wwwroot, $this->appname);
                    $this->publickey       = new PublicKey($somekey, $this->wwwroot);
                } catch (XmlrpcClientException $e) {
                    $errcode = $e->getCode();
                    if ($errcode == 404) {
                        throw new RemoteServerException('404: Incorrect WWWRoot or Application: file not found.');
                    } elseif($errcode == 704) {
                        throw new RemoteServerException('Networking is disabled on the host at '.$this->wwwroot.'.');
                    }
                    else {
                        throw new RemoteServerException('Error retrieving public key, failed with error code ' . $errcode . ': ' . $e->getMessage());
                    }
                } catch (Exception $e) {
                    throw new RemoteServerException('Error retrieving public key: ' . $e->getMessage());
                }
            } else {
                $this->publickey       = new PublicKey($pubkey, $this->wwwroot);
            }

            $this->lastconnecttime     = 0;
            $this->initialized         = self::INITIALIZED;
            $this->changed             = true;
            if (false == $this->publickey->expires) {
                $this->publickey == null;
                return false;
            }

        }

        return true;
        
    }
}
