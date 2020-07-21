{include file="header.tpl" headertype="progresscompletion"}

<div class="card progresscompletion">
    <div class="card-body">
        <p id="quota_message">
            {$quotamessage|safe}
        </p>
        <div id="quotawrap" class="progress">
            <div id="quota_fill" class="progress-bar {if $completedactionspercentage < 11}small-progress{/if}" role="progressbar" aria-valuenow="{if $completedactionspercentage }{$completedactionspercentage}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$completedactionspercentage}%;">
                <span>{$completedactionspercentage}%</span>
            </div>
        </div>
    </div>
</div>


<table class="fullwidth table tablematrix progresscompletion" id="tablematrix">
    <caption class="sr-only">{str tag="tabledesc" section="module.framework"}</caption>
    <tr class="table-pager">
        <th>{str tag="view"}</th>
        <th class="userrole">{str tag="signoff" section="blocktype.peerassessment/signoff"}</th>
        <th class="userrole">{str tag="verification" section="collection"}</th>
    </tr>
    {foreach from=$views item=view}
    <tr data-view="{$view->id}">
        <td><div><a href="{$view->fullurl}">{$view->displaytitle}</a></div></td>
        <td>
        {if $view->owneraction}
            <a class="{$view->owneraction}" href="#" data-view="{$view->id}" data-signedoff="{$view->signedoff}">
                <span title="{$view->ownertitle}" class="{$view->ownericonclass}"></span>
            </a>
        {else}
            <span title="{$view->ownertitle}" class="{$view->ownericonclass}"></span>
        {/if}
        </td>
        <td>
        {if $view->manageraction}
            <a class="{$view->manageraction}" href="#" data-view="{$view->id}" data-verified="{$view->verified}">
                <span title="{$view->managertitle}" class="{$view->managericonclass}"></span>
            </a>
        {else}
            <span title="{$view->managertitle}" class="{$view->managericonclass}"></span>
        {/if}
        </td>
    </tr>
    {/foreach}
</table>

{* signoff modal form *}
<div tabindex="0" class="modal fade" id="signoff-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    {str tag=signoffpagetitle section=blocktype.peerassessment/signoff}
                </h1>
            </div>
            <div class="modal-body">
                <p id="signoff-on" class="hidden">{str tag=signoffpageundodesc section=blocktype.peerassessment/signoff}</p>
                <p id="signoff-off" class="hidden">{str tag=signoffpagedesc section=blocktype.peerassessment/signoff}</p>
                <div class="btn-group">
                    <button id="signoff-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="signoff-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                </div>
            </div>
        </div>
    </div>
</div>
{* verify modal form *}
<div tabindex="0" class="modal fade" id="verify-confirm-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">
                    {str tag=verifypagetitle section=blocktype.peerassessment/signoff}
                </h1>
            </div>
            <div class="modal-body">
                <p>{str tag=verifypagedesc section=blocktype.peerassessment/signoff}</p>
                <div class="btn-group">
                    <button id="verify-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                    <button id="verify-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/javascript">
$(function() {
    var
    verified_title = "{str tag='verified' section='collection'}",
    needsverified_title = "{str tag='needsverified' section='collection'}",
    needssignedoff_title = "{str tag='needssignedoff' section='collection'}",
    signedoff_title = "{str tag='signedoff' section='collection'}";
    totalactions = "{$totalactions}";

    // click 'No' button on modals
    $("#signoff-back-button, #verify-back-button").on('click', function() {
        $("#signoff-confirm-form").modal('hide');
        $("#verify-confirm-form").modal('hide');
    });

    //sign off action
    $('.signoff_action , .unsignoff_action').each(function() {
        add_click_event_handler_action_signoff(this);
    });
    $('#signoff-yes-button').on('click', function(event) {
        $("#verify-confirm-form").modal('hide');
        event.preventDefault();
        event.stopPropagation();
        var viewid = $("#signoff-confirm-form").attr('viewid');
        var signedoff = $("#signoff-confirm-form").attr('signedoff');
        var newvalue = (signedoff == "" ? 1 :  0);
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': viewid, 'signoff': newvalue }, 'POST', function (data) {
            if (data.data) {
                var cell, icon,
                viewid = $("#signoff-confirm-form").attr('viewid');

                if (data.data.signoff_newstate) {
                    icon = $('a.signoff_action[data-view="' + viewid + '"] span');
                    icon.removeClass('icon-circle action').addClass('icon-check-circle completed');
                    icon.attr('title', signedoff_title);
                    cell = $('a.signoff_action[data-view="' + viewid + '"]');
                    cell.removeClass('signoff_action').addClass('unsignoff_action');
                    update_progress_bar(1);
                }
                else {
                    var resetactions = 1;
                    icon = $('a.unsignoff_action[data-view="' + viewid + '"] span');
                    icon.removeClass('icon-check-circle completed').addClass('icon-circle action');
                    icon.attr('title', needssignedoff_title);
                    cell = $('a.unsignoff_action[data-view="' + viewid + '"]');
                    cell.removeClass('unsignoff_action').addClass('signoff_action');
                    //also reset the verified icon to unverified
                    var verifiedicon = $('[data-view="' + viewid + '"] span.completed');
                    if (verifiedicon.length) {
                        verifiedicon.removeClass("icon-check-circle completed").addClass('icon-cicle dot disabled');
                        verifiedicon.attr('title', needsverified_title);
                        resetactions++;
                    }
                    update_progress_bar(-1 * resetactions);
                }
                cell.attr('data-signedoff', data.data.signoff_newstate);
                add_click_event_handler_action_signoff(cell);
            }
            $("#signoff-confirm-form").modal('hide');
        });
    });

    // verify action
    $('.verify_action').each(function() {
        $(this).on('click',function() {
            $("#verify-confirm-form").attr('viewid', $(this).attr('data-view'));
            $("#verify-confirm-form").attr('verified', $(this).attr('data-verified'));
            $("#verify-confirm-form").modal('show');
        });
    });
    $('#verify-yes-button').on('click', function(event) {
        $("#signoff-confirm-form").modal('hide');
        event.preventDefault();
        event.stopPropagation();
        var viewid = $("#verify-confirm-form").attr('viewid');
        var verified = $("#verify-confirm-form").attr('verified');
        var newvalue = (verified == "" ? 1 :  0);
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': viewid, 'verify': newvalue }, 'POST', function (data) {
            if (data.data) {
                var cell, icon,
                viewid = $("#verify-confirm-form").attr('viewid');
                if (data.data.verify_newstate) {
                    icon = $('a.verify_action[data-view="' + viewid + '"] span');
                    icon.removeClass('icon-circle action').addClass('icon-check-circle completed');
                    icon.attr('title', verified_title);
                    $('a.verify_action[data-view="' + viewid + '"]').replaceWith(icon);
                    update_progress_bar(1);
                }
            }
            $("#verify-confirm-form").modal('hide');
        });
    });

    function add_click_event_handler_action_signoff(el) {
        $(el).on('click',function() {
            $("#signoff-confirm-form").attr('viewid', $(this).attr('data-view'));
            var signedoff = $(this).attr('data-signedoff');
            $("#signoff-confirm-form").attr('signedoff', signedoff);
            if (signedoff) {
                $('#signoff-on').removeClass('hidden');
                $('#signoff-off').addClass('hidden');
            }
            else {
                $('#signoff-on').addClass('hidden');
                $('#signoff-off').removeClass('hidden');
            }
            $("#signoff-confirm-form").modal('show');
        });
    }

    /*
     * Update the progress bar percentage
     * it calculates the number of signed off pages base on the actual percentage
     * of the progress bar and the total number of pages in the collection
     * @param int update will be 1 if the user signed off
     *  -1 if the user removed a sign off
     */
    function update_progress_bar(update) {
        var percentage_text = $('#quota_fill span')[0].innerHTML;
        var percentage_int = parseInt(percentage_text.replace('%', ''));
        var old_completed_actions = Math.round((totalactions * percentage_int) / 100);
        var new_completed_actions = old_completed_actions + update;
        var new_percentage = Math.round((new_completed_actions/totalactions)*100);
        $("#quota_fill span")[0].innerHTML = new_percentage + "%";
        $("#quota_fill").width(new_percentage + "%");
    }
});
</script>
{include file="footer.tpl"}
