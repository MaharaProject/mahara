{include file="header.tpl"}

<div id="notifications">
    <form method="post" class="form-inline form-select-filter ptl pbl">
        <div class="form-group">
            <label for="notifications_type">{str section='activity' tag='type'}:</label>
            <select id="notifications_type" name="type">
            {foreach from=$options item=name key=t}
                <option value="{$t}"{if $type == $t} selected{/if}>
                    {$name}
                </option>
            {/foreach}
            </select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
        </div>
    </form>
    <form class="form-notificationlist ptl pbl" name="notificationlist" method="post" onSubmit="markread(this, 'read'); return false;">
        <div class="activity-buttons pull-left">
            <input class="submit btn btn-danger" type="button" value="{str tag='delete'}" onClick="markread(document.notificationlist, 'del'); return false;" />
        </div>
        <div class="selectall pull-right">
            <strong class="prm">{str section='activity' tag='selectall'}: </strong>
            <div class="delete">
                <label class="selected" for="deleteall">
                    <input type="checkbox" name="deleteall" id="deleteall" data-togglecheckbox="tocheckdel">
                    {str tag='delete'}
                    <span class="accessible-hidden sr-only">{str tag='selectalldelete' section='artefact.multirecipientnotification'}</span>
                </label>
            </div>
        </div>
        <div id="activitylist" class="notification-list ptl">
            {$activitylist['tablerows']|safe}
        </div>
    </form>
    {$deleteall|safe}
    {$activitylist['pagination']|safe}
</div>

{include file="footer.tpl"}
