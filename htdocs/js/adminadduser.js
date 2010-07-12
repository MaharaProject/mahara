/**
 * Support file for the adduser admin page in Mahara
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

var current;

function move_step(i) {
    var selected = getFirstParentByTagAndClassName(i, 'td', 'step');
    if (selected != current) {
        addElementClass(selected, 'current');
        if (current) {
            removeElementClass(current, 'current');
        }
        current = selected;
    }
}

addLoadEvent(function() {
    var step1_spans = getElementsByTagAndClassName('span', 'requiredmarker', 'step1');
    var step1_inputs = getElementsByTagAndClassName('input', 'required', 'step1');
    var leap2a_input = $('adduser_leap2afile');
    var leap2a_label = $('leap2a_label');

    leap2a_input.disabled = true;
    addElementClass('step1', 'current');

    /**
     * state = true if the user selects the leap2a radio button, else false
     */
    function set_step1_requiredfields(state) {
        if (state) {
            forEach(step1_spans, function(span) {
                setStyle(span, {'visibility': 'hidden'});
            });
            forEach(step1_inputs, function(input) {
                removeElementClass(input, 'required');
            });
        }
        else {
            forEach(step1_spans, function(span) {
                setStyle(span, {'visibility': 'visible'});
            });
            forEach(step1_inputs, function(input) {
                addElementClass(input, 'required');
            });
        }

        $('adduser_firstname').disabled = state;
        $('adduser_lastname').disabled = state;
        $('adduser_email').disabled = state;
        $('adduser_leap2afile').disabled = !state;
    }


    forEach(getElementsByTagAndClassName('input', 'ic', 'adduser'), function(i) {
        connect(i, 'onclick', function(e) {
            set_step1_requiredfields(i.id == 'uploadleap');
        });
        if (i.checked) {
            set_step1_requiredfields(i.id == 'uploadleap');
        }
    });


    current = getFirstElementByTagAndClassName('td', 'step1', 'adduser');
    forEach(getElementsByTagAndClassName('input', null, 'adduser'), function(i) {
        connect(i, 'onfocus', partial(move_step, i));
        connect(i, 'onclick', partial(move_step, i));
    });
});

