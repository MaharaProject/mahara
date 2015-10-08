{include file="header.tpl"}
{$searchform|safe}
<div class="panel panel-default view-container">

    <table id="admgroupslist" class="fullwidth table table-striped">
        <thead>
        <tr>
            <th>{str tag="groupname" section="admin"}</th>
            <th class="center">{str tag="groupmembers" section="admin"}</th>
            <th class="center">{str tag="groupadmins" section="admin"}</th>
            <th>{str tag="grouptype" section="admin"}</th>
                                {if get_config('allowgroupcategories')}
                                    <th>{str tag="groupcategory" section="group"}</th>
                                {/if}
            <th>{str tag="groupvisible" section="admin"}</th>
            <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
        </tr>
        </thead>
        <tbody>
        {$results.tablerows|safe}
        </tbody>
    </table>
</div>
{$results.pagination|safe}
{include file="footer.tpl"}
