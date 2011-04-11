{include file="header.tpl"}

{$searchform|safe}
			<table id="admgroupslist" class="fullwidth">
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
                    <th></th>
				</tr>
				</thead>
				<tbody>
				{$results.tablerows|safe}
				</tbody>
			</table>
{$results.pagination|safe}

{include file="footer.tpl"}
