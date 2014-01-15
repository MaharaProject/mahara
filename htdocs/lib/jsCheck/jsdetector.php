<?php
/*
 *   jsCheck - By Gustav Eklundh
 *   A PHP-class to check if JavaScript is activated or not.
 *
 *   Copyright (C) 2009  Gustav Eklundh
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License v3 as published
 *   by the Free Software Foundation.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *   Homepage:  http://threebyte.eu/
 *   Email:     gustav@xcoders.info
 **/
/**
 *
 * This class will detect if the browser supports javascript and store the result in $_SESSION['javascriptenabled'].
 *
 * How to use:
 * // At the begining of your PHP file
 * JavascriptDetector::check_javascript();
 * ...
 * ...
 * // When you need to know if the browser supports javascript or not
 * if (JavascriptDetector::is_javascript_activated()) {
 *     // Code for javascript is enabled
 *     ...
 * }
 * else {
 *     // Code for javascript is disabled
 *     ...
 * }
 * // You can also use $SESSION->get('javascriptenabled')
 * if ($SESSION->get('javascriptenabled')) {
 *     // Code for javascript is supported
 *     ...
 * }
 *
 * The following method should be called when the user does logout
 * JavascriptDetector::reset(); // to force the JavascriptDetector to detect again
 */
class JavascriptDetector {

    static public function check_javascript() {
        global $SESSION;
        if (isset($_POST['javascriptenabled'])) {
            $SESSION->set('javascriptenabled', true);
        }
        if ($SESSION->get('javascriptenabled') ===  null && !defined('JSON') && !defined('CLI')) {
            $SESSION->set('javascriptenabled', false);
            echo <<<JS
    <form name="jsdetector_form" id="jsdetector_form" method="post">
        <input name="javascriptenabled" type="hidden" value="true" />
        <script type="text/javascript">
            document.jsdetector_form.submit();
        </script>
    </form>
JS;
        }
    }

    static public function reset() {
        global $SESSION;
        $SESSION->clear('javascriptenabled');
    }

    static public function is_javascript_activated() {
        global $SESSION;
        return ($SESSION->get('javascriptenabled') === true);
    }
}
