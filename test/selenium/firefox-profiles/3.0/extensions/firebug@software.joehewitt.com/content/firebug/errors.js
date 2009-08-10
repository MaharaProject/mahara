/* See license.txt for terms of usage */

FBL.ns(function() { with (FBL) {

// ************************************************************************************************
// Constants

const Cc = Components.classes;
const Ci = Components.interfaces;
const nsIScriptError = Ci.nsIScriptError;

const WARNING_FLAG = nsIScriptError.warningFlag;

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

const urlRe = new RegExp("([^:]*):(//)?([^/]*)");
const reUncaught = /uncaught exception/;
const reException = /uncaught exception:\s\[Exception...\s\"([^\"]*)\".*nsresult:.*\(([^\)]*)\).*location:\s\"([^\s]*)\sLine:\s(\d*)\"/;
const statusBar = $("fbStatusBar");
const statusText = $("fbStatusText");

const pointlessErrors =
{
    "uncaught exception: Permission denied to call method Location.toString": 1,
    "uncaught exception: Permission denied to get property Window.writeDebug": 1,
    "uncaught exception: Permission denied to get property XULElement.accessKey": 1,
    "this.docShell has no properties": 1,
    "aDocShell.QueryInterface(Components.interfaces.nsIWebNavigation).currentURI has no properties": 1,
    "Deprecated property window.title used. Please use document.title instead.": 1,
    "Key event not available on GTK2:": 1
};

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

const fbs = Cc["@joehewitt.com/firebug;1"].getService().wrappedJSObject;
const consoleService = CCSV("@mozilla.org/consoleservice;1", "nsIConsoleService");

// ************************************************************************************************

var Errors = Firebug.Errors = extend(Firebug.Module,
{
    clear: function(context)
    {
        this.setCount(context, 0)
    },

    increaseCount: function(context)
    {
        this.setCount(context, context.errorCount + 1)
    },

    setCount: function(context, count)
    {
        context.errorCount = count;

        if (context == FirebugContext)
            this.showCount(context.errorCount);
    },

    showMessageOnStatusBar: function(error)
    {
        if (statusText && statusBar && Firebug.breakOnErrors && error.message &&  !(error.flags & WARNING_FLAG))  // sometimes statusText is undefined..how?
        {
            statusText.setAttribute("value", error.message);
            statusBar.setAttribute("errors", "true");
        }
    },

    showCount: function(errorCount)
    {
        if (!statusBar)
            return;

        if (errorCount)
        {
            if (Firebug.showErrorCount)
            {
                var errorLabel = errorCount > 1
                    ? $STRF("ErrorsCount", [errorCount])
                    : $STRF("ErrorCount", [errorCount]);

                statusText.setAttribute("value", errorLabel);
            }

            statusBar.setAttribute("errors", "true");
        }
        else
        {
            statusText.setAttribute("value", "");
            statusBar.removeAttribute("errors");
        }
    },

    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    // Called by Console

    startObserving: function()
    {
        var errorsOn = $('fbStatusIcon').getAttribute("errors"); // signal user and be a marker.
        if (!errorsOn) // need to be safe to multiple calls
        {
            consoleService.registerListener(this);
            $('fbStatusIcon').setAttribute("errors", "on");
        }
    },

    stopObserving: function()
    {
        var errorsOn = $('fbStatusIcon').getAttribute("errors");
        if (errorsOn)  // need to be safe to multiple calls
        {
            try
            {
                consoleService.unregisterListener(this);
                $('fbStatusIcon').removeAttribute("errors");
            }
            catch (e)
            {
            }
        }
    },

    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    // extends consoleListener

    observe: function(object)
    {
        try
        {
            if (!FBTrace)
                return;
        }
        catch(exc)
        {
            return;
        }
        
        try
        {
            var context = null;
            if (FirebugContext)
                context = FirebugContext;

            if (object instanceof nsIScriptError)
            {
                var isWarning = object.flags & WARNING_FLAG;  // This cannot be pulled in front of the instanceof
                context = this.logScriptError(context, object, isWarning)
                if(!context)
                    return;
            }
            else
            {
                var isWarning = object.flags & WARNING_FLAG;
                if (Firebug.showChromeMessages)
                {
                    if (lessTalkMoreAction(context, object, isWarning))
                        return;
                    if (context) // Must be an nsIConsoleMessage
                        Firebug.Console.log(object.message, context, "consoleMessage", FirebugReps.Text);
                    else
                    {
                        return;
                    }
                }
                else
                {
                    return;
                }
            }
        }
        catch (exc)
        {
            // Errors prior to console init will come out here, eg error message from Firefox startup jjb.
        }
    },

    logScriptError: function(context, object, isWarning)
    {
        var category = getBaseCategory(object.category);
        var isJSError = category == "js" && !isWarning;

        var errorContext = getErrorContext(object);
        if (errorContext)
            context = errorContext;

        if (Firebug.showStackTrace && Firebug.errorStackTrace)
        {
            var trace = Firebug.errorStackTrace;
            trace = this.correctLineNumbersWithStack(trace, object) ? trace : null;
        }
        else if (checkForUncaughtException(context, object))
        {
            context = getExceptionContext(context);
            correctLineNumbersOnExceptions(context, object);
        }

        if (lessTalkMoreAction(context, object, isWarning))
            return null;

        Firebug.errorStackTrace = null;  // clear global: either we copied it or we don't use it.
        context.thrownStackTrace = null;

        if (!isWarning)
            this.increaseCount(context);

        var error = new ErrorMessage(object.errorMessage, object.sourceName,
            object.lineNumber, object.sourceLine, category, context, trace);  // the sourceLine will cause the source to be loaded.

        var className = isWarning ? "warningMessage" : "errorMessage";

        if (context)
        {
             // then report later to avoid loading sourceS
            context.throttle(Firebug.Console.log, Firebug.Console, [error, context,  className, false, true], true);
        }
        else
        {
            Firebug.Console.log(error, context,  className);
        }
        return context;
    },

    correctLineNumbersWithStack: function(trace, object)
    {
        var stack_frame = trace.frames[0];
        if (stack_frame)
        {
            sourceName = stack_frame.href;
            lineNumber = stack_frame.lineNo;
            // XXXjjb Seems to change the message seen in Firefox Error Console
            var correctedError = object.init(object.errorMessage, sourceName, object.sourceLine,lineNumber, object.columnNumber, object.flags, object.category);
            return true;
        }
        return false;
    },
    
    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    // extends Module

    initContext: function(context)
    {
        context.errorCount = 0;
    },

    showContext: function(browser, context)
    {
        this.showCount(context ? context.errorCount : 0);
    }
});

// ************************************************************************************************
// Local Helpers

const categoryMap =
{
    "javascript": "js",
    "JavaScript": "js",
    "DOM": "js",
    "Events": "js",
    "CSS": "css",
    "XML": "xml",
    "malformed-xml": "xml"
};

function getBaseCategory(categories)
{
    var categoryList = categories.split(" ");
    for (var i = 0 ; i < categoryList.length; ++i)
    {
        var category = categoryList[i];
        if ( categoryMap.hasOwnProperty(category) )
            return categoryMap[category];
    }
}

function isShownCategory(url, category, isWarning)
{
    var m = urlRe.exec(url);
    var errorScheme = m ? m[1] : "";
    if (errorScheme == "javascript")
        return true;

    var isChrome = false;

    if (!category)
        return Firebug.showChromeErrors;

    var categories = category.split(" ");
    for (var i = 0 ; i < categories.length; ++i)
    {
        var category = categories[i];
        if (category == "CSS" && !Firebug.showCSSErrors)
            return false;
        else if ((category == "XML" || category == "malformed-xml" ) && !Firebug.showXMLErrors)
            return false;
        else if ((category == "javascript" || category == "JavaScript" || category == "DOM")
                    && !isWarning && !Firebug.showJSErrors)
            return false;
        else if ((category == "javascript" || category == "JavaScript" || category == "DOM")
                    && isWarning && !Firebug.showJSWarnings)
            return false;
        else if (errorScheme == "chrome" || category == "XUL" || category == "chrome" || category == "XBL"
                || category == "component")
            isChrome = true;
    }

    if ((isChrome && !Firebug.showChromeErrors))
        return false;

    return true;
}

function domainFilter(url)  // never called?
{
    if (Firebug.showExternalErrors)
        return true;

    var browserWin = document.getElementById("content").contentWindow;

    var m = urlRe.exec(browserWin.location.href);
    if (!m)
        return false;

    var browserDomain = m[3];

    m = urlRe.exec(url);
    if (!m)
        return false;

    var errorScheme = m[1];
    var errorDomain = m[3];

    return errorScheme == "javascript"
        || errorScheme == "chrome"
        || errorDomain == browserDomain;
}

function lessTalkMoreAction(context, object, isWarning)
{
    if (!context || !isShownCategory(object.sourceName, object.category, isWarning))
    {
        return true;
    }

    var enabled = Firebug.Console.isEnabled(context);
    if (!enabled) {
        FBTrace.sysout("errors.observe not enabled for context "+(context.window?context.window.location:"no window")+"\n");
        return true;
    }

    var incoming_message = object.errorMessage;  // nsIScriptError
    if (!incoming_message)                       // nsIConsoleMessage
        incoming_message = object.message;


    for (var msg in pointlessErrors)
    {

        if( msg.charAt(0) == incoming_message.charAt(0) )
        {
            if (incoming_message.indexOf(msg) == 0)
            {
                return true;
            }
        }
    }

    var msgId = [incoming_message, object.sourceName, object.lineNumber].join("/");
    if (context.errorMap && msgId in context.errorMap)
    {
        context.errorMap[msgId] += 1;
        return true;
    }

    if (!context.errorMap)
        context.errorMap = {};

    context.errorMap[msgId] = 1;

    return false;
}

function getErrorContext(object)
{
    var errorContext = null;

    var url = object.sourceName;

    TabWatcher.iterateContexts(
        function findContextByURL(context)
        {
            if (!context.window || !context.window.location)
                return;

            if (context.window.location.toString() == url)
                return errorContext = context;
            else
            {
                if (context.sourceFileMap && context.sourceFileMap[url])
                    return errorContext = context;
            }
        }
    );

    return errorContext; // we looked everywhere...
}

function checkForUncaughtException(context, object)
{
    if (object.flags & object.exceptionFlag)
    {
        if (reUncaught.test(object.errorMessage))
        {
            if (context.thrownStackTrace)
            {
                Firebug.errorStackTrace = context.thrownStackTrace;
            }
            else
            {
            }
            return true;
        }
        else
        {
        }
    }
    return false;
}

function getExceptionContext(context)
{
    var errorWin = fbs.lastErrorWindow;  // not available unless Script panel is enabled.
    if (errorWin)
    {
        var errorContext = TabWatcher.getContextByWindow(errorWin);
        return errorContext;
    }
    return context;
}

function correctLineNumbersOnExceptions(context, object)
{
    var m = reException.exec(object.errorMessage);
    if (m)
    {
        var exception = m[1];
        if (exception)
            errorMessage = "uncaught exception: "+exception;
        var nsresult = m[2];
        if (nsresult)
            errorMessage += " ("+nsresult+")";
        sourceName = m[3];
        lineNumber = m[4];

        var correctedError = object.init(errorMessage, sourceName, object.sourceLine, lineNumber, object.columnNumber, object.flags, object.category);
    }
}

// ************************************************************************************************

Firebug.registerModule(Errors);

// ************************************************************************************************

}});
