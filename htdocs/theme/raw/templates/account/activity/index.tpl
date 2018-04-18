{include file="header.tpl"}
<div id="notifications">
    <form method="post" class="form-inline pieform">
        <div class="form-group select">
            <label for="notifications_type">{str section='activity' tag='type'}:</label>
            <div class="picker">
                <select class="form-control select" id="notifications_type" name="type">
                    <option value="all">--</option>
                {foreach from=$options item=name key=t}
                    <option value="{$t}"{if $type == $t} selected{/if}>{$name}</option>
                {/foreach}
                </select>
            </div>

            {contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
        </div>
    </form>

    {$deleteall|safe}
    <form name="notificationlist" method="post" onSubmit="markread(this, 'read'); return false;">
        <table id="activitylist" class="fullwidth table table-striped">
            <thead>
                <tr>
                    <th><span class="accessible-hidden sr-only">{str section='activity' tag='messagetype'}</span></th>
                    <th>{str section='activity' tag='subject'}</th>
                    <th>{str section='activity' tag='date'}</th>
                    <th class="center">{str section='activity' tag='read'}</th>
                    <th class="center">{str tag='delete'}</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="center">
                        <a href="" data-togglecheckbox="tocheckread">{str section='activity' tag='selectall'}<span class="accessible-hidden sr-only"> {str section='activity' tag='selectallread'}</span></a>
                    </td>
                    <td class="center">
                        <a href="" data-togglecheckbox="tocheckdel">{str section='activity' tag='selectall'} <span class="accessible-hidden sr-only">{str section='activity' tag='selectalldelete'}</span></a>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                {$activitylist.tablerows|safe}
            </tbody>
        </table>
        <div class="right activity-buttons">
            <input class="submit btn btn-primary" type="submit" value="{str tag='markasread' section='activity'}" />
            <input class="submit btn btn-secondary" type="button" value="{str tag='delete'}" onClick="markread(document.notificationlist, 'del'); return false;" />
        </div>
        {$activitylist.pagination|safe}
    </form>
</div>
{include file="footer.tpl"}
