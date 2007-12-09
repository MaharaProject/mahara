{include file="header.tpl"}

{include file="columnfullstart.tpl"}
	
			<h2>{str tag="admininstitutions" section="admin"}</h2>
			
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
				<tr class="{cycle values=r1,r0}">
					<td>{$institution->displayname|escape}</td>
					<td>{$institution->authplugin}</td>
					<td>{if $institution->authplugin == 'internal'}{if $institution->registerallowed}{str tag="yes"}{else}{str tag="no"}{/if}{else}-{/if}</td>
					<td>
						<form action="" method="post">
							<input type="hidden" name="i" value="{$institution->name}">
							<input type="submit" class="submit" name="edit" value="{str tag="edit"}" id="admininstitution_edit">
							{if $siteadmin && !$institution->hasmembers && $institution->name != 'mahara'}<input type="submit" class="submit" name="delete" value="{str tag="delete"}" id="admininstitution_delete">{/if}
						</form>
					</td>
				</tr>
				{/foreach}
				<tr>
				</tbody>
			</table>
{if $siteadmin}
            <form action="" method="post">
                <input type="submit" class="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
            </form>
{/if}
			{/if}
			
			{/if}

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
