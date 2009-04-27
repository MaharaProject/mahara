/**
 * Hooks in a stylesheet that is only loaded when javascript is enabled
 *
 * Copyright (C) 2009 Catalyst IT Ltd
 * This file is licensed under the same terms as Mahara itself
 */
var styleNode = createDOM('link', {
    'rel' : 'stylesheet',
    'type': 'text/css',
    'href': config['theme']['style/js.css']
});
appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
