/**
 * debug.js - for developer use
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later 
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * If you're developing for Mahara, you can put any javascript you want to use
 * for debugging in here.
 *
 * This file will only be included if the configuration setting 'developermode'
 * is enabled. You can enable this in config.php
 * JS behaviour for the export UI
 */

/*
 * gives a nice, stable string representation for objects,
 * ignoring any methods
 */
debugObject = function (obj) {
    var keyValuePairs = [];
    for (var k in obj) {
        var v = obj[k];
        if (typeof(v) != 'function') {
            keyValuePairs.push([k, v]);
        }
    };
    keyValuePairs.sort(compare);
    log( "{" + map(
        function (pair) {
            return map(repr, pair).join(":");
        },
        keyValuePairs
    ).join(", ") + "}");
};

/*
 * gives a nice, stable string representation for objects
 */
debugObjectAll = function (obj) {
    var keyValuePairs = [];
    for (var k in obj) {
        var v = obj[k];
        keyValuePairs.push([k, v]);
    };
    keyValuePairs.sort(compare);
    log( "{" + map(
        function (pair) {
            return map(repr, pair).join(":");
        },
        keyValuePairs
    ).join(", ") + "}");
};
