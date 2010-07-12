/**
 * Javascript for the resume artefact
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

/*
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
