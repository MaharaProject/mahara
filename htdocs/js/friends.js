/**
 * Helper functionality for the friends section
 *
 * Author: Nigel McNie
 */
var wwwroot    = config['wwwroot'];
var profileurl = wwwroot + 'thumb.php?type=profileicon&size=40x40&id=';
var viewurl    = wwwroot + 'user/view.php?id=';

function addFriend(id) {
    var pd = {'id': id, 'control': 1, 'filter': 0, 'type': 'add'};
    sendjsonrequest('index.json.php', pd, 'POST', function() { searchresults.doupdate(); });
}

function removeFriend(id) {
    var pd = {'id': id, 'control': 1, 'filter': 0, 'type': 'remove'};

    if (!confirm(get_string('confirmremovefriend'))) {
        return false;
    }

    sendjsonrequest('index.json.php', pd, 'POST', function() { friendslist.doupdate(); });
};

function sendMessage(id, link, tableRenderer) {
    friendRequestWithMessage(id, link, 'message', tableRenderer);
}

function approveFriend(id, tableRenderer) {
    var pd = {'id': id, 'control': 1, 'filter': 0, 'type': 'accept'};
    sendjsonrequest('index.json.php', pd, 'POST', function() { tableRenderer.doupdate(); });
    decrementPendingFriends();
}

function disallowFriend(id, link) {
    friendRequestWithMessage(id, link, 'disallow', friendslist);
}

function decrementPendingFriends() {
    var oldcount = parseInt($('pendingfriendscount').innerHTML);
    var newcount = oldcount - 1;
    var messagenode = $('pendingfriendsmessage');
    if (newcount == 1) { // jump through hoops to change between plural and singular
        messagenode.innerHTML = get_string('pendingfriend');
    }
    else {
        messagenode.innerHTML = get_string('pendingfriends');
    }
    $('pendingfriendscount').innerHTML = newcount;
}

function sendFriendRequest(id, link) {
    friendRequestWithMessage(id, link, 'request', searchresults);
}

function friendRequestWithMessage(id, link, type, tableRenderer) {
    // If the reason box is already open for this friend and the disallow link 
    // is clicked again, make it act like a toggle and close the box
    var currentContainer = $('dc' + id + '_' + type);
    if (currentContainer) {
        toggleElementClass('hidden', currentContainer);
        return false;
    }

    var title = '';
    var buttonText = '';
    if (type == 'message') {
        title = buttonText = get_string('sendmessage');
    }
    else if (type == 'request') {
        title = get_string('reason');
        buttonText = get_string('requestfriendship');
    }
    else if (type == 'disallow') {
        title = get_string('reason');
        buttonText = get_string('denyrequest');
    }

    var submitButton = INPUT({'type': 'submit', 'class': 'submit', 'value': buttonText});
    connect(submitButton, 'onclick', function(e) {
        var pd;
        if (type == 'disallow') {
            // type accept due to wierdness in friend_submit caused by form wierdness in lib/user.php
            pd = {'id': id, 'control': 1, 'filter': 0, 'type': 'accept', 'rejectsubmit': 'reject', 'rejectreason': $(type + '_message_' + id).value};
            decrementPendingFriends();
        }
        else if (type == 'message') {
            pd = {'id': id, 'control': 1, 'filter': 0, 'type': 'message', 'message': $(type + '_message_' + id).value};
        }
        else if (type == 'request') {
            pd = {'id': id, 'control': 1, 'filter': 0, 'type': 'request', 'reason': $(type + '_message_' + id).value};
        }
        sendjsonrequest('index.json.php', pd, 'POST', function() { tableRenderer.doupdate(); });
    });
        
    var cancelButton = INPUT({'type': 'submit', 'class': 'submit', 'value': get_string('cancel')});
    connect(cancelButton, 'onclick', function(e) {
        toggleElementClass('hidden', 'dc' + id + '_' + type);
        e.stop();
    });

    var container = $('friendinfo_' + id);
    appendChildNodes(container,
        DIV({'id': 'dc' + id + '_' + type},
            TABLE(null,
                TR(null,
                    TH(null, title)
                ),
                TR(null,
                    TD(null, TEXTAREA({'class': 'textarea fullwidth', 'id': type + '_message_' + id, 'rows': 5, 'cols': 40}))
                ),
                TR(null,
                    TD(null,
                        submitButton,
                        cancelButton
                    )
                )
            )
        )
    );
}

var friendslist = new TableRenderer(
    'friendslist',
    'index.json.php',
    [
         function (r, d) {
            var introduction = null;
            if (r.introduction != null) {
                introduction = P(null, createDOM('EM', null, '"' + r.introduction + '"'));
            }

            var views = null;
            var viewnote = null;
            if (typeof(d.views) == 'object' && d.views[r.id] && d.numviews[r.id] > 0) {
                views = UL();
                forEach(d.views[r.id], function(i) {
                    appendChildNodes(views, LI(null, A({'href': config['wwwroot'] + 'view/view.php?id=' + i.id}, i.title)));
                });

                if (d.views[r.id].length < d.numviews[r.id]) {
                    viewnote = P({'style': 'font-size: smaller;'}, createDOM('EM', null, A({'href': viewurl + r.id}, get_string('seeallviews', d.numviews[r.id]))));
                }
            }
            else {
                views = P(null, get_string('noviewstosee'));
            }

            var sendMessageLink = null;
            var sendMessageLI = null;
            if (r.messages == 1) {
                sendMessageLink = A({'href': ''}, get_string('sendmessage'));
                connect(sendMessageLink, 'onclick', function(e) {
                    sendMessage(r.id, sendMessageLink, friendslist);
                    e.stop();
                });
                sendMessageLI = LI(null, sendMessageLink);
            }

            if (r.pending == 1) {
                var reason = null;
                if (r.reason != '') {
                    reasonContainerDiv = DIV();
                    reasonContainerDiv.innerHTML = r.reason;
                    reason = DIV({'class': 'pending'}, DIV(null, STRONG(null, get_string('whymakemeyourfriend'))), reasonContainerDiv);
                }

                // Links for friend actions
                var approveFriendLink = BUTTON({'type': 'button'}, STRONG(get_string('approverequest')));
                connect(approveFriendLink, 'onclick', function(e) {
                    approveFriend(r.id, friendslist);
                    e.stop();
                });
                var disallowFriendLink = A({'href': ''}, get_string('denyrequest'));
                connect(disallowFriendLink, 'onclick', function(e) {
                    disallowFriend(r.id, disallowFriendLink);
                    e.stop();
                });

                return TD({'class': 'pending'},
                    DIV({'class': 'fl'},
                        IMG({'src': profileurl + r.id})
                    ),
                    TABLE({'class': 'friendinfo'},
                        TR(null,
                            TH(null,
                                DIV({'class': 'fr'}, approveFriendLink),
                                H3(null, A({'href': viewurl + r.id}, r.name), ' - ', get_string('pending')))
                        ),
                        TR(null,
                            TD({'id': 'friendinfo_' + r.id},
                                introduction,
                                reason,
                                UL(null,
                                    LI(null, disallowFriendLink),
                                    sendMessageLI
                                )
                            )
                        )
                    )
                );
            }
            else {
                var removeLink = A({'href': ''}, get_string('removefromfriendslist'));
                connect(removeLink, 'onclick', function(e) {
                    removeFriend(r.id);
                    e.stop();
                });

                return TD(null,
                    DIV({'class': 'fl'},
                        IMG({'src': profileurl + r.id})
                    ),
                    TABLE({'class': 'friendinfo'},
                        TR(null,
                            TH(null, H3(null, A({'href': viewurl + r.id}, r.name))),
                            TD({'rowspan': 2, 'class': 'viewlist'},
                                H3(null, get_string('views')),
                                views,
                                viewnote
                            )
                        ),
                        TR(null,
                            TD({'id': 'friendinfo_' + r.id},
                                introduction,
                                UL(null,
                                    sendMessageLI,
                                    LI(null, removeLink)
                                )
                            )
                        )
                    )
                );
            }
        }
    ]
);                                
friendslist.filter = 0;
friendslist.statevars.push('filter');
// Not using emtpycontent - that functionality has a broken implementation really
friendslist.updatecallback = function(data) {
    if (data.count == 0) {
        if (data.filter == 0 || data.filter == 1) {
            showFriendMessage(get_string('trysearchingforfriends', '<a href="" onclick="searchUsers(); return false;">', '</a>'));
        }
        else if (data.filter == 2) {
            showFriendMessage(get_string('nobodyawaitsfriendapproval'));
        }
    }
};
friendslist.updateOnLoad();

function filterChange() {
    addElementClass('searchresults', 'hidden');
    hideFriendMessage();
    var filter = $('filter').options[$('filter').selectedIndex].value;
    friendslist.doupdate({'filter': filter});
}

function searchUsers() {
    addElementClass('friendslist', 'hidden');
    addElementClass('filter', 'hidden');
    removeElementClass('backlink', 'hidden');
    hideFriendMessage();
    searchresults.query = $('friendsquery').value;
    searchresults.offset = 0;
    searchresults.doupdate();
}

function showFriendslist() {
    addElementClass('searchresults', 'hidden');
    addElementClass('backlink', 'hidden');
    removeElementClass('filter', 'hidden');
    hideFriendMessage();
    friendslist.doupdate();
}

function showFriendMessage(message) {
    $('friendmessage').innerHTML = message;
    removeElementClass('friendmessage', 'hidden');
}

function hideFriendMessage() {
    addElementClass('friendmessage', 'hidden');
    $('friendmessage').innerHTML = '';
}

var searchresults = new TableRenderer(
    'searchresults',
    config['wwwroot'] + 'user/searchfriends.json.php',
    []
);
searchresults.statevars.push('query');

searchresults.rowfunction = function(r, n, d) {
    var introduction = null;
    if (r.introduction != null) {
        introduction = P(null, createDOM('EM', null, '"' + r.introduction + '"'));
    }

    var actionButton = null;
    var tdAttrs = {};
    var suffix = '';
    if (r.id != config['userid']) {
        if (r.pending == 1) {
            actionButton = BUTTON({'type': 'button'}, STRONG(get_string('approverequest')));
            connect(actionButton, 'onclick', function(e) {
                approveFriend(r.id, searchresults);
                e.stop();
            });
            tdAttrs['class'] = 'pending';
            suffix = ' - ' + get_string('pending');
        }
        else if (r.friend == null) {
            // Check the friendscontrol setting
            if (r.friendscontrol == 'auto') {
                actionButton = BUTTON({'type': 'button'}, STRONG(get_string('addtomyfriends')));
                connect(actionButton, 'onclick', function(e) {
                    addFriend(r.id);
                    e.stop();
                });
            }
            else if (r.friendscontrol == 'auth') {
                if (r.requestedfriendship == 1) {
                    actionButton = get_string('friendshiprequested');
                }
                else {
                    actionButton = BUTTON({'type': 'button'}, STRONG(get_string('sendfriendrequest')));
                    connect(actionButton, 'onclick', function(e) {
                        sendFriendRequest(r.id, actionButton);
                        e.stop();
                    });
                }
            }
            else {
                actionButton = get_string('userdoesntwantfriends');
            }
        }
        else {
            suffix = ' - ' + get_string('existingfriend');
        }
    }

    var sendMessageLink = null;
    var sendMessageLI = null;
    if (r.id != config['userid'] && r.messages == 1) {
        sendMessageLink = A({'href': ''}, get_string('sendmessage'));
        connect(sendMessageLink, 'onclick', function(e) {
            sendMessage(r.id, sendMessageLink, searchresults);
            e.stop();
        });
        sendMessageLI = LI(null, sendMessageLink);
    }

    return TR({'class':'r'+(n%2)}, TD(tdAttrs,
        DIV({'class': 'fl'},
            IMG({'src': profileurl + r.id})
        ),
        TABLE({'class': 'friendinfo'},
            TR(null,
                TH(null,
                    DIV({'class': 'fr'}, actionButton),
                    H3(null, A({'href': viewurl + r.id}, r.name), suffix)
                )
            ),
            TR(null,
                TD({'id': 'friendinfo_' + r.id},
                    introduction,
                    UL(null,
                        sendMessageLI
                    )
                )
            )
        )
    ));
}
searchresults.updatecallback = function(data) {
    if (data.count == 0) {
        showFriendMessage(get_string('nosearchresultsfound'));
    }
};

