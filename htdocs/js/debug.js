/*
 * debug.js - for developer use
 *
 * If you're developing for Mahara, you can put any javascript you want to use
 * for debugging in here.
 *
 * This file will only be included if the configuration setting 'developermode'
 * is enabled. You can enable this in config.php
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
