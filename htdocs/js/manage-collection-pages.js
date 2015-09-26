/**
* Javascript for the manage collection pages
*
* @package    mahara
* @subpackage core
* @author     Catalyst IT Ltd
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
* @copyright  For copyright information on Mahara, please see the README file distributed with this software.
*
*/
jQuery(function($) {

    var updaterows = function(viewid) {
        var sortorder = $('#collectionviews').sortable('serialize');
        var collectionid = $('#collectionpages').data('collectionid');
        $.post(config.wwwroot + "collection/views.json.php", {
            sesskey: config.sesskey,
            id: collectionid,
            direction: sortorder }
        )
            .done(function(data) {
                // update the page with the new table
                if (data.returnCode == '0') {
                    $('#collectionviews').replaceWith(data.message.html);

                    if (viewid) {
                        $('#addviews_view_' + viewid + '_container').remove();
                        // check if we have just removed the last option leaving
                        // only the add pages button
                        if ($('#addviews .checkbox').children().length <= 1) {
                            // Remove addview button
                            $('#addviews').remove();
                            // Disply no page to add message
                            // And hide bulk select pages buttons
                            $('.select-pages').addClass('hidden');
                            $('#nopagetoadd').removeClass('hidden');
                        }
                    }
                    if (data.message.message) {

                        var warningClass = data.message.messagestatus === 'ok' ? 'success' : 'warning';

                        var warnmessage = $('<div id="changestatusline" class="alert alert-dismissible alert-' + warningClass + '" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><p>' + data.message.message + '</p></div>');

                        $('#messages').empty().append(warnmessage);
                    }
                    wiresortables();
                    wireaddrow();
                }
            });
        };

        var wiresortables = function() {
            $('#collectionviews').sortable({
                items: '> li',
                appendTo: '#collectionpages',
                cursor: 'move',
                helper: 'clone',
                opacity: 0.8,
                placeholder: 'highlight',
                stop: function(e, ui) {
                    // Get label within the div using children
                    // This is for receiving item from all pages list
                    var labelfor = ui.item.children().attr('for');
                    if (typeof labelfor !== 'undefined' && labelfor !== false) {
                        // remove all but the digits
                        var viewid = ui.item.children().attr('for').replace(/[^\d.]/g,'');
                        ui.item.replaceWith('<li id="row_' + viewid + '" class="dropped-in-row">' + ui.item.html() + '</li>');
                        updaterows(viewid);
                    }
                    else {
                        updaterows(false);
                    }
                }
            })
            .disableSelection()
            .hover(function() {
                $(this).css('cursor', 'move');
            });
        };

        var wireaddrow = function() {
            $('#addviews div').draggable({
                connectToSortable: '#collectionviews',
                cursor: 'move',
                revert: 'invalid',
                helper: 'clone'
            }).hover(function() {
                $(this).css('cursor', 'move');
            });
        };

        var wiredrop = function() {
            $('#collectionpages .dropzone-previews').droppable({
                accept: 'div',
                activeClass: 'highlight',
                drop: function (e, ui) {
                    var labelfor = ui.draggable.children().attr('for');
                    if (typeof labelfor !== 'undefined' && labelfor !== false) {
                        // remove all but the digits
                        var viewid = ui.draggable.children().attr('for').replace(/[^\d.]/g,'');
                        $('#collectionpages .dropzone-previews').replaceWith('<ol id="collectionviews"><li id="row_' + viewid + '">' + ui.draggable.html() + '</li></ol>');
                        wiresortables();
                        updaterows(viewid);
                    }
                },
            });
        };

        var wireselectall = function() {
            $("#selectall").click(function(e) {
                e.preventDefault();
                $("#addviews :checkbox").prop("checked", true);
            });
        };

        var wireselectnone = function() {
            $("#selectnone").click(function(e) {
                e.preventDefault();
                $("#addviews :checkbox").prop("checked", false);
            });
        };

        // init
        if ($('#collectionviews > li').length > 0) {
            wireaddrow();
            wiresortables();
        }
        else {
            wireaddrow();
            wiredrop();
        }
        wireselectall();
        wireselectnone();
    });
