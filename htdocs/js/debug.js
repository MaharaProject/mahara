/**
 * debug.js - for developer use
 * @source: http://gitorious.org/mahara/mahara
 *
 * If you're developing for Mahara, you can put any javascript you want to use
 * for debugging in here.
 *
 * This file will only be included if the configuration setting 'developermode'
 * is enabled. You can enable this in config.php
 * JS behaviour for the export UI
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
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
