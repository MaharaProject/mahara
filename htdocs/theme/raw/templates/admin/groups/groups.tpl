{auto_escape off}
{include file="header.tpl"}
			
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
				{foreach from=$groups item=group}
				<tr class="{cycle values='r0,r1'}">
					<td>{$group->name}</td>
                    <td class="center">{$group->members}</td>
					<td class="center">{$group->admins}</td>
					<td class="center">{$group->type}</td>
					<td class="center">{$group->visible}</td>
                    <td class="center"><a href="{$WWWROOT}admin/groups/manage.php?id={$group->id}">{str tag="groupmanage" section="admin"}</a></td>
                    <td class="center"><a href="{$WWWROOT}admin/groups/delete.php?id={$group->id}">{str tag="groupdelete" section="admin"}</a></td>
				</tr>
				{/foreach}
				</tbody>
			</table>

{include file="footer.tpl"}
{/auto_escape}
