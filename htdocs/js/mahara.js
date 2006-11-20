// @todo: Pack it down.

// Expects strings array
function get_string(s) {
    var args = flattenArguments(arguments).slice(1);
    if (typeof(strings) == 'undefined' || typeof(strings[s]) == 'undefined') {
        return '[[[' + s + ((args.length > 0) ? ('(' + args.join(',') + ')') : '') + ']]]';
    }
    var str = strings[s];
    // @todo Need to sprintf these strings properly.
    for (var i = 0; i < args.length; i++) {
        str = str.replace('%s',args[i]);
    }
    return str;
}

function global_error_handler(data) {}

// Appends a status message to the end of elemid
function displayMessage(message, type) {
    // ensure we have type 'ok', 'error', or 'info' (the default)
    if (!type || (type != 'ok' && type != 'error')) {
        type = 'info';
    }

    var message = DIV({'class':type},message, ' ', A({'style': 'cursor: pointer;', 'onclick':'removeElement(this.parentNode)'},'[X]'));

    appendChildNodes('messages', message);

    // callLater(2, function() {
    //     removeElement(message);
    //     //fade(message);
    // });
}

// display a nice little loading notification
function processingStart(msg) {
    if (!msg) {
        msg = get_string('loading');
    }

    replaceChildNodes(
        $('loading_box'),
        DIV(msg)
    );
    $('loading_box').style.display = 'block';
}

// hide the loading notification
function processingStop() {
    $('loading_box').style.display = 'none';
}

// Autofocus the first element with a class of 'autofocus' on page load
addLoadEvent(function() {
    var element = getFirstElementByTagAndClassName(null, 'autofocus', document.body)

    if ( element && typeof(element.focus) == 'function' ) {
        element.focus();
    }
});

// @todo remove this when we migrate to mochikit 1.4
//if (typeof(getFirstElementByTagAndClassName) == 'undefined') {
//    function getFirstElementByTagAndClassName(tag, className, parentElement) {
//        return getElementsByTagAndClassName(tag, className, parentElement)[0];
//    }
//}

var ctxHelp = new Array();
var ctxHelp_selected;
var container;

function contextualHelp(formName, helpName, pluginType, pluginName, language) {
    var tooltip = $('tooltip');
    log(tooltip);
    log(ctxHelp_selected);

    var key = pluginType + '/' + pluginName + '/' + helpName;
    if (typeof(ctxHelp_selected) != 'undefined' && ctxHelp_selected && ctxHelp_selected != key) {
        log('chose another help when one was open');
        contextualHelpClose();
    }

    if (ctxHelp[key] && tooltip) {
        log('have help for this key, and it is already open');
        contextualHelpClose();
        return;
    }
    else if (ctxHelp[key]) {
        log('reloaded help from cache');
        ctxHelp_selected = key;
        contextualHelpOpen(helpName, ctxHelp[key].content);
        return;
    }
    else {
        log('no help for this key yet, getting...');
        ctxHelp[key] = new Object();
        processingStart();
        var d = loadJSONDoc('../lang/' + language + '/help/' + formName + '.' + helpName + '.html');
        d.addCallbacks(
        function (data) {
            ctxHelp[key].content = data;
            container.innerHTML = ctxHelp[key].content;
            processingStop();
        },
        function () {
            ctxHelp[key].content = '<p>Sorry, no help for this element could be found</p>';
            container.innerHTML = ctxHelp[key].content;
            processingStop();
        });
    }

    contextualHelpOpen(helpName, 'spinner');
    ctxHelp[key] = {
        'content': ''
    };
    ctxHelp_selected = key;
}

function contextualHelpClose() {
    var tooltip = $('tooltip');
    if ( ctxHelp_selected && tooltip) {
        tooltip.style.display = 'none';
        removeElement(tooltip);
        //ctxHelp[ctxHelp_selected].visible = 0;
        ctxHelp_selected = null;
    }
}

function contextualHelpOpen(helpName, content) {
    var help = DIV({'id': 'tooltip'});
    container = DIV({'class':'content'});
    container.innerHTML = content;
    appendChildNodes(help, container);

    appendChildNodes($(helpName + '_container'), help);
}

