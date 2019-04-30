/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */
jQuery(function($) {
    "use strict";

    var paginatorData = window.paginatorData,
        requesturl = 'indexin.json.php';

    // Set up event handlers
    function init() {

        // Re-attach listeners when page has finished updating
        $(window).on('pageupdated',function() {
            // Need to reset this if lazy loading a block
            if ($('[data-requesturl]').length > 0) {
                requesturl = $('[data-requesturl]').attr('data-requesturl');
            }
            attachNotificationEvents();
        });

        attachNotificationEvents();

        if ($('[data-requesturl]').length > 0) {
            requesturl = $('[data-requesturl]').attr('data-requesturl');
        }

        $('.notification .control-wrapper').on('click', function(e) {
            e.stopPropagation();
        });

        // Check all of type
        $('[data-togglecheckbox]').on('change', function(){
            var targetClass = '.' + $(this).attr('data-togglecheckbox');
            $(targetClass).prop('checked', $(this).prop('checked'));
            $(targetClass).trigger('change');
        });

        $('[data-triggersubmit]').on('click', function(){
            var targetID = '#'  + $(this).attr('data-triggersubmit');
            $(targetID).trigger('click');
        });

        $('[data-action="markasread"]').on('click', function(e){
            e.preventDefault;
            markread(e, this, paginatorData);
        });

        $('[data-action="deleteselected"]').on('click', function(e){
            e.preventDefault;
            deleteselected(e, this, paginatorData);
        });

        $('.js-notifications-type').on('change', function(e){
            changeactivitytype(e);
        });
    }

    function markread(e, self, paginatorData) {

        var checked = $(self).closest('.notification-parent').find('.js-notifications .control.unread input:checked'),
            i,
            requestdata = {};

        if(checked.length < 1){
            //@todo maybe tell the user they need something valid checked
            return; //no valid items selected
        }

        for (i = 0; i < checked.length; i++) {
            requestdata[checked[i].name] = 1;
        }

        requestdata['markasread'] = 1;

        if (paginatorData) {
            for (var page in paginatorData.params) {
                if(paginatorData.params.hasOwnProperty(page)){
                    requestdata[page] = paginatorData.params[page];
                }
            }
        }

        sendjsonrequest(requesturl, requestdata, 'GET', function (data) {
            updateUnread(data, false);
        });
    }

    function deleteselected(e, self, paginatorData) {

        var checked = $(self).closest('.notification-parent').find('.js-notifications .control input:checked'),
            i,
            requestdata = {};

        if(checked.length < 1){
            //@todo tell the user they need something valid checked
            return; //no valid items selected
        }

        for (i = 0; i < checked.length; i++) {
            requestdata[checked[i].name] = 0;
        }

        requestdata['delete'] = 1;

        if (paginatorData) {
            for (var page in paginatorData.params) {
                if(paginatorData.params.hasOwnProperty(page)){
                    requestdata[page] = paginatorData.params[page];
                }
            }
        }

        sendjsonrequest(requesturl, requestdata, 'GET', function (data) {
            updateUnread(data, false);
            window.paginator.updateResults(data);
        });
    }

    function markthisread(e, self, paginatorData) {

        var checked = $(self).find('.control.unread input.tocheck'),
           inboxblockunread = $(self).find('.link-block.unread'),
           item = self,
           i,
           requestdata = {};

        if (checked.length < 1 && inboxblockunread.length < 1) {
            return; // no valid items selected
        }

        for (i = 0; i < checked.length; i++) {
            requestdata[checked[i].name] = 1;
        }

        requestdata['list'] = $(self).find('a[data-list]').attr('data-list');
        requestdata['readone'] = $(self).find('a[data-id]').attr('data-id');

        if (paginatorData) {
            for (var page in paginatorData.params) {
                if(paginatorData.params.hasOwnProperty(page)){
                    requestdata[page] = paginatorData.params[page];
                }
            }
        }

        sendjsonrequest(requesturl, requestdata, 'GET', function (data) {
            updateUnread(data, item);
        });
    }

    function updateUnread(data, self) {
        var inboxmenu = $('#nav-inbox'),
            countnode,
            countnodesr,
            notificationList = $('.notification-list');

        if (inboxmenu.length < 1) {
            return;
        }
        if (data.data.newunreadcount !== undefined) {
            countnode = inboxmenu.find('.unreadmessagecount');
            if (countnode.length > 0) {
                countnode.html(data.data.newunreadcount);
            }
            countnodesr = inboxmenu.find('.unreadmessagecount-sr');
            if (countnodesr.length > 0) {
                countnodesr.html(data.data.newunreadcounttext);
            }
        }
        if (data.data.html) {
            notificationList.html(data.data.html);
        }
        else if (self) {
            $(self).removeClass('text-weight-bold js-card-unread').addClass('card');
            $(self).find('.control').removeClass('control');
            $(self).find('.unread').removeClass('unread'); // for inbox block
        }
        $('#selectall').attr('checked', false); // Need to uncheck bulk checkbox
    }

    function changeactivitytype(e) {
        var delallform = document.forms['delete_all_notifications'],
            params,
            query = $(e.currentTarget).val();

        delallform.elements['type'].value = query;
        params = {'type': query};

        sendjsonrequest(requesturl, params, 'GET', function(data) {
            window.paginator.updateResults(data);
            attachNotificationEvents();
        });
    }

    function attachNotificationEvents() {

        // Add warning class to all selected notifications
        $('.card .control input').on('change', function() {
            if ($(this).prop('checked')) {
                $(this).closest('.card').addClass('card-warning');
            }
            else {
                $(this).closest('.card').removeClass('card-warning');
            }
        });

        $('.js-card-unread').on('show.bs.collapse', function(e) {
            markthisread(e, this, paginatorData);
        });
    }

    init();
});
