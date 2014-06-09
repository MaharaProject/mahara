/**
 * Javascript for the Activity Stream
 * @source: http://gitorious.org/mahara/mahara
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

//self executing function for namespacing code
(function( ActivityStreamManager, $, undefined ) {

    function init() {
        $('.as-actionlike').click(function(event) {
            var link = $(this);
            sendjsonrequest(
                config.wwwroot + 'blocktype/activitystream/likes/like.json.php',
                {'activityid': link.attr('activityid'), 'action': link.attr('action')},
                'GET',
                function(reply) {
                    linkid = link.attr('id').replace('actionlike', '');
                    $('#totallikes' + linkid + '.as-totallikes').replaceWith(reply.totallikes);
                    $('#actionlike' + linkid + '.as-actionlike').html(reply.newactiontext);
                    $('#actionlike' + linkid + '.as-actionlike').attr('action', reply.newaction);
                }
            );
        });
    }

    $(document).ready(function() {
        init();
    });

}( window.ActivityStreamManager = window.ActivityStreamManager || {}, jQuery ));