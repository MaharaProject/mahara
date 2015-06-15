{include file="header.tpl"}
<a title="{str section='artefact.multirecipientnotification' tag='composemessagedesc'}" class="btn-with-heading btn-lg btn btn-default" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php">
    <span class="icon icon-edit"></span>
    {str section='artefact.multirecipientnotification' tag='composemessage'}
</a>


{if $activitylist.count > 0}

    <div id="notifications" class="ptl notification-parent">

        <div class="btn-group pull-left mbl" role="group">
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

        <form class="form-notificationlist pbl ptl" name="notificationlist" method="post">
            <div id="activitylist" class="notification-list"{if $paginatorData } data-paginator='{$paginatorData}'{/if}>
                {$activitylist['html']|safe}
            </div>
        </form>

        {$deleteall|safe}
        <div class="pull-right">
            {$activitylist['pagination']|safe}
        </div>
        <form method="post" class="form-inline form-select-filter pieform">
            <div class="form-group">
                <label for="notifications_type">{str section='activity' tag='type'}:</label>
                <div class="input-group select-group with-help">
                    <div class="input-group-addon" id="icon-addon-filter">
                        <span class="icon icon-filter"></span>
                    </div>
                    <div class="select form-group">
                        <div class="picker">
                            <select class="form-control select" id="notifications_type" name="type">
                            {foreach from=$options item=name key=t}
                                <option value="{$t}"{if $type == $t} selected{/if}>
                                    {$name}
                                </option>
                            {/foreach}
                            </select>
                        </div>
                    </div>
                    {contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
                </div>
            </div>
        </form>
    </div>
{else}
<div class="mtxl ptxl" id="notifications">
    <p class="lead mtxl ptxl text-center ">
          {str section='activity' tag='youroutboxisempty'}
    </p>
</div>
{/if}


{include file="footer.tpl"}
