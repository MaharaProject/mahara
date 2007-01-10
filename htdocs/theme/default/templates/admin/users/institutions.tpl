{include file="header.tpl"}

{include file="columnfullstart.tpl"}
	
			<h2>AdminInstitutions</h2>
			
			{if $delete_form}
			<h3>{str tag="deleteinstitution" section="admin"}</h3>
			<p>{str tag="deleteinstitutionconfirm" section="admin"}</p>
			{$delete_form}
			{else}
			
			{if $institution_form}
			{if $add}
			<h3>{str tag="addinstitution" section="admin"}</h3>
			{/if}
			{$institution_form}
			{else}
			
			<table id="adminstitutionslist">
				<thead>
				<tr>
					<th>{str tag="institution"}</th>
					<th>{str tag="authplugin" section="admin"}</th>
					<th>{str tag="registrationallowed" section="admin"}</th>
					<th></th>
				</tr>
				</thead>
				{foreach from=$institutions item=institution}
				<tbody>
				<tr>
					<td>{$institution->displayname|escape}</td>
					<td>{$institution->authplugin}</td>
					<td>{if $institution->authplugin == 'internal'}{if $institution->registerallowed}{str tag="yes"}{else}{str tag="no"}{/if}{else}-{/if}</td>
					<td>
						<form action="" method="post">
							<input type="hidden" name="i" value="{$institution->name}">
							<input type="submit" name="edit" value="{str tag="edit"}" id="admininstitution_edit">
							{if !$institution->hasmembers && $institution->name != 'mahara'}<input type="submit" name="delete" value="{str tag="delete"}" id="admininstitution_delete">{/if}
						</form>
					</td>
				</tr>
				{/foreach}
				<tr>
					<td colspan="4">
						<form action="" method="post">
							<input type="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
						</form>
					</td>
				</tr>
				</tbody>
			</table>
			{/if}
			
			{/if}

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
