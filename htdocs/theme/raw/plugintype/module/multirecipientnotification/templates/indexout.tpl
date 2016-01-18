{include file="header.tpl"}
<a title="{str section='module.multirecipientnotification' tag='composemessagedesc'}" class="btn-with-heading btn-lg btn btn-default" href="{$WWWROOT}module/multirecipientnotification/sendmessage.php">
    <span class="icon icon-edit icon-lg left" role="presentation" aria-hidden="true"></span>
    {str section='module.multirecipientnotification' tag='composemessage'}
</a>


{if $activitylist.count > 0}

    <div id="notifications" class="notification-parent"  data-requesturl="indexout.json.php">

        <div class="btn-group pull-leftbulk-actions" role="group">
            <label class="btn btn-default" for="selectall">
                <input type="checkbox" name="selectall" id="selectall" data-togglecheckbox="tocheck">
                <span class="sr-only">{str section='activity' tag='selectall'}</span>
            </label>

            <button type="button" class="btn btn-default" data-toggle="dropdown" aria-expanded="false">
                {str section='admin' tag='bulkactions'}
            </button>
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="caret"></span>
            </button>

            <div class="activity-buttons dropdown-menu" role="menu">
                <button class="btn btn-link btn-link-danger" data-action="deleteselected">
                    {str tag='delete'}
                </button>

                <a href="#delete_all_notifications_submit" class="btn btn-link btn-link-danger" data-triggersubmit="delete_all_notifications_submit">
                    {str section='activity' tag='deleteallnotifications'}
                </a>
            </div>
        </div>
        <form method="post" class="form-inline form-select-filter pieform pull-right">
            <div class="form-group">
                <label for="notifications_type" class="sr-only">{str section='activity' tag='type'}:</label>
                <div class="input-group select-group">
                    <div class="input-group-addon" id="icon-addon-filter">
                        <span class="icon icon-filter" role="presentation" aria-hidden="true"></span>
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

        <form class="form-notificationlist js-notifications" name="notificationlist" method="post">
            <div id="activitylist" class="notification-list">
                {$activitylist['html']|safe}
            </div>
        </form>

        {$deleteall|safe}
        <div class="fullwidth">
            {$activitylist['pagination']|safe}
        </div>
    </div>
{else}
<div class="notifications-empty" id="notifications">
    <p class="no-results">
        {str section='activity' tag='youroutboxisempty'}
    </p>
</div>
{/if}


{include file="footer.tpl"}
