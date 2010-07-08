{include file="header.tpl"}

{$searchform|safe}
			<table id="adminstitutionslist" class="fullwidth">
				<thead>
				<tr>
					<th>{str tag="groupname" section="admin"}</th>
					<th class="center">{str tag="groupmembers" section="admin"}</th>
                    <th class="center">{str tag="groupadmins" section="admin"}</th>
					<th class="center">{str tag="grouptype" section="admin"}</th>
					<th class="center">{str tag="groupvisible" section="admin"}</th>
					<th></th>
                    <th></th>
				</tr>
				</thead>
				<tbody>
				{foreach from=$groups.data item=group}
				<tr class="{cycle values='r0,r1'}">
					<td>{$group->name}</td>
                    <td class="center">{$group->members}</td>
					<td class="center">{$group->admins}</td>
					<td class="center">{str tag=name section=grouptype.$group->grouptype}: {str tag=membershiptype.$group->jointype section=group}</td>
					<td class="center">{$group->visibility}</td>
                    <td class="center"><a href="{$WWWROOT}admin/groups/manage.php?id={$group->id}">{str tag="groupmanage" section="admin"}</a></td>
                    <td class="center"><a href="{$WWWROOT}admin/groups/delete.php?id={$group->id}">{str tag="groupdelete" section="admin"}</a></td>
				</tr>
				{/foreach}
				</tbody>
			</table>
{$pagination.html|safe}

{include file="footer.tpl"}
