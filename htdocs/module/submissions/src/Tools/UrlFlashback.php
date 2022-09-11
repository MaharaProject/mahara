<?php
/**
 *
 * @package    Mahara
 * @subpackage module-submissions
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information, please see the README file distributed with this software.
 *
 */

namespace Submissions\Tools;

class UrlFlashback {

    public $sourceUrl;
    public $destinationUrls = [];
    public $flashbackUrl;

    protected $sourceUrlReferrer;
    protected $currentUrl;
    protected $previousUrl;
    protected $requestMethod;

    protected $data;

    protected $options = [
        'entryLivespan' => 86400,
        'cookiePath' => '/',
        'destinationUrlHasGetAndPost' => true,
        'browserBackIsActive' => true
    ];

    // region getter/setter

    /**
     * @return string
     */
    public function getCurrentUrl() {
        return $this->currentUrl;
    }

    /**
     * @return mixed
     */
    public function getPreviousUrl() {
        return $this->previousUrl;
    }

    /**
     * @return array|object
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param $value
     */
    public function setOptions($value) {
        self::setArrayOrStdClass($this->options, $value);
    }

    /**
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param $value
     */
    public function setData($value) {
        self::setArrayOrStdClass($this->data, $value);
    }

    /**
     * @param $property
     * @param $value
     */
    private static function setArrayOrStdClass($property, $value) {
        if (is_array($value)) {
            $property = (object) $value;
        }
        else if (is_a($value, 'stdClass')) {
            $property = $value;
        }
        else {
            throw new \UnexpectedValueException();
        }
    }
    // endregion

    /**
     * UrlFlashback constructor.
     * @param bool $newEntry
     */
    function __construct(bool $newEntry = true) {
        // $_SERVER['REQUEST_SCHEME'] isn't reliable set
        $requestScheme = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
        $this->currentUrl = $requestScheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $this->previousUrl = $_SERVER['HTTP_REFERER'];
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->options = (object) $this->options;
        $this->options->cookiePath = parse_url(get_config('wwwroot'), PHP_URL_PATH);

        if ($newEntry) {
            $this->sourceUrl = $this->currentUrl;
            $this->sourceUrlReferrer = $this->previousUrl;
        }
        else {
            $this->readEntry();
        }
    }

    // region instance methods

    /**
     * @param array $destinationUrls
     * @param string|null $flashbackUrl
     * @param array|null $data
     * @param array|null $options
     * @return UrlFlashback
     */
    public static function createNewInstance(array $destinationUrls = [], string $flashbackUrl = null, array $data = null, array $options = null) {
        $urlFlashback = new UrlFlashback(true);
        if ($destinationUrls) {
            $urlFlashback->destinationUrls = $destinationUrls;
        }
        if (isset($flashbackUrl)) {
            $urlFlashback->flashbackUrl = $flashbackUrl;
        }
        if (isset($data)) {
            $urlFlashback->data = (object) $data;
        }
        if (isset($options)) {
            $urlFlashback->options = (object) $options;
        }

        return $urlFlashback;
    }

    /**
     * @return UrlFlashback
     */
    public static function createInstanceFromEntry() {
        return new UrlFlashback(false);
    }

    /**
     * @return UrlFlashback
     */
    public static function createInstanceFromEntryAndRemoveEntry() {
        $instance = self::createInstanceFromEntry();
        $instance->removeEntry();

        return $instance;
    }
    // endregion

    // region info/validation methods
    /**
     * @return bool
     */
    public function isValid() {
        switch (true) {
            case $this->currentUrlIsDestination():
                if ($this->options->destinationUrlHasGetAndPost && $this->requestMethod === 'POST') {
                    return $this->previousUrl === $this->currentUrl;
                }
                $previousUrlIsDestination = false;
                if (count($this->destinationUrls) > 1) {
                    $previousUrlIsDestination = $this->urlIsDestination($this->previousUrl);
                }
                return $this->previousUrl === $this->sourceUrl || $previousUrlIsDestination;

            case $this->currentUrlIsFlashback() && $this->sourceUrl !== $this->flashbackUrl:
                return $this->urlIsDestination($this->previousUrl);

            // If sourceUrl is flashbackUrl then we also have to consider the browser back button for validation
            case $this->currentUrlIsFlashback() && $this->sourceUrl === $this->flashbackUrl:
                return $this->urlIsDestination($this->previousUrl) || ($this->options->browserBackIsActive ? $this->previousUrl === $this->sourceUrlReferrer : false);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isValidOrRemoveEntry() {
        $isValid = $this->isValid();

        if (!$isValid) {
            $this->removeEntry();
        }
        return $isValid;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function urlIsDestination(string $url) {
        return in_array($url, $this->destinationUrls);
    }

    /**
     * @return bool
     */
    public function currentUrlIsSource() {
        return $this->currentUrl === $this->sourceUrl;
    }

    /**
     * @return bool
     */
    public function currentUrlIsDestination() {
        return $this->urlIsDestination($this->currentUrl);
    }

    /**
     * @return bool
     */
    public function currentUrlIsFlashback() {
        return $this->currentUrl === $this->flashbackUrl;
    }
    // endregion

    // region entry methods
    /**
     * @throws \ErrorException
     */
    public function addOrUpdateEntry() {
        if (!is_array($this->destinationUrls) || empty($this->destinationUrls)) {
            throw new \ErrorException('Missing destination URL or wrong variable type (Has to be array).');
        }
        if (empty($this->flashbackUrl)) {
            $this->flashbackUrl = $this->sourceUrl;
        }
        setcookie('UrlFlashback', json_encode($this), time() + $this->options->entryLivespan, $this->options->cookiePath, '', true);
    }

    public function removeEntry() {
        setcookie('UrlFlashback', '', time() - 1, $this->options->cookiePath);
    }

    public function readEntry() {
        if (isset($_COOKIE['UrlFlashback'])) {
            $flashbackEntry = json_decode($_COOKIE['UrlFlashback']);
            $this->sourceUrl = $flashbackEntry->sourceUrl;
            $this->destinationUrls = $flashbackEntry->destinationUrls;
            $this->flashbackUrl = $flashbackEntry->flashbackUrl;
            $this->sourceUrlReferrer = $flashbackEntry->sourceUrlReferrer;
            $this->data = $flashbackEntry->data;
            $this->options = $flashbackEntry->options;
        }
    }
    // endregion
}