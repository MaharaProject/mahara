{include file="header.tpl"}
{$searchform|safe}
<div class="card view-container">

    <table id="admgroupslist" class="fullwidth table table-striped table-group-list">
        <thead>
        <tr>
            <th class="groupname">{str tag="groupname" section="admin"}</th>
            <th class="groupshortname">{str tag="groupshortname" section="admin"}</th>
            <th class="center">{str tag="groupmembers" section="admin"}</th>
            <th class="center">{str tag="groupadmins" section="admin"}</th>
            <th>{str tag="grouptype" section="admin"}</th>
                                {if get_config('allowgroupcategories')}
                                    <th>{str tag="groupcategory" section="group"}</th>
                                {/if}
            <th>{str tag="groupvisible" section="admin"}</th>
            <th>{str tag="institution"}</th>
            <th class="groupmanagebuttons"><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
        </tr>
        </thead>
        <tbody>
        {$results.tablerows|safe}
        </tbody>
    </table>
    {if $results.csv}
        <a href="{$WWWROOT}download.php" class="card-footer"><span class="icon icon-table" role="presentation" aria-hidden="true"></span> {str tag=exportgroupscsv section=admin}</a>
    {/if}
</div>
{$results.pagination|safe}
{include file="footer.tpl"}
