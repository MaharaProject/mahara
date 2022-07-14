{foreach from=$groups item=group}
<tr class="{cycle values='r0,r1'}">
    <td><a href="{$group->homepage_url}">{$group->name}</a></td>
    <td>{$group->shortname}</td>
    <td class="center">{$group->members}</td>
    <td class="center">{$group->admins}</td>
    <td>{strip}
      {str tag=name section=grouptype.$group->grouptype}
      {if $group->jointype != 'approve'}, {str tag=membershiptype.abbrev.$group->jointype section=group}{/if}
      {if $group->request}, {str tag=request section=group}{/if}
    {/strip}</td>
    {if get_config('allowgroupcategories')}
        <td>{$group->categorytitle}</td>
    {/if}
    <td>{$group->visibility}</td>
    <td>{$group->institutionname}</td>
    <td class="right">
        <div class="btn-group">
            <button class="btn btn-secondary btn-sm" title="{str tag="groupmanage" section="admin"}" type="button" data-url="{$WWWROOT}admin/groups/manage.php?id={$group->id}">
                <span class="icon icon-cog" role="presentation" aria-hidden="true"></span><span class="visually-hidden">{str(tag=groupmanagespecific section=admin arg1=$group->name)|escape:html|safe}</span>
            <button class="btn btn-secondary btn-sm" title="{str tag="exportgroupmembershipscsv" section="admin"}" type="button" data-url="{$WWWROOT}download.php?type=groupmembership&groupid={$group->id}">
                <span class="icon icon-people-group" role="presentation" aria-hidden="true"></span><span class="visually-hidden">{str(tag=exportgroupmembershipscsvspecific section=admin arg1=$group->name)|escape:html|safe}</span>
            </button>
            <button class="btn btn-secondary btn-sm" title="{str tag="copy"}" type="button" data-url="{$WWWROOT}group/copy.php?id={$group->id}&return=adminlist">
                <span class="icon icon-regular icon-clone" role="presentation" aria-hidden="true"></span><span class="visually-hidden">{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}</span>
            </button>
            <button class="btn btn-secondary btn-sm" title="{str tag="delete"}" type="button" data-url="{$WWWROOT}admin/groups/delete.php?id={$group->id}">
                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span><span class="visually-hidden">{str(tag=deletespecific arg1=$group->name)|escape:html|safe}</span>
            </button>
        </div>
    </td>
</tr>
{/foreach}
