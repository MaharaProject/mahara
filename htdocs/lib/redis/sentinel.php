<?php

class sentinel {

    private $sentinels = array();

    public $connecttimeout = 1;
    public $readtimeout = 1;
    public $persistent = true;

    private $flags;

    private $connected;

    private $socket;

    private $pingonconnect = false;

    public function __construct($sentinels) {
        if (is_string($sentinels)) {
            $sentinels = explode(',', $sentinels);
        }
        $this->sentinels = $sentinels;

        $this->flags = STREAM_CLIENT_CONNECT;

        $this->connected = false;

    }

    public function __destruct() {
        if (!$this->persistent && $this->connected) {
            $this->disconnect();
        }
    }

    public function connecttopool() {
        if ($this->connected) {
            return true;
        }

        foreach ($this->sentinels as $sentinel) {
            if ($this->connect($sentinel)) {
                return true;
            }
        }

        throw new \Exception('Unable to connect to sentinel pool');
    }


    private function connect($sentinel) {

        if ($this->persistent) {
            $this->socket = @stream_socket_client($sentinel, $errorno, $errstr, $this->connecttimeout, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);
        }
        else {
            $this->socket = @stream_socket_client($sentinel, $errorno, $errstr, $this->connecttimeout);
        }

        if (!$this->socket) {
            $this->connected = false;
            return false;
        }

        $this->connected = true;

        stream_set_blocking($this->socket, true);
        stream_set_timeout($this->socket, $this->readtimeout);

        // Test sentinel is alive
        if ($this->pingonconnect) {
            fwrite($this->socket, "PING\n");
            if (trim(fgets($this->socket)) != '+PONG') {
                fclose($this->socket);
                $this->connected = false;
                return false;
            }
        }
        return true;
    }

    public function disconnect() {
        fclose($this->socket);
        $this->connected = false;
    }

    public function get_master_addr($name) {

        $cmd = "get-master-addr-by-name $name";

        $this->command($cmd);
        if (!$resp = $this->readreply()) {
            return false;
        }

        $ret = new \stdClass();
        $ret->ip = $resp[0];
        $ret->port = $resp[1];

        return ($ret);
    }


    private function command($command) {
        if (!$this->connected) {
            $this->connecttopool();
        }

        if (!$this->connected) {
            return false;
        }
        $cmd = "SENTINEL $command\n";

        $cmdlen = strlen($cmd);
        $lastwrite = 0;
        for ($written = 0; $written < $cmdlen; $written += $lastwrite) {
            $lastwrite = fwrite($this->socket, substr($cmd, $written));

            if ($lastwrite === false || $lastwrite == 0) {
                $this->connected = false;
                throw new \Exception('Failed to write command to stream');
            }
        }
    }


    private function readreply() {
        if (!$this->connected) {
            return false;
        }

        $resp = fgets($this->socket);

        $type = substr($resp, 0, 1);

        switch($type) {

            // Error response
            case '-':
                throw new \Exception('Error response received: '.$resp);
                break;

            // In-line response
            case '+':
                $response = substr($resp, 1);
                return(substr($resp, 1));

            // Defined size response
            case '$':
                $size = (int) substr($resp, 1);
                $resp = stream_get_contents($this->socket, $size + 2);
                if ($resp === false) {
                    throw new \Exception('Failed to read from stream');
                }
                return (trim($resp));

            // Int response
            case ':':
                return ((int)substr($reply, 1));

            // Multi line response
            case '*':
                $multireponse = array();
                $size = (int) substr($resp, 1);

                for ($i = 0; $i < $size; $i++) {
                    $multireponse[] = $this->readreply();
                }
                return($multireponse);

            // Unknown response.
            default:
                throw new \Exception('Unknown read response from stream');
        }
    }
}

