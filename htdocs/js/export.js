/**
 * JS behaviour for the export UI
 *
 * Copyright (C) 2009 Catalyst IT
 *
 * This file is licensed under the same terms as Mahara itself
 */

addLoadEvent(function() {
    var container = $('whatviews');
    var containerVisible = false;
    var radios = [];

    function toggleRadios(state) {
        forEach(radios, function(r) {
            r.disabled = state;
        });
    }
    var enableRadios  = partial(toggleRadios, '');
    var disableRadios = partial(toggleRadios, 'disabled');

    // Make the radio buttons show/hide the view selector
    forEach(getElementsByTagAndClassName('input', 'radio', 'whattoexport-buttons'), function(radio) {
        radios.push(radio);
        connect(radio, 'onclick', function(e) {
            if (radio.value == 'views' && radio.checked && !containerVisible) {
                disableRadios();
                containerVisible = true;
                slideDown(container, {'duration': 0.5, 'afterFinish': enableRadios, 'beforeSetup': function() { removeElementClass(container, 'js-hidden');  }});
            }
            else if (radio.checked && radio.value != 'views' && containerVisible) {
                disableRadios();
                containerVisible = false;
                slideUp(container, {'duration': 0.5, 'afterFinish': enableRadios});
            }
        });
    });

    // Hook up 'click to preview' links
    forEach(getElementsByTagAndClassName('a', 'viewlink', container), function(i) {
        disconnectAll(i);
        setNodeAttribute(i, 'title', 'Click to preview');
        connect(i, 'onclick', function (e) {
            e.stop();
            var href = getNodeAttribute(this, 'href');
            var params = parseQueryString(href.substring(href.indexOf('?') + 1, href.length));
            sendjsonrequest(config['wwwroot'] + 'view/viewcontent.json.php', params, 'POST', partial(showPreview, 'big'));
        });
    });

});
