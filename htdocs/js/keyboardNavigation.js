/**
 * Adds keystroke navigation to Mahara.
 * @source: http://gitorious.org/mahara/mahara
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

addLoadEvent(function() {
    connect(window,'onkeypress',function(e) {
        var targetType = e.target().nodeName;
        
        if (
            targetType == 'INPUT'
            || targetType == 'TEXTAREA'
            || targetType == 'SELECT'
            || targetType == 'BUTTON'
        ) {
            return;
        }

        if (config.commandMode) {
            switch(e.key().string) {
                case 'a':
                    document.location.href = config.wwwroot + 'admin/';
                    break;
                case 'h':
                    document.location.href = config.wwwroot;
                    break;
                case 'b':
                    document.location.href = config.wwwroot + 'artefact/blog/';
                    break;
                case 'p':
                    document.location.href = config.wwwroot + 'artefact/internal/';
                    break;
                case 'f':
                    document.location.href = config.wwwroot + 'artefact/file/';
                    break;
                case 'g':
                    document.location.href = config.wwwroot + 'group/mygroups.php';
                    break;
                case 'v':
                    document.location.href = config.wwwroot + 'view';
                    break;
                case 'c':
                    document.location.href = config.wwwroot + 'collection';
                    break;
                case 'l':
                    document.location.href = config.wwwroot + 'artefact/plans';
                    break;
                case '/':
                    document.usf.query.focus();
                    break;
            }
            config.commandMode = false;
        }
        else {
            if (e.key().string == 'g') {
                config.commandMode = true;
            }
        }
    });
});
