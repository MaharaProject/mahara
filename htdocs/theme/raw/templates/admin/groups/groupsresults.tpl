{foreach from=$groups item=group}
<tr class="{cycle values='r0,r1'}">
    <td><a href="{$group->homepage_url}">{$group->name}</a></td>
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
    <td class="right">
        <a title="{str tag="groupmanage" section="admin"}" href="{$WWWROOT}admin/groups/manage.php?id={$group->id}">
            <img src="{theme_image_url filename="btn_configure"}" alt="{str(tag=groupmanagespecific section=admin arg1=$group->name)|escape:html|safe}">
        </a>
        <a title="{str tag="delete"}" href="{$WWWROOT}admin/groups/delete.php?id={$group->id}">
            <img src="{theme_image_url filename="btn_deleteremove"}" alt="{str(tag=deletespecific arg1=$group->name)|escape:html|safe}">
        </a>
    </td>
</tr>
{/foreach}
