/**
 *
 * @package    mahara
 * @subpackage artefact-module
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

class UrlFlashback {
    constructor(newEntry) {
        newEntry = (typeof newEntry === 'undefined' ? true : newEntry !== false);
        this.currentUrl = document.location.href;
        this.previousUrl = document.referrer;
        this.options = {
            entryLivespan: 86400,
            cookiePath: UrlFlashback.getPath(config.wwwroot),
            destinationUrlHasGetAndPost: true,
            browserBackIsActive: true,
            i18n: function(string) {
                return get_string(string, 'module.submissions');
            },
        };

        if (newEntry) {
            this.sourceUrl = this.currentUrl;
            this.sourceUrlReferrer = this.previousUrl;
            this.destinationUrls = [];
            this.flashbackUrl = null;
            this.data = null;
        }
        else {
            this.readEntry();
        }
    }

    // region instance methods
    static createNewInstance(destinationUrls, flashbackUrl, data, options) {
        let newInstance = new UrlFlashback(true);

        if (Array.isArray(destinationUrls)) {
            newInstance.destinationUrls = destinationUrls;
        }
        if (flashbackUrl) {
            newInstance.flashbackUrl = flashbackUrl;
        }
        if (data) {
            newInstance.data = data;
        }
        if (options) {
            newInstance.options = options;
        }

        return newInstance;
    }

    static createInstanceFromEntry() {
        return new UrlFlashback(false);
    }

    static createInstanceFromEntryAndRemoveEntry() {
        let instance = UrlFlashback.createInstanceFromEntry();
        instance.removeEntry();

        return instance;
    }
    // endregion

    // region info/validation methods
    isValid() {
        switch (true) {
            // In frontend we unfortunately don't have the info about the request method, so we have 2 valid previous URLs
            // case this.currentUrlIsDestination() && (this.previousUrl === this.sourceUrl || this.previousUrl === this.destinationUrls):
            case this.currentUrlIsDestination():
                if (this.options.destinationUrlHasGetAndPost) {
                    return this.previousUrl === this.sourceUrl || this.urlIsDestination(this.previousUrl);
                }
                let previousUrlIsDestination = false;
                if (this.destinationUrls.length > 1) {
                    previousUrlIsDestination = this.urlIsDestination(this.previousUrl);
                }
                return this.previousUrl === this.sourceUrl || previousUrlIsDestination;

            case this.currentUrlIsFlashback() && this.sourceUrl !== this.flashbackUrl:
                return this.urlIsDestination(this.previousUrl);

            // If sourceUrl is flashbackUrl then we also have to consider the browser back button for validation
            case this.currentUrlIsFlashback() && this.sourceUrl === this.flashbackUrl:
                return this.urlIsDestination(this.previousUrl) || (this.options.browserBackIsActive ? this.previousUrl === this.sourceUrlReferrer : false);
        }
        return false;
    }

    isValidOrRemoveEntry() {
        let isValid = this.isValid();

        if (!isValid) {
            this.removeEntry();
        }

        return isValid;
    }

    urlIsDestination(url) {
        return Array.isArray(this.destinationUrls) && this.destinationUrls.includes(url);
    }

    currentUrlIsSource() {
        return this.currentUrl === this.sourceUrl;
    }

    currentUrlIsDestination() {
        return this.urlIsDestination(this.currentUrl);
    }

    currentUrlIsFlashback() {
        return this.currentUrl === this.flashbackUrl;
    }
    // endregion

    // region entry methods
    // region Cookie Tools
    createCookie(name, value, liveSpanSeconds) {
        let expires = '';
        if (liveSpanSeconds) {
            let date = new Date();
            date.setTime(date.getTime() + (liveSpanSeconds * 1000));
            // expires = '; expires=' + date.toGMTString();
            expires = '; expires=' + date.toUTCString();
        }
        let sameSite = '; samesite=strict; Secure';
        let path = '; path=' + this.options.cookiePath;
        document.cookie = name + "=" + value + expires + sameSite + path;
    }

    // Slightly modified from
    // https://stackoverflow.com/questions/5639346/what-is-the-shortest-function-for-reading-a-cookie-by-name-in-javascript
    readCookie(name) {
        let cookie = document.cookie.match('(^|[^;]+)\\s*' + name + '\\s*=\\s*([^;]+)');
        return cookie ? cookie.pop() : null;
    }

    eraseCookie(name) {
        this.createCookie(name, '', -1, this.options.cookiePath);
    }

    static getPath(href) {
        let url = new URL(href);
        return url.pathname;
    }
    // endregion

    static entryExists() {
        return (UrlFlashback.createInstanceFromEntry().flashbackUrl !== undefined);
    }

    confirmProceedWithRestrictions() {
        let existingFlashback = UrlFlashback.createInstanceFromEntry();

        if (existingFlashback) {
            if (this.sourceUrl === existingFlashback.sourceUrl) {
                if (confirm(this.options.i18n('ProceedWithBackRestrictionsToExistingTab'))) {
                    existingFlashback.options.browserBackIsActive = false;
                    existingFlashback.addOrUpdateEntry();
                    return true;
                }
                return false;
            }
            return confirm(this.options.i18n('ProceedWithoutFlashbackFunctionality'));
        }
        return true;
    }

    addEntry() {
        if (UrlFlashback.entryExists()) {
            return this.confirmProceedWithRestrictions();
        }
        return this.addOrUpdateEntry();
    }

    addOrUpdateEntry() {
        if (!Array.isArray(this.destinationUrls) || this.destinationUrls.length === 0) {
            throw new Error('Property destinationURLs is not set or has the wrong type.');
        }

        if (this.flashbackUrl === null) {
            this.flashbackUrl = this.sourceUrl;
        }

        let flashbackData = {
            sourceUrl: this.sourceUrl,
            destinationUrls: this.destinationUrls,
            flashbackUrl: this.flashbackUrl,
            sourceUrlReferrer: this.sourceUrlReferrer,
            data: this.data,
            options: this.options
        };

        sessionStorage.setItem('UrlFlashback', JSON.stringify(flashbackData));
        this.createCookie('UrlFlashback', JSON.stringify(flashbackData), this.options.entryLivespan);

        return true;
    }

    removeEntry() {
        sessionStorage.removeItem('UrlFlashback');
        this.eraseCookie('UrlFlashback');
    }

    readEntry() {
        let flashbackEntry = null;
        try {
            flashbackEntry = JSON.parse(sessionStorage.getItem('UrlFlashback'));
        }
        catch (e) {}

        if (flashbackEntry !== null) {
            this.sourceUrl = flashbackEntry.sourceUrl;
            this.destinationUrls = flashbackEntry.destinationUrls;
            this.flashbackUrl = flashbackEntry.flashbackUrl;
            this.sourceUrlReferrer = flashbackEntry.sourceUrlReferrer;
            this.data = flashbackEntry.data;
            this.options = flashbackEntry.options;
        }
    }
    // endregion
}