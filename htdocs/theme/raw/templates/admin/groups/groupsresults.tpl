{foreach from=$groups item=group}
<tr class="{cycle values='r0,r1'}">
    <td>{$group->name}</td>
    <td class="center">{$group->members}</td>
    <td class="center">{$group->admins}</td>
    <td class="center">{str tag=name section=grouptype.$group->grouptype}: {str tag=membershiptype.$group->jointype section=group}</td>
    <td class="center">{$group->visibility}</td>
    <td class="center s"><a class="icon btn-manage" href="{$WWWROOT}admin/groups/manage.php?id={$group->id}">{str tag="groupmanage" section="admin"}</a></td>
    <td class="center s"><a class="icon btn-del" href="{$WWWROOT}admin/groups/delete.php?id={$group->id}">{str tag="groupdelete" section="admin"}</a></td>
</tr>
{/foreach}