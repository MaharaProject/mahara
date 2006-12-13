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

// Function to post a data object to a json script.
function sendjsonrequest(script, data, successcallback, errorcallback) {
    donothing = function () { return; };
    if (typeof(successcallback) != 'function') {
        successcallback = donothing;
    }
    if (typeof(errorcallback) != 'function') {
        errorcallback = donothing;
    }
    processingStart();
    var req = getXMLHttpRequest();
    req.open('POST', script);
    req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    var d = sendXMLHttpRequest(req,queryString(data));
    d.addCallbacks(function (result) {
        var data = evalJSONRequest(result);
        var errtype = false;
        if (!data.error) { 
            errtype = 'info';
        }
        else if (data.error == 'local') {
            errtype = 'error';
        }
        else {
            global_error_handler(data);
        }
        if (errtype) {
            displayMessage(data.message,errtype);
            successcallback();
            processingStop();
        }
    },
    function () {
        displayMessage(get_string('unknownerror'),'error');
        errorcallback();
        processingStop();
    });
}

// Autofocus the first element with a class of 'autofocus' on page load
// Also, connect input elements with the 'emptyonfocus' class to work properly
addLoadEvent(function() {
    var element = getFirstElementByTagAndClassName(null, 'autofocus', document.body)

    if ( element && typeof(element.focus) == 'function' ) {
        element.focus();
    }

    forEach(getElementsByTagAndClassName('input', 'emptyonfocus'), function(elem) {
        connect(elem, 'onfocus', function(e) { elem.value = ''; e.stop(); });
    });
});

var ctxHelp = new Array();
var ctxHelp_selected;
var container;

function contextualHelp(formName, helpName, pluginType, pluginName, page) {
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
        contextualHelpOpen(formName + '_' + helpName, ctxHelp[key].content);
        return;
    }
    else {
        log('no help for this key yet, getting...');
        ctxHelp[key] = new Object();
        processingStart();
	var url = '../json/help.php?plugintype=' + pluginType + '&pluginname=' + pluginName;
	if (page) {
	    url += '&page=' + page;
	}
	else {
	    url += '&form=' + formName + '&element=' + helpName;
	}
        var d = loadJSONDoc(url);
        d.addCallbacks(
        function (data) {
	    if (data.error) {
		ctxHelp[key].content = data.message;
		container.innerHTML = ctxHelp[key].content;
		processingStop();
	    } 
	    else {
		ctxHelp[key].content = data.content;
		container.innerHTML = ctxHelp[key].content;
		processingStop();
	    }
        },
        function () {
            ctxHelp[key].content = '<p>Sorry, no help for this element could be found</p>';
            container.innerHTML = ctxHelp[key].content;
            processingStop();
        });
    }

    contextualHelpOpen(formName + '_' + helpName, 'spinner');
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

// this function gets the cookie, if it exists
function getCookie( name ) {
    var start = document.cookie.indexOf( name + "=" );
    var len = start + name.length + 1;

    if (
        ( !start ) &&
        ( name != document.cookie.substring( 0, name.length ) )
    ) {
        return null;
    }

    if ( start == -1 ) {
        return null;
    }

    var end = document.cookie.indexOf( ";", len );

    if ( end == -1 ) {
        end = document.cookie.length;
    }

    return unescape( document.cookie.substring( len, end ) );
}

function clearCookie( name ) {
    setCookie(name, '', -1);
}

// expires is in seconds
function setCookie( name, value, expires, path, domain, secure ) 
{
    // set time, it's in milliseconds
    var today = new Date();
    today.setTime( today.getTime() );

    /*
    if the expires variable is set, make the correct 
    expires time, the current script below will set 
    it for x number of days, to make it for hours, 
    delete * 24, for minutes, delete * 60 * 24
    */
    if ( expires )
    {
        expires = expires * 1000;
    }

    var expires_date = new Date( today.getTime() + (expires) );

    document.cookie = name + "=" + escape( value ) +
    ( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
    ( ( path ) ? ";path=" + path : "" ) + 
    ( ( domain ) ? ";domain=" + domain : "" ) +
    ( ( secure ) ? ";secure" : "" );
}

function toggleChecked(c) {
    var e = getElementsByTagAndClassName(null,c);
    if (e) {
        for (cb in e) {
	    if (e[cb].checked == true) {
                e[cb].checked = '';
            } 
            else {
                e[cb].checked = 'checked';
            }
        }
    }
    return false;

}

function expandDownToViewport(element, width) {
    if (typeof(width) == 'undefined') {
        width = getElementDimensions(element).w;
    }

    var viewport = getViewportDimensions();
    var position = getElementPosition(element);
    var newheight = new Dimensions(width, viewport.h - position.y - 2);

    if ($('footer')) {
        newheight.h -= getElementDimensions('footer').h + 8;
    }

    setElementDimensions(element, newheight);
}


// Support images for checkboxes
// This adapted from http://slayeroffice.com/code/custom_checkbox/ 
function toggleCheckBox(img, caller) {
    log('toggleCheckBox ');
    log(img.src);
    log(img.alt);
    log(img.xid);
    var form = document.forms[img.formIndex];
    var objName = img.xid;
    log(form.elements[objName].checked);

    //if (caller) {
    log('current value = ' + form.elements[objName].checked);
        //form.elements[objName].checked = !form.elements[objName].checked;
    //}

    if (form.elements[objName].checked) {
        log('checked');
        //removeElement(img);
        //img = IMG({'src': '/theme/default/static/images/tickbox_off.gif'});
        //insertSiblingNodesBefore(form.elements[objName], img);
        setNodeAttribute(img, 'src', '/theme/default/static/images/tickbox_off.gif');
        setNodeAttribute(img, 'alt', 'Click here to deselect this option');
    }
    else {
        log('not checked');
        removeElement(img);
        img = IMG({'src': '/theme/default/static/images/tickbox_on.gif'});
        insertSiblingNodesBefore(form.elements[objName], img);
        //setNodeAttribute(img, 'src', '/theme/default/static/images/tickbox_off.gif');
        //setNodeAttribute(img, 'alt', 'Click here to select this option');
    }
}

//addLoadEvent(function() {
//    var events = new Array("onfocus", "onblur", "onselect", "onchange", "onclick", "ondblclick", "onmousedown", "onmouseup", "onmouseover", "onmousemove", "onmouseout", "onkeypress", "onkeydown", "onkeyup");
//    var forms = document.forms;
//
//    for (var i = 0; i < forms.length; i++) {
//        var formElements = forms[i].elements;
//        for (var j = 0; j < formElements.length; j++) {
//            if (getNodeAttribute(formElements[j], 'type') == 'checkbox') {
//                formElements[j].style.position = 'absolute';
//                formElements[j].style.left = '-9000px';
//                var img = IMG(null);
//                if (formElements[j].checked == false) {
//                    setNodeAttribute(img, 'src', '/theme/default/static/images/tickbox_off.gif');
//                    setNodeAttribute(img, 'alt', 'Click here to select this option');
//                }
//                else {
//                    setNodeAttribute(img, 'src', '/theme/default/static/images/tickbox_on.gif');
//                    setNodeAttribute(img, 'alt', 'Click here to deselect this option');
//                }
//                img.xid = formElements[j].name;
//                img.formIndex = i;
//                //connect(img, 'onclick', function() { toggleCheckBox(img); });
//                insertSiblingNodesBefore(formElements[j], img);
//                formElements[j].objRef = img;
//                log(formElements[j].checked);
//                //for (var e = 0; e < events.length; e++) {
//                //    if (eval('formElements[j].' + events[e])) {
//                //        eval('img.' + events[e] + '= formElements[j].' + events[e]);
//                //    }
//                //}
//
//                connect(formElements[j], 'onchange', function (e) {
//                    toggleCheckBox(this.objRef);
//                    log('CHECKBOX NOW ' + this.checked);
//                    e.stop();
//                });
//            }
//        }
//    }
//});



var cboxOnImg  = config['themeurl'] + 'images/tickbox_on.gif';
var cboxOffImg = config['themeurl'] + 'images/tickbox_off.gif';

var d=document;

addLoadEvent(function () {
    // an array of applicable events that we'll need to carry over to our custom checkbox
    events = new Array("onfocus", "onblur", "onselect", "onchange", "onclick", "ondblclick", "onmousedown", "onmouseup", "onmouseover", "onmousemove", "onmouseout", "onkeypress", "onkeydown", "onkeyup");
    // a reference var to all the forms in the document

    ///frm = d.getElementsByTagName("form");
    frm = getElementsByTagAndClassName('form');
    // loop over the length of the forms in the document
    for(i = 0;i < frm.length; i++) {
        // reference to the elements of the form
        c = frm[i].elements;
        // loop over the length of those elements
        for(j = 0; j < c.length; j++) {
            // if this element is a checkbox, do our thing

            if(getNodeAttribute(c[j], 'type')/*c[j].getAttribute("type")*/ == 'checkbox') {
                // hide the original checkbox
                ///c[j].style.position = "absolute";
                ///c[j].style.left = "-9000px";
                hideElement(c[j]);
                // create the replacement image
                n = d.createElement("img");
                ///n.setAttribute("class","chk");

                // check if the corresponding checkbox is checked or not. set the
                // status of the image accordingly
                if(c[j].checked == false) {
                    n.setAttribute("src", cboxOffImg);
                    //n.setAttribute("title", "click here to select this option.");
                    //n.setAttribute("alt", "click here to select this option.");
                } else {
                    n.setAttribute("src", cboxOnImg);
                    //n.setAttribute("title","click here to deselect this option.");
                    //n.setAttribute("alt","click here to deselect this option.");
                }
                // there are several pieces of data we'll need to know later.
                // assign them as attributes of the image we've created
                // first - the name of the corresponding checkbox
                ///n.xid = c[j].getAttribute("name");
                n.xid = getNodeAttribute(c[j], 'name');
                // next, the index of the FORM element so we'll know which form object to access later

                n.frmIndex = i;
                // assign the onclick event to the image
                ///n.onclick = function() { toggleCheckBox(this,0);return false; }
                connect(n, 'onclick', function(e) {
                    toggleCheckBox(this, 0);
                    e.stop();
                });
                // insert the image into the DOM
                ///c[j].parentNode.insertBefore(n,c[j].nextSibling)
                insertSiblingNodesAfter(c[j], n);
                // this attribute is a bit of a hack - we need to know in the event of a label click (for browsers that support it)
                // which image we need turn on or off. So, we set the image as an attribute!
                c[j].objRef = n;
                // assign the checkbox objects event handlers to its replacement image
                for(e = 0;e < events.length; e++) if(eval('c[j].' +events[e])) eval('n.' + events[e] + '= c[j].' + events[e]);
                // append our onchange event handler to any existing ones.
                fn = c[j].onchange;
                if(typeof(fn) == 'function') {
                    c[j].onchange = function() { fn(); toggleCheckBox(this.objRef,1); return false; }
                } else {
                    c[j].onchange = function () { toggleCheckBox(this.objRef,1); return false; }
                }
            }
        }
    }
});

function toggleCheckBox(imgObj,caller) {
    // if caller is 1, this method has been called from the onchange event of the checkbox, which means
    // the user has clicked the label element. Dont change the checked status of the checkbox in this instance
    // or we'll set it to the opposite of what the user wants. caller is 0 if coming from the onclick event of the image
    
    // reference to the form object
    formObj = d.forms[imgObj.frmIndex];
    // the name of the checkbox we're changing
    objName = imgObj.xid;
    // change the checked status of the checkbox if coming from the onclick of the image
    if(!caller)formObj.elements[objName].checked = !formObj.elements[objName].checked?true:false;
    // finally, update the image to reflect the current state of the checkbox.
    if(imgObj.src.indexOf(cboxOnImg)>-1) {
        imgObj.setAttribute("src",cboxOffImg);
        imgObj.setAttribute("title","click here to select this option.");
        imgObj.setAttribute("alt","click here to select this option.");
    } else {
        imgObj.setAttribute("src",cboxOnImg);
        imgObj.setAttribute("title","click here to deselect this option.");
        imgObj.setAttribute("alt","click here to deselect this option.");
    }
}

//addLoadEvent(createCustomCheckBoxes);
