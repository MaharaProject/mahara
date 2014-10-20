{include file="header.tpl"}

<div id="notifications">
    <form method="post">
        <label for="notifications_type">{str section='activity' tag='type'}:</label>
        <select id="notifications_type" name="type">
        {foreach from=$options item=name key=t}
            <option value="{$t}"{if $type == $t} selected{/if}>{$name}</option>
        {/foreach}
        </select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
    </form>
    {$deleteall|safe}
    <form name="notificationlist" method="post" onSubmit="markread(this, 'read'); return false;">
        <table id="activitylist" class="fullwidth">
            <thead>
                <tr>
                    <th><span class="accessible-hidden">{str section='activity' tag='messagetype'}</span></th>
                    <th>{str section='artefact.multirecipientnotification' tag='fromuser'}</th>
                    <th>{str section='activity' tag='subject'}</th>
                    <th>{str section='artefact.multirecipientnotification' tag='touser'}</th>
                    <th>{str section='activity' tag='date'}</th>
                    <th class="center">{str section='activity' tag='read'}</th>
                    <th class="center">{str tag='delete'}</th>
                </tr>
            </thead>
        <tfoot>
            <tr>
                <td colspan="5"></td>
                <td class="center">
                    <a href="" onclick="toggleChecked('tocheckread'); return false;">
                        {str section='activity' tag='selectall'}
                        <span class="accessible-hidden"> {str tag='selectallread' section='artefact.multirecipientnotification'}</span>
                    </a>
                </td>
                <td class="center">
                    <a href="" onclick="toggleChecked('tocheckdel'); return false;">
                        {str section='activity' tag='selectall'}
                        <span class="accessible-hidden">{str tag='selectalldelete' section='artefact.multirecipientnotification'}</span>
                    </a>
                </td>
            </tr>
        </tfoot>
        <tbody>
            {$activitylist['tablerows']|safe}
        </tbody>
        </table>
        <div class="right activity-buttons">
            <input class="submit" type="submit" value="{str tag='markasread' section='activity'}" />
            <input class="submit btn-del" type="button" value="{str tag='delete'}" onClick="markread(document.notificationlist, 'del'); return false;" />
        </div>
    </form>
    {$activitylist['pagination']|safe}
</div>

{include file="footer.tpl"}
