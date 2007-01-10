/**
 * Pieforms core javascript
 * Author: Nigel McNie
 * (C) 2006 Nigel McNie
 * Released under the GNU GPL, see the COPYING file
 * @todo pack this, provide a source version. Same with MochiKit
 */

// The resizable textarea code is based on the code from Drupal (http://drupal.org/)

/**
 * Retrieves the absolute position of an element on the screen
 * This function (C) 2006 Drupal
 */
function absolutePosition(el) {
    var sLeft = 0, sTop = 0;
    var isDiv = /^div$/i.test(el.tagName);
    if (isDiv && el.scrollLeft) {
        sLeft = el.scrollLeft;
    }
    if (isDiv && el.scrollTop) {
        sTop = el.scrollTop;
    }
    var r = { x: el.offsetLeft - sLeft, y: el.offsetTop - sTop };
    if (el.offsetParent) {
        var tmp = absolutePosition(el.offsetParent);
        r.x += tmp.x;
        r.y += tmp.y;
    }
    return r;
}

addLoadEvent(function() {
    forEach(getElementsByTagAndClassName('form', 'pieform'), function(form) {
        forEach(getElementsByTagAndClassName('textarea', 'resizable', form), function (textarea) {
            new TextArea(textarea);
        });
    });
});

/**
 * This class based on Drupal's textArea class, which is (C) 2006 Drupal
 *
 * Provides a 'grippie' for resizing a textarea vertically.
 */
function TextArea(element) {
    var self = this;

    this.element = element;
    this.parent = this.element.parentNode;
    this.dimensions = getElementDimensions(element);

    // Prepare wrapper
    this.wrapper = DIV({'class':'resizable-textarea'});
    insertSiblingNodesBefore(this.element, this.wrapper);

    // Add grippie and measure it
    this.grippie = DIV({'class': 'grippie'});
    appendChildNodes(this.wrapper, this.grippie);
    this.grippie.dimensions = getElementDimensions(this.grippie);

    // Set wrapper and textarea dimensions
    setElementDimensions(this.wrapper, {'h': this.dimensions.h + this.grippie.dimensions.h + 1});
    setStyle(this.element, {
        'margin-bottom': '0',
        'width': '100%',
        'height': this.dimensions.h + 'px'
    });

    // Wrap textarea
    removeElement(this.element);
    insertSiblingNodesBefore(this.grippie, this.element);

    // Measure difference between desired and actual textarea dimensions to account for padding/borders
    this.widthOffset = getElementDimensions(this.wrapper).w - this.dimensions.w;

    // Make the grippie line up in various browsers
    if (window.opera) {
        setStyle(this.grippie, {'margin-right': '4px'});
    }
    if (document.all && !window.opera) {
        this.grippie.style.width = '100%';
        this.grippie.style.paddingLeft = '2px';
        setStyle(this.grippie, {
            'width': '100%',
            'padding-left': '2px'
        });
    }
    this.element.style.MozBoxSizing = 'border-box';

    this.heightOffset = absolutePosition(this.grippie).y - absolutePosition(this.element).y - this.dimensions.h;


    this.handleDrag = function (e) {
        // Get coordinates relative to text area
        var pos = absolutePosition(this.element);
        var y = e.mouse().client.y - pos.y;

        // Set new height
        var height = Math.max(32, y - this.dragOffset - this.heightOffset);
        setStyle(this.wrapper, {'height': height + this.grippie.dimensions.h + 1 + 'px'});
        setStyle(this.element, {'height': height + 'px'});

        // Avoid text selection
        e.stop();
    }

    this.endDrag = function (e) {
        disconnect(this.mouseMoveHandler);
        disconnect(this.mouseUpHandler);
        document.isDragging = false;
    }

    this.beginDrag = function(e) {
        if (document.isDragging) {
            return;
        }
        document.isDragging = true;

        self.mouseMoveHandler = connect(document, 'onmousemove', self, 'handleDrag');
        self.mouseUpHandler   = connect(document, 'onmouseup', self, 'endDrag');

        // Store drag offset from grippie top
        var pos = absolutePosition(this.grippie);
        this.dragOffset = e.mouse().client.y - pos.y;

        // Process
        this.handleDrag(e);
    }

    connect(this.grippie, 'onmousedown', self, 'beginDrag');
}
