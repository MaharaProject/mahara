/**
 * Helper for showing "preview" boxes, which are just modal dialogs
 * Javascript for the views interface
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

var preview = DIV({'id':'viewpreview', 'class':'hidden main-column'},
    DIV({'id':'viewpreviewinner'},
        DIV({'id':'viewpreviewclose'},
            A({'href':'','id':'closepreview', 'class':'btn-big-close'}, 'Close')
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
    appendChildNodes(document.body, DIV({id: 'overlay'}));
}

addLoadEvent(function() {
    appendChildNodes(getFirstElementByTagAndClassName('body'), preview);

    connect('closepreview', 'onclick', function (e) {
        e.stop();
        fade(preview, {'duration': 0.2});
        if ($('overlay')) {
            removeElement('overlay');
        }
    });
    connect('viewpreviewcontent', 'onclick', function (e) {
        e.stop();
        return false;
    });
});
