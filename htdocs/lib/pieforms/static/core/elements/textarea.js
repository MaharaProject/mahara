/**
 * Pieforms: Advanced web forms made easy
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 * Copyright (C) 2006 Drupal (http://www.drupal.org)
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
 *
 * @package    pieform
 * @subpackage static
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 */

/**
 * Retrieves the absolute position of an element on the screen
 * This function (C) 2006 Drupal
 */
function absolutePosition(el) {//{{{
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
}//}}}

/**
 * This class based on Drupal's textArea class, which is (C) 2006 Drupal
 *
 * Provides a 'grippie' for resizing a textarea vertically.
 */
function PieformTextarea(element, fullwidth) {//{{{
    var self = this;

    this.element = element;
    this.parent = this.element.parentNode;
    // need to find the actual size of the element when not hidden so we clone it,
    // add it to the dom, find the dimensions, then remove it.
    this.actual = function(element) {
        var origwidth = document.defaultView.getComputedStyle(element,null).getPropertyValue('width');
        var target = false;
        target = getElement(element).cloneNode(true);
        setStyle(target, {'position': 'absolute',
                          'top': '-2000px',
                          'width': origwidth,
                          '-webkit-box-sizing': 'border-box', /* Safari/Chrome, other WebKit */
                          '-moz-box-sizing': 'border-box', /* Firefox, other Gecko */
                          'box-sizing': 'border-box', /* Opera/IE 8+ */
                          });
        appendChildNodes(currentDocument().body, target);
        var actual = getElementDimensions(target);
        removeElement(target);
        return actual;
    }
    this.dimensions = this.actual(element);

    // Prepare wrapper
    this.wrapper = DIV({'class':'resizable-textarea'});
    insertSiblingNodesBefore(this.element, this.wrapper);

    // Add grippie and measure it
    this.grippie = DIV({'class': 'grippie'});
    appendChildNodes(this.wrapper, this.grippie);
    this.grippie.dimensions = this.actual(this.grippie);

    // Set wrapper and textarea dimensions
    setElementDimensions(this.wrapper, {'h': this.dimensions.h + this.grippie.dimensions.h + 1});
    if (!fullwidth) {
        setElementDimensions(this.wrapper, {'w': this.dimensions.w});
    }
    setStyle(this.element, {
        'margin-bottom': '0',
        'width': '100%'
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
        setStyle(this.grippie, {
            'width': '100%',
            'padding-left': '2px'
        });
    }
    setStyle(this.element, {
        '-webkit-box-sizing': 'border-box', /* Safari/Chrome, other WebKit */
        '-moz-box-sizing': 'border-box', /* Firefox, other Gecko */
        'box-sizing': 'border-box' /* Opera/IE 8+ */
    });
    setStyle(this.grippie, {
        '-webkit-box-sizing': 'border-box', /* Safari/Chrome, other WebKit */
        '-moz-box-sizing': 'border-box', /* Firefox, other Gecko */
        'box-sizing': 'border-box' /* Opera/IE 8+ */
    });

    this.heightOffset = absolutePosition(this.grippie).y - absolutePosition(this.element).y - this.dimensions.h;


    this.handleDrag = function (e) {//{{{
        // Get coordinates relative to text area
        var pos = absolutePosition(this.element);
        var y = e.mouse().client.y - pos.y;

        // Set new height
        var height = Math.max(32, y - this.dragOffset);
        setStyle(this.wrapper, {'height': height + this.grippie.dimensions.h + 1 + 'px'});
        setStyle(this.element, {'height': height - this.grippie.dimensions.h + 1 + 'px'});

        // Avoid text selection
        e.stop();
    }//}}}

    this.endDrag = function (e) {//{{{
        disconnect(this.mouseMoveHandler);
        disconnect(this.mouseUpHandler);
        document.isDragging = false;
    }//}}}

    this.beginDrag = function(e) {//{{{
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
    }//}}}

    connect(this.grippie, 'onmousedown', self, 'beginDrag');
}//}}}

