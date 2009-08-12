/* See license.txt for terms of usage */

FBL.ns(function() { with (FBL) {

// ************************************************************************************************
// Constants

const Cc = Components.classes;
const Ci = Components.interfaces;
const nsIWebNavigation = Ci.nsIWebNavigation;
const nsIWebProgressListener = Ci.nsIWebProgressListener;
const nsIWebProgress = Ci.nsIWebProgress;
const nsISupportsWeakReference = Ci.nsISupportsWeakReference;
const nsISupports = Ci.nsISupports;
const nsIURI = Ci.nsIURI;

const NOTIFY_STATE_DOCUMENT = nsIWebProgress.NOTIFY_STATE_DOCUMENT;

const STATE_IS_WINDOW = nsIWebProgressListener.STATE_IS_WINDOW;
const STATE_IS_DOCUMENT = nsIWebProgressListener.STATE_IS_DOCUMENT;
const STATE_IS_REQUEST = nsIWebProgressListener.STATE_IS_REQUEST;

const STATE_START = nsIWebProgressListener.STATE_START;
const STATE_STOP = nsIWebProgressListener.STATE_STOP;
const STATE_TRANSFERRING = nsIWebProgressListener.STATE_TRANSFERRING;

const STOP_ALL = nsIWebNavigation.STOP_ALL;

const dummyURI = "about:layout-dummy-request";
const aboutBlank = "about:blank";

const observerService = CCSV("@joehewitt.com/firebug-http-observer;1", "nsIObserverService");

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

const tabBrowser = $("content");

// ************************************************************************************************
// Globals

var contexts = [];

// ************************************************************************************************

top.TabWatcher = extend(new Firebug.Listener(),
{
    // Store contexts where they can be accessed externally
    contexts: contexts,

    initialize: function()
    {
        if (tabBrowser)
            tabBrowser.addProgressListener(TabProgressListener, NOTIFY_STATE_DOCUMENT);

        observerService.addObserver(HttpObserver, "firebug-http-event", false);
    },

    destroy: function()
    {
        observerService.removeObserver(HttpObserver, "firebug-http-event");

        if (tabBrowser)
        {
            tabBrowser.removeProgressListener(TabProgressListener);

            for (var i = 0; i < tabBrowser.browsers.length; ++i)
            {
                var browser = tabBrowser.browsers[i];
                this.unwatchTopWindow(browser.contentWindow);
            }
        }
    },

    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

    /**
     * Called when tabBrowser browsers get a new location OR when we get a explicit user op to open firebug
     * Attaches to a top-level window. Creates context unless we just re-activated on an existing context
     */
    watchTopWindow: function(win, uri, userCommands)
    {
        if (!win)
        {
            return false;
        }

        var selectedBrowser = Firebug.chrome.getCurrentBrowser();
        if (selectedBrowser.cancelNextLoad)
        {
            // We need to cancel this load and try again after a delay... this is used
            // mainly to prevent chaos while when the debugger is active when a page
            // is unloaded
            delete selectedBrowser.cancelNextLoad;
            selectedBrowser.webNavigation.stop(STOP_ALL);
            var url = (uri instanceof nsIURI?uri.spec:uri);
            delayBrowserLoad(selectedBrowser, url);
            return;
        }

        var context = this.getContextByWindow(win);
        if (context) // then we've looked at this window before in this FF session
        {
            if (!this.shouldShowContext(context))
            {
                // ...but now it is not wanted.
                if (context.browser)
                    delete context.browser.showFirebug;
                this.unwatchContext(win, context);

                // There shouldn't be context for this window so, remove it from the
                // global array.
                remove(contexts, context);

                return;  // did not create a context
            }
            // else we should show
        }
        else // then we've not looked this window in this session
        {
            // decide whether this window will be debugged or not
            var url = (uri instanceof nsIURI) ? uri.spec : uri;
            if (!this.shouldCreateContext(tabBrowser.selectedBrowser, url, userCommands))
            {
                delete selectedBrowser.showFirebug;
                this.watchContext(win, null);

                return false;  // we did not create a context
            }

            var browser = this.getBrowserByWindow(win);

            context = this.createContext(win, browser, Firebug.TabContext);
       }

        if (win instanceof Ci.nsIDOMWindow && win.parent == win)
        {
            win.addEventListener("pageshow", onLoadWindowContent, onLoadWindowContent.capturing);
            win.addEventListener("DOMContentLoaded", onLoadWindowContent, onLoadWindowContent.capturing);
        }

        // Dispatch watchWindow for the outer most DOM window
        this.watchWindow(win, context);

        // This is one of two places that loaded is set. The other is in watchLoadedTopWindow
        if (context && !context.loaded)
        {
            context.loaded = !context.browser.webProgress.isLoadingDocument;

            // If the loaded flag is set, the proper event should be dispatched.
            if (context.loaded)
                dispatch(this.fbListeners, "loadedContext", [context]);

        }

        // Call showContext only for currently active tab.
        if (Firebug.chrome.getCurrentURI().spec != context.browser.currentURI.spec)
        {
            return context;  // we did create or find a context
        }

        if (context && !context.loaded && !context.showContextTimeout)
        {
            // still loading, we want to showContext one time but not too agressively
            context.showContextTimeout = setTimeout(bindFixed( function delayShowContext()
            {
                if (context.window)   // Sometimes context.window is not defined ?
                    this.watchContext(win, context);  // calls showContext
                else
                {
                }
            }, this), 400);
        }
        else
        {
            this.rushShowContext(win, context);
        }

        return context;  // we did create or find a context
    },

    rushShowContext: function(win, context)
    {
        if (context.showContextTimeout)
            clearTimeout(context.showContextTimeout);
        delete context.showContextTimeout;

        this.watchContext(win, context);  // calls showContext
    },

    // Listeners decide to show or not
    shouldShowContext: function(context)
    {
        if ( dispatch2(this.fbListeners, "shouldShowContext", [context]))
            return true;
        else
            return false;
    },

    // Listeners given force-in and veto on URIs/Window.

    shouldCreateContext: function(browser, url, userCommands)
    {
        // called when win has no context, answers the question: create one, true or false?

        // Create if any listener says true to showCreateContext
        if ( dispatch2(this.fbListeners, "shouldCreateContext", [browser, url, userCommands]) )
            return true;

        if ( dispatch2(this.fbListeners, "shouldNotCreateContext", [browser, url, userCommands]) )
            return false;

        return userCommands;
    },

    createContext: function(win, browser, contextType)
    {
        // If the page is reloaded, store the persisted state from the previous
        // page on the new context
        var persistedState = browser.persistedState;
        delete browser.persistedState;
        if (!persistedState || persistedState.location != win.location.href)
            persistedState = null;

        // The proper instance of FirebugChrome object (different for detached Firebug and
        // accessible as Firebug.chrome property) must be used for the context object.
        // (the global context object FirebugContext is also different for detached firebug).
        var context = new contextType(win, browser, Firebug.chrome, persistedState);
        contexts.push(context);

        context.uid = FBL.getUniqueId();

        browser.showFirebug = true; // this is the only place we should set showFirebug.

        dispatch(this.fbListeners, "initContext", [context, persistedState]);

        return context;
    },

    /**
     * Called once the document within a tab is completely loaded.
     */
    watchLoadedTopWindow: function(win)
    {
        var isSystem = isSystemPage(win);

        var context = this.getContextByWindow(win);
        if ((context && !context.window))
        {
            this.unwatchTopWindow(win);
            this.watchContext(win, null, isSystem);
            return;
        }

        if (context && !context.loaded)
        {
            context.loaded = true;

            dispatch(this.fbListeners, "loadedContext", [context]);

            if (context.showContextTimeout)
                this.rushShowContext(win, context);
        }
    },

    /**
     * Attaches to a window that may be either top-level or a frame within the page.
     */
    watchWindow: function(win, context)
    {
        if (!context)
            context = this.getContextByWindow(getRootWindow(win));

        var href = win.location.href;

        // Unfortunately, dummy requests that trigger the call to watchWindow
        // are called several times, so we have to avoid dispatching watchWindow
        // more than once
        if (context && context.windows.indexOf(win) == -1 && href != aboutBlank)
        {
            context.windows.push(win);

            if (win.parent == win)
            {
                win.addEventListener("pagehide", onPageHideTopWindow, false);
            }
            else
            {
                win.addEventListener("unload", onUnloadWindow, false);
            }

            dispatch(this.fbListeners, "watchWindow", [context, win]);

        }
    },

    /**
     * Detaches from a top-level window. Destroys context
     * Called when windows are closed, or user closes firebug
     */
    unwatchTopWindow: function(win)
    {
        var context = this.getContextByWindow(win);
        this.unwatchContext(win, context);

        return true; // we might later allow extensions to reject unwatch
    },

    /**
     * Detaches from a window, top-level or frame (interior)
     */
    unwatchWindow: function(win)
    {
        var context = this.getContextByWindow(win);

        var index = context ? context.windows.indexOf(win) : -1;
        if (index != -1)
        {
            context.windows.splice(index, 1);
            dispatch(this.fbListeners, "unwatchWindow", [context, win]);
        }
    },

    /**
     * Attaches to the window inside a browser because of user-activation
     * returns false if no context was created by the attach attempt, eg extension rejected page
     */
    watchBrowser: function(browser)
    {
        registerFrameListener(browser);

        var shouldDispatch = this.watchTopWindow(browser.contentWindow, safeGetURI(browser), true);

        if (shouldDispatch)
        {
            dispatch(this.fbListeners, "watchBrowser", [browser]);
            return true;
        }
        return false;
    },

    /*
     * User closes Firebug
     */

    unwatchBrowser: function(browser, userCommands)
    {
        if (!browser)
            return;

        delete browser.showFirebug;

        var shouldDispatch = this.unwatchTopWindow(browser.contentWindow);

        if (shouldDispatch)
        {
            dispatch(this.fbListeners, "unwatchBrowser", [browser, userCommands]);
            return true;
        }
        return false;
    },

    watchContext: function(win, context, isSystem)  // called when tabs change in firefox
    {
        var browser = context ? context.browser : this.getBrowserByWindow(win);
        if (browser)
            browser.isSystemPage = isSystem;

        dispatch(this.fbListeners, "showContext", [browser, context]); // context is null if we don't want to debug this browser
    },

    unwatchContext: function(win, context)
    {
        if (!context)
        {
            var browser = this.getBrowserByWindow(win);
            if (browser)
            {
                browser.persistedState = {};
                delete browser.showFirebug;
                dispatch(this.fbListeners, "showContext", [browser, null]); // context is null if we don't want to debug this browser
            }
            dispatch(this.fbListeners, "destroyContext", [null, (browser?browser.persistedState:null), browser]);
            return;
        }

        var persistedState = {location: context.window.location.href};
        context.browser.persistedState = persistedState;  // store our state on FF browser elt

        iterateWindows(context.window, function(win)
        {
            dispatch(TabWatcher.fbListeners, "unwatchWindow", [context, win]);
        });

        dispatch(this.fbListeners, "destroyContext", [context, persistedState, context.browser]);

        if (this.cancelNextLoad)
        {
            delete this.cancelNextLoad;
            context.browser.cancelNextLoad = true;
        }

        context.destroy(persistedState);
        remove(contexts, context);

        var currentBrowser = Firebug.chrome.getCurrentBrowser();
        if (!currentBrowser.showFirebug)  // unwatchContext can be called on an unload event after another tab is selected
            dispatch(this.fbListeners, "showContext", [browser, null]); // context is null if we don't want to debug this browser
    },

    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

    getContextByWindow: function(winIn)
    {
        if (!winIn)
            return;

        var rootWindow = getRootWindow(winIn);

        if (rootWindow)
        {
            for (var i = 0; i < contexts.length; ++i)
            {
                var context = contexts[i];
                if (context.window == rootWindow)
                    return context;
            }
        }
    },

    getContextBySandbox: function(sandbox)
    {
        for (var i = 0; i < contexts.length; ++i)
        {
            var context = contexts[i];
            if (context.sandboxes)
            {
                for (var iframe = 0; iframe < context.sandboxes.length; iframe++)
                {
                    if (context.sandboxes[iframe] == sandbox)
                        return context;
                }
            }
        }
        return null;
    },

    getBrowserByWindow: function(win)
    {
        for (var i = 0; i < tabBrowser.browsers.length; ++i)
        {
            var browser = tabBrowser.browsers[i];
            if (browser.contentWindow == win)
            {
                registerFrameListener(browser);
                return browser;
            }
        }

        return null;
    },

    iterateContexts: function(fn)
    {
        for (var i = 0; i < contexts.length; ++i)
            fn(contexts[i]);
    },
});

// ************************************************************************************************

var TabProgressListener = extend(BaseProgressListener,
{
    onLocationChange: function(progress, request, uri)
    {
        // Only watch windows that are their own parent - e.g. not frames
        if (progress.DOMWindow.parent == progress.DOMWindow)
        {
            var srcWindow = getWindowForRequest(request);
            var browser = srcWindow ? TabWatcher.getBrowserByWindow(srcWindow) : null;
            var requestFromFirebuggedWindow = browser && browser.showFirebug;

            if (uri)
                TabWatcher.watchTopWindow(progress.DOMWindow, uri);
            else // the location change to a non-uri means we need to hide
                TabWatcher.watchContext(progress.DOMWindow, null, true);
        }
    },

    onStateChange: function(progress, request, flag, status)
    {
    }
});

// ************************************************************************************************

var FrameProgressListener = extend(BaseProgressListener,
{
    onStateChange: function(progress, request, flag, status)
    {
        if (flag & STATE_IS_REQUEST && flag & STATE_START)
        {
            // We need to get the hook in as soon as the new DOMWindow is created, but before
            // it starts executing any scripts in the page.  After lengthy analysis, it seems
            // that the start of these "dummy" requests is the only state that works.

            var safeName = safeGetName(request);
            if (safeName && ((safeName == dummyURI) || safeName == "about:document-onload-blocker") )
            {
                var win = progress.DOMWindow;
                // Another weird edge case here - when opening a new tab with about:blank,
                // "unload" is dispatched to the document, but onLocationChange is not called
                // again, so we have to call watchTopWindow here

                if (win.parent == win && (win.location.href == "about:blank"))
                {
                    TabWatcher.watchTopWindow(win, win.location.href);
                    return;
                }
                else
                    TabWatcher.watchWindow(win);
            }
        }

        // Later I discovered that XHTML documents don't dispatch the dummy requests, so this
        // is our best shot here at hooking them.
        if (flag & STATE_IS_DOCUMENT && flag & STATE_TRANSFERRING)
        {
            TabWatcher.watchWindow(progress.DOMWindow);
            return;
        }

    }
});

// Registers frame listener for specified tab browser.
function registerFrameListener(browser)
{
    if (browser.frameListener)
        return;

    browser.frameListener = FrameProgressListener;  // just a mark saying we've registered. TODO remove!
    browser.addProgressListener(FrameProgressListener, NOTIFY_STATE_DOCUMENT);

}

function getRefererHeader(request)
{
    var http = QI(request, Ci.nsIHttpChannel);
    var referer = null;
    http.visitRequestHeaders({
        visitHeader: function(name, value)
        {
            if (name == 'referer')
                referer = value;
        }
    });
    return referer;
}

var HttpObserver = extend(Object,
{
    // nsIObserver
    observe: function(aSubject, aTopic, aData)
    {
        try
        {
            if (aTopic == "http-on-modify-request")
            {
                aSubject = aSubject.QueryInterface(Ci.nsIHttpChannel);
                this.onModifyRequest(aSubject);
            }
        }
        catch (err)
        {
            ERROR(err);
        }
    },

    onModifyRequest: function(request)
    {
        var win = getWindowForRequest(request);
        var tabId = Firebug.getTabIdForWindow(win);

        // Tab watcher is only interested in tab related requests.
        if (!tabId)
            return;

        // Ignore redirects
        if (request.URI.spec != request.originalURI.spec)
            return;

        // A document request for the specified tab is here. It can be a top window
        // request (win == win.parent) or embedded iframe request.
        if (request.loadFlags & Ci.nsIHttpChannel.LOAD_DOCUMENT_URI)
        {
            if ( (FBTrace.DBG_ACTIVATION || FBTrace.DBG_WINDOWS) && win == win.parent)
            {
                FBTrace.sysout("-> tabWatcher HttpObserver *** START *** " +
                    "document request for: " + request.URI.spec + " window for request is "+win.location+"\n");
            }

            if (win == win.parent)
            {
                // Make sure the frame listener is registered for top level window so,
                // we can get all onStateChange events and init context for all opened tabs.
                var browser = TabWatcher.getBrowserByWindow(win);

                delete browser.FirebugLink;

                if (win.location.toString() == "about:blank") // then this page is opened in new tab or window
                {
                    var referer = getRefererHeader(request);
                    if (referer)
                    {
                        try
                        {
                            var srcURI = makeURI(referer);
                            browser.FirebugLink = {src: srcURI, dst: request.URI};
                        }
                        catch(e)
                        {
                        }
                    }
                }
                else
                {
                    // Here we know the source of the request is 'win'. For viral activation and web app tracking
                    browser.FirebugLink = {src: browser.currentURI, dst: request.URI};
                }
            }
        }
    },

    QueryInterface : function (aIID)
    {
        if (aIID.equals(Ci.nsIObserver) ||
            aIID.equals(Ci.nsISupportsWeakReference) ||
            aIID.equals(Ci.nsISupports))
        {
            return this;
        }

        throw Components.results.NS_NOINTERFACE;
    }
});

// ************************************************************************************************
// Local Helpers

function onPageHideTopWindow(event)
{
    var win = event.currentTarget;  // we set the handler on a window
    var doc = event.target; // the pagehide is sent to the document.
    if (doc.defaultView != win)
        return; // ignore page hides on interior windows

    win.removeEventListener("pagehide", onPageHideTopWindow, false);

    // http://developer.mozilla.org/en/docs/Using_Firefox_1.5_caching#pagehide_event
    if (event.persisted) // then the page is cached and there cannot be an unload handler
    {
        //  see Bug 484710 -  add pageIgnore event for pages that are ejected from the bfcache

        TabWatcher.unwatchTopWindow(win);
    }
    else
    {
        // Page is not cached, there may be an unload
        win.addEventListener("unload", onUnloadTopWindow, true);
    }
}

function onUnloadTopWindow(event)
{
    var win = event.currentTarget;
    win.removeEventListener("unload", onUnloadTopWindow, true);
    TabWatcher.unwatchTopWindow(win);
}

function onLoadWindowContent(event)
{
    var win = event.currentTarget;
    try
    {
        win.removeEventListener("pageshow", onLoadWindowContent, onLoadWindowContent.capturing);
    }
    catch (exc)
    {
    }

    try
    {
        win.removeEventListener("DOMContentLoaded", onLoadWindowContent, onLoadWindowContent.capturing);
    }
    catch (exc)
    {
    }

    // Signal that we got the onLoadWindowContent event. This prevents the FrameProgressListener from sending it.
    var context = TabWatcher.getContextByWindow(win);
    if (context)
        context.onLoadWindowContent = true;

    try
    {
        TabWatcher.watchLoadedTopWindow(win);
    }
    catch(exc)
    {
    }

}
onLoadWindowContent.capturing = false;

function onUnloadWindow(event)
{
    var win = event.currentTarget;
    var eventType = "unload";
    win.removeEventListener(eventType, onUnloadWindow, false);
    TabWatcher.unwatchWindow(win);
}

function delayBrowserLoad(browser, uri)
{
    setTimeout(function delayBrowserLoad100()
    {
        browser.loadURI(uri);
    }, 100);
}

function safeGetName(request)
{
    try
    {
        return request.name;
    }
    catch (exc)
    {
        return null;
    }
}

function safeGetURI(browser)
{
    try
    {
        return browser.currentURI;
    }
    catch (exc)
    {
        return null;
    }
}

// ************************************************************************************************

}});
