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

