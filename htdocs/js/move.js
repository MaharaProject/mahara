/**
 * Javascript for moving things around with drag and drop
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

var MoveSources = {
    sources: [],
    register: function(movesource) {
        if (this.sources.length === 0) {
            var conn = MochiKit.Signal.connect;
            this.eventmouseclick = conn(document, 'onclick', this, this.onClick);
            this.eventKeypress = conn(document, 'onkeypress', this, this.keyPress);
        }
        this.sources.push(movesource);
    },
    activate: function(movesource) {
        if (this.activeSource) {
            this.activeSource.cancelMove();
        }
        this.activeSource = movesource;
    },
    deactivate: function(movesource) {
        this.activeSource = null;
    },
    onClick: function(e) {
        if (this.activeSource) {
            this.activeSource.cancelMove();
        }
    },
    keyPress: function(e) {
    }
};

var MoveSource = function(element, options) {
    var self = this;
    var d = MochiKit.DOM;

    self.element = d.getElement(element);
    self.element.moveSource = self;
    self.selectedClass = options.selectedClass;
    self.acceptData = options.acceptData;

    self.startMove = function(e) {
        MochiKit.Signal.disconnect(self.onClick);
        MochiKit.DOM.addElementClass(self.element, self.selectedClass);
        MoveSources.activate(self);
        MoveTargets.prepare(self);
        self.onClick = MochiKit.Signal.connect(self.element, 'onclick', self, self.cancelMove);

        if (e) {
            e.stop();
        }
    }

    self.cancelMove = function(e) {
        MochiKit.Signal.disconnect(self.onClick);
        MochiKit.DOM.removeElementClass(self.element, self.selectedClass);
        MoveSources.deactivate(self);
        MoveTargets.reset(self);
        self.onClick = MochiKit.Signal.connect(self.element, 'onclick', self, self.startMove);

        if (e) {
            e.stop();
        }
    }

    self.onClick = MochiKit.Signal.connect(self.element, 'onclick', self, self.startMove);
    MoveSources.register(self);
};

var MoveTargets = {
    targets: [],
    register: function(movetarget) {
        this.targets.push(movetarget);
    },
    prepare: function(movesource) {
        MochiKit.Base.map(function (movetarget) {
            if (movetarget.accepts(movesource)) {
                movetarget.startAccept();
            }
        }, this.targets);
    },
    reset: function(movesource) {
        MochiKit.Base.map(function (movetarget) {
            if (movetarget.accepts(movesource)) {
                movetarget.stopAccept();
            }
        }, this.targets);
    }
};

var MoveTarget = function(element,options) {
    var self = this;
    var d = MochiKit.DOM;

    self.element = d.getElement(element);
    self.element.moveTarget = self;
    self.activeClass = options.activeClass;
    self.hoverClass = options.hoverClass;
    self.ondrop = options.ondrop;
    self.acceptData = options.acceptData;
    self.acceptFunction = options.acceptFunction;

    self.accepts = function(movesource) {
        if (typeof(self.acceptFunction) == 'function') {
            return self.acceptFunction(movesource.acceptData, self.acceptData);
        }

        return true;
    };

    self.onMove = function(e) {
        if (typeof(self.ondrop) == 'function') {
            self.ondrop(MoveSources.activeSource.element, self.element);
        }
    };

    self.startAccept = function() {
        var d = MochiKit.DOM;
        var s = MochiKit.Signal;

        d.addElementClass(self.element, self.activeClass);
        self.mouseOver = s.connect(self.element, 'onmouseover', function () { d.addElementClass(self.element, self.hoverClass); } );
        self.mouseOut  = s.connect(self.element, 'onmouseout', function () { d.removeElementClass(self.element, self.hoverClass); } );
        self.onClick   = s.connect(self.element, 'onclick', self, self.onMove);
    };

    self.stopAccept = function() {
        MochiKit.DOM.removeElementClass(self.element, self.activeClass);
        MochiKit.DOM.removeElementClass(self.element, self.hoverClass);
        MochiKit.Signal.disconnect(self.mouseOver);
        MochiKit.Signal.disconnect(self.mouseOut);
        MochiKit.Signal.disconnect(self.onClick);
    };

    MoveTargets.register(self);
};
