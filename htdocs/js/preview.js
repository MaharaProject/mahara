/**
 * Helper for showing "preview" boxes, which are just modal dialogs
 * Javascript for the views interface
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2009-2010  Catalyst IT Ltd
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

var preview = DIV({'id':'viewpreview', 'class':'hidden main-column'},
    DIV({'id':'viewpreviewinner'},
        DIV({'id':'viewpreviewclose'},
            A({'href':'','id':'closepreview'}, 'Close')
        ),
        DIV({'id':'viewpreviewcontent'})
    )
);

function showPreview(size, data) {
    $('viewpreviewcontent').innerHTML = data.html;
    var vdim = getViewportDimensions();
    var vpos = getViewportPosition();
    var offset = 16; // Left border & padding of preview container elements (@todo: use getStyle()?)
    if (size == 'small') {
        var width = 400;
        var xpos = (vdim.w - width - offset) / 2;
    }
    else { 
        var width = vdim.w - 200;
        var xpos = vpos.x + 100 - offset;
    }
    setElementDimensions(preview, {'w': width});
    setElementPosition(preview, {'x': xpos, 'y': vpos.y + 200});
    showElement(preview);
}

addLoadEvent(function() {
    appendChildNodes(getFirstElementByTagAndClassName('body'), preview);

    connect('closepreview', 'onclick', function (e) {
        e.stop();
        fade(preview, {'duration': 0.2});
    });
    connect('viewpreviewcontent', 'onclick', function (e) {
        e.stop();
        return false;
    });
});
