/**
 * TODO

 * move javascript into the resume plugin?
 * test in safari and opera
 * implement for employment history
 */

addLoadEvent(function() {
    forEach(getElementsByTagAndClassName('table', 'resumecomposite', 'bottom-pane'), function(i) {
        forEach(getElementsByTagAndClassName('tr', null, getFirstElementByTagAndClassName('tbody', null, i)), function(tr) {
            var title = getFirstElementByTagAndClassName('div', 'jstitle', tr);
            var description = getFirstElementByTagAndClassName('div', 'jsdescription', tr);

            if (!description.childNodes.length) {
                return;
            }

            var td = getFirstParentByTagAndClassName(title);

            var a = A({'href': ''});
            connect(a, 'onclick', function(e) {
                e.stop();
                if (getStyle(description, 'visibility') == 'hidden') {
                    setStyle(description, {
                        'visibility': 'visible',
                        'height': 'auto'
                    });
                }
                else {
                    setStyle(description, {
                        'visibility': 'hidden',
                        'height': '0'
                    });
                }
            });

            replaceChildNodes(title, appendChildNodes(a, title.childNodes));
        });
    });
});

var styleNode = createDOM('link', {
    'rel' : 'stylesheet',
    'type': 'text/css',
    'href': config['wwwroot'] + 'artefact/resume/resume.css'
});
appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
