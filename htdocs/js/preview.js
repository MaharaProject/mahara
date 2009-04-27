/**
 * Helper for showing "preview" boxes, which are just modal dialogs
 *
 * Copyright (C) 2009 Catalyst IT
 *
 * This file is licensed under the same terms as Mahara itself
 */

var preview = DIV({'id':'viewpreview', 'class':'hidden'},
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
