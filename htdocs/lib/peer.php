<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
                            'appname'         => '',
                            'institution'     => '',
                            'ipaddress'       => '',
                            'name'            => '',
                            'publickey'       => '',
                            'wwwroot'         => '',
                            'deleted'         => 0,
                            'lastconnecttime' => 0,
                            'publickeyexpires'=> 0,
                        );

    protected $appname;
    protected $institution;
    protected $ipaddress;
    protected $name;
    protected $publickey;
    protected $wwwroot;
    protected $deleted;
    protected $lastconnecttime;
    protected $publickeyexpires;
    protected $certificate;

    public function __construct($result = null) {

        if (null == $result) {
            require_once(get_config('libroot') . 'application.php');
            $this->application = new Application();
            return;
        }
        $this->populate($result);
        $this->initialized = self::PERSISTENT;
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'certificate') {
            return $this->members['publickey']->certificate;
        }
        else if ($field == 'application') {
            return $this->application;
        }
        else if ($field == 'publickeyexpires') {
            $pubkeyexp = $this->get('publickey');
            if (isset($this->publickey->expires)) {
                return $this->publickey->expires;
            }
        }
        return $this->members[$field];
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            $this->{$field} = $value;
            if ($field == 'certificate') {
                $this->members['publickey']->certificate = $value;
            }
            else if ($field == 'publickey') {
                if (!is_object($this->publickey)) {
                    $this->publickey = new stdClass();
                    $this->publickey->certificate = $value;
                }
            }
            else if ($field == 'publickeyexpires') {
                $this->publickey->expires = $value;
            }

            if (isset($this->members[$field])) {
                $this->__set($field, $value);
            }
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    protected function populate($result) {
        $values = get_object_vars($result);
        foreach ($values as $key => $value) {
            $this->set($key, $value);
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
                if (!empty($this->get('appname')) && is_object($this->application) && !empty($this->application->xmlrpcserverurl)) {

                }
            } elseif ($name == 'appname') {

            }
            $this->members[$name] = $value;
            $this->changed = true;
        }

        if (!empty($this->get('wwwroot')) &&
            !empty($this->get('name')) &&
            !empty($this->get('institution')) &&
            !empty($this->get('ipaddress')) &&
            !empty($this->get('appname')) &&
            !empty($this->get('publickey')) &&
            !empty($this->get('publickeyexpires'))) {

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

    public function delete() {
        $this->set('deleted', 1);
        $this->set('changed', true);
    }

    public function commit() {
        if ($this->initialized == self::UNINITIALIZED) return false;
        if (false == $this->changed) return true;
        $host = new stdClass();
        $host->wwwroot          = $this->get('wwwroot');
        $host->deleted          = $this->get('deleted');
        $host->ipaddress        = $this->get('ipaddress');
        $host->name             = $this->get('name');
        $host->publickey        = $this->get('certificate');
        $host->publickeyexpires = $this->get('publickeyexpires');
        $host->lastconnecttime  = $this->get('lastconnecttime');
        $host->appname          = $this->get('appname');
        $host->institution      = $this->get('institution');

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
            $this->set('name', $wwwroot);

            // Get a page from the remote host, and check its title.
            $homepage = file_get_contents($wwwroot);
            if (!empty($homepage) && $count = preg_match("@<title>(.*)</title>@siU", $homepage, $matches)) {
                $this->set('name', $matches[1]);
            }

            $exists = get_record('application', 'name', $appname);

            if (empty($exists)) {
                throw new ParamOutOfRangeException('Application '.addslashes($appname) .' does not exist.');
            }

            $this->set('appname', $appname);
            $this->set('application', Application::findByName($this->get('appname')));
            $this->set('wwwroot', $wwwroot);
            $this->set('ipaddress', $ipaddress);

            require_once(get_config('libroot') .'institution.php');

            if (null == $institution) {
                $institution = new Institution;
                $institution->name = preg_replace('/[^a-zA-Z]/', '', $this->get('name'));

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

                $institution->displayname = $this->get('name');
                $institution->commit();
                $this->set('institution', $institution->name);
            } else {
                $this->set('institution', $institution);
            }

            if (empty($pubkey)) {
                try {
                    $somekey = get_public_key($this->get('wwwroot'), $this->get('appname'));
                    $publickey = new PublicKey($somekey, $this->get('wwwroot'));
                    $this->set('publickey', $publickey);
                } catch (XmlrpcClientException $e) {
                    $errcode = $e->getCode();
                    if ($errcode == 404) {
                        throw new RemoteServerException('404: Incorrect WWWRoot or Application: file not found.');
                    } elseif($errcode == 704) {
                        throw new RemoteServerException('Networking is disabled on the host at ' . $this->get('wwwroot') . '.');
                    }
                    else {
                        throw new RemoteServerException('Error retrieving public key, failed with error code ' . $errcode . ': ' . $e->getMessage());
                    }
                } catch (Exception $e) {
                    throw new RemoteServerException('Error retrieving public key: ' . $e->getMessage());
                }
            } else {
                $publickey = new PublicKey($pubkey, $this->get('wwwroot'));
                $this->set('publickey', $publickey);
            }

            $this->set('lastconnecttime', 0);
            $this->initialized         = self::INITIALIZED;
            $this->changed             = true;
            $pubkeyexp = $this->get('publickey');
            if (false == $pubkeyexp->expires) {
                $this->set('publickey', null);
                return false;
            }

        }

        return true;

    }
}
