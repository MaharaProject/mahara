{include file="header.tpl"}

<div id="notifications">
    <form method="post" class="form-inline form-select-filter pbl">
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
            <input class="submit btn btn-success" type="submit" value="{str tag='markasread' section='activity'}" />
            <input class="submit btn btn-danger" type="button" value="{str tag='delete'}" onClick="markread(document.notificationlist, 'del'); return false;" />
        </div>
        <div class="selectall pull-right">
            <strong class="prm">{str section='activity' tag='selectall'}: </strong>
            <div class="markasread">
                <label class="selected" for="markallasread" onclick="toggleChecked('tocheckread'); return false;">
                     <input type="checkbox" data-tocheckread="true" name="markallasread" id="markallasread">
                    {str tag='markasread' section='activity'}
                    <span class="accessible-hidden sr-only"> {str tag='selectallread' section='artefact.multirecipientnotification'}</span>
                </label>
            </div>
            <div class="delete">
                <label class="selected" for="deleteall" onclick="toggleChecked('tocheckdel'); return false;">
                    <input type="checkbox" name="deleteall" id="deleteall">
                    {str tag='delete'}
                    <span class="accessible-hidden sr-only">{str tag='selectalldelete' section='artefact.multirecipientnotification'}</span>
                </label>
            </div>
        </div>
        <div id="activitylist" class="notification-list">
            {$activitylist['tablerows']|safe}
        </div>
    </form>
    {$deleteall|safe}
    {$activitylist['pagination']|safe}
</div>

{include file="footer.tpl"}
