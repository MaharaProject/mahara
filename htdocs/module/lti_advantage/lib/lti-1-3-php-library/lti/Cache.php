<?php
namespace IMSGlobal\LTI;

class Cache {

    private $cache;

    /**
     * @var string The path to the dir we will write the cache to.
     */
    private $cache_dir;

    public function get_launch_data($key) {
        $this->load_cache();
        return $this->cache[$key];
    }

    public function cache_launch_data($key, $jwt_body) {
        $this->cache[$key] = $jwt_body;
        $this->save_cache();
        return $this;
    }

    public function cache_nonce($nonce) {
        $this->cache['nonce'][$nonce] = true;
        $this->save_cache();
        return $this;
    }

    public function check_nonce($nonce) {
        $this->load_cache();
        if (!isset($this->cache['nonce'][$nonce])) {
            return false;
        }
        return true;
    }

    private function load_cache() {
        $cache = file_get_contents($this->get_cache_dir() . '/lti_cache.txt');
        if (empty($cache)) {
            file_put_contents($this->get_cache_dir() . '/lti_cache.txt', '{}');
            $this->cache = [];
        }
        $this->cache = json_decode($cache, true);
    }

    private function save_cache() {
        file_put_contents($this->get_cache_dir() . '/lti_cache.txt', json_encode($this->cache));
    }

    /**
     * Get the cache directory.
     *
     * @return string The path of the cache directory.
     */
    public function get_cache_dir() {
        if (empty($this->cache_dir)) {
            $this->cache_dir = sys_get_temp_dir();
        }
        return $this->cache_dir;
    }

    /**
     * Set an alternative cache directory.
     *
     * @param string $dir
     */
    public function set_cache_dir($dir) {
        // Tidy up $dir.
        $dir = trim($dir);
        // Remove trailing slash if present.
        $dir = rtrim($dir, '/');
        // Create the dir if needed. We assume this dir is in an existing dir.
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $this->cache_dir = $dir;
    }
}
?>