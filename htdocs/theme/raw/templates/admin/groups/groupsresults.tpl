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
            <a class="btn btn-secondary btn-sm" title="{str tag="groupmanage" section="admin"}" href="{$WWWROOT}admin/groups/manage.php?id={$group->id}">
                <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">{str(tag=groupmanagespecific section=admin arg1=$group->name)|escape:html|safe}</span>
            </a>
            <a class="btn btn-secondary btn-sm" title="{str tag="exportgroupmembershipscsv" section="admin"}" href="{$WWWROOT}download.php?type=groupmembership&groupid={$group->id}">
                <span class="icon icon-users icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">{str(tag=exportgroupmembershipscsvspecific section=admin arg1=$group->name)|escape:html|safe}</span>
            </a>
            <a class="btn btn-secondary btn-sm" title="{str tag="copy"}" href="{$WWWROOT}group/copy.php?id={$group->id}&return=adminlist">
                <span class="icon icon-clone icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}</span>
            </a>
            <a class="btn btn-secondary btn-sm" title="{str tag="delete"}" href="{$WWWROOT}admin/groups/delete.php?id={$group->id}">
                <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">{str(tag=deletespecific arg1=$group->name)|escape:html|safe}</span>
            </a>
        </div>
    </td>
</tr>
{/foreach}
