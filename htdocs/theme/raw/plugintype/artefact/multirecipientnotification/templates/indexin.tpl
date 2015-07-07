{include file="header.tpl"}
<a title="{str section='artefact.multirecipientnotification' tag='composemessagedesc'}" class="btn-with-heading btn-lg btn btn-default" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php">
    <span class="icon icon-edit"></span>
    {str section='artefact.multirecipientnotification' tag='composemessage'}
</a>


{if $activitylist.count > 0}

    <div id="notifications" class="ptl notification-parent" data-requesturl="indexin.json.php">

        <div class="btn-group pull-left mbl bulk-actions" role="group">
            <label class="btn btn-default" for="selectall">
                <input type="checkbox" name="selectall" id="selectall" data-togglecheckbox="tocheck">
                <span class="sr-only">{str section='activity' tag='selectall'}</span>
            </label>

            <button class="btn btn-default" type="button" data-toggle="dropdown" aria-expanded="false">
                {str section='admin' tag='bulkactions'}
            </button>

            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                <span class="caret"></span>
            </button>

            <div class="activity-buttons dropdown-menu" role="menu">
                <button class="btn btn-link" data-action="markasread">
                    {str tag='markasread' section='activity'}
                </button>
                <button class="btn btn-link btn-link-danger" data-action="deleteselected">
                    {str tag='delete'}
                </button>
                <a class="btn btn-link btn-link-danger" href="#delete_all_notifications_submit" data-triggersubmit="delete_all_notifications_submit">
                    {str section='activity' tag='deleteallnotifications'}
                </a>
            </div>
        </div>
        <form method="post" class="mlm form-inline form-select-filter pieform form-as-button pull-right">
            <div class="form-group">
                <label class="sr-only" for="notifications_type">{str section='activity' tag='type'}:</label>
                <div class="input-group select-group">
                    <div class="input-group-addon" id="icon-addon-filter">
                        <span class="icon icon-filter"></span>
                    </div>
                    <div class="select form-group">
                        <div class="picker">
                            <select class="form-control select js-notifications-type" id="notifications_type" name="type">
                            {foreach from=$options item=name key=t}
                                <option value="{$t}"{if $type == $t} selected{/if}>
                                    {$name}
                                </option>
                            {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form class="form-notificationlist js-notifications pbl" name="notificationlist" method="post">
            <div id="activitylist" class="notification-list">
                {$activitylist['html']|safe}
            </div>
        </form>

        {$deleteall|safe}
        <div class="pull-right">
            {$activitylist['pagination']|safe}
        </div>
    </div>
{else}
<div class="notifications-empty" id="notifications">
    <p class="no-results">
        {str section='activity' tag='yourinboxisempty'}
    </p>
</div>
{/if}

{include file="footer.tpl"}
