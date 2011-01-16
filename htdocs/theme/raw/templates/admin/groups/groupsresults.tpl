{foreach from=$groups item=group}
<tr class="{cycle values='r0,r1'}">
    <td>{$group->name}</td>
    <td class="center">{$group->members}</td>
    <td class="center">{$group->admins}</td>
    <td>{str tag=name section=grouptype.$group->grouptype}: {str tag=membershiptype.$group->jointype section=group}</td>
    {if get_config('allowgroupcategories')}
        <td>{$group->categorytitle}</td>
    {/if}
    <td>{$group->visibility}</td>
    <td class="right"><a href="{$WWWROOT}admin/groups/manage.php?id={$group->id}"><img src="{theme_url filename="images/manage.gif"}" alt="{str tag="groupmanage" section="admin"}"></a>
      <a title="{str tag="delete"}" href="{$WWWROOT}admin/groups/delete.php?id={$group->id}">
        <img src="{theme_url filename="images/icon_close.gif"}" alt="[x]">
      </a>
    </td>
</tr>
{/foreach}