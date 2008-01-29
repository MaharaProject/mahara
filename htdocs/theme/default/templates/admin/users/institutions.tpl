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
					<th class="center">{str tag="Members" section="admin"}</th>
					<th class="center">{str tag="Maximum" section="admin"}</th>
					<th class="center">{str tag="Staff" section="admin"}</th>
					<th class="center">{str tag="Admins" section="admin"}</th>
					<th></th>
				</tr>
				</thead>
				{foreach from=$institutions item=institution}
				<tbody>
				<tr class="{cycle values=r1,r0}">
					<td>{$institution->displayname|escape}</td>
					<td class="center">
                                          {if $institution->name != 'mahara'}
                                            <a href="{$WWWROOT}admin/users/institutionusers.php?usertype=members&institution={$institution->name}">{$institution->members}</a>
                                          {else}
                                            <a href="{$WWWROOT}admin/users/search.php?institution=mahara">{$institution->members}</a>
                                          {/if}
                                        </td>
					<td class="center">{$institution->maxuseraccounts}</td>
					<td class="center"><a href="{$WWWROOT}admin/users/institutionstaff.php?institution={$institution->name}">{$institution->staff}</a></td>
					<td class="center"><a href="{$WWWROOT}admin/users/institutionadmins.php?institution={$institution->name}">{$institution->admins}</a></td>
					<td>
						<form action="" method="post">
							<input type="hidden" name="i" value="{$institution->name}">
							<input type="submit" class="submit" name="edit" value="{str tag="edit"}" id="admininstitution_edit">
							{if $siteadmin && !$institution->members && $institution->name != 'mahara'}<input type="submit" class="submit" name="delete" value="{str tag="delete"}" id="admininstitution_delete">{/if}
						</form>
					</td>
				</tr>
				{/foreach}
				<tr>
				</tbody>
			</table>
<div class="institutioneditbuttons">
{if $siteadmin}
            <form action="" method="post">
                <input type="submit" class="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
            </form>
{/if}
{if count($institutions) > 1}
  <div class="institutionedituserbuttons">
    <form action="{$WWWROOT}admin/users/institutionusers.php" method="post">
      <input type="submit" class="submit" name="editmembers" value="{str tag="editmembers" section="admin"}">
    </form>
    <form action="{$WWWROOT}admin/users/institutionstaff.php" method="post">
      <input type="submit" class="submit" name="editstaff" value="{str tag="editstaff" section="admin"}">
    </form>
    <form action="{$WWWROOT}admin/users/institutionadmins.php" method="post">
      <input type="submit" class="submit" name="editadmins" value="{str tag="editadmins" section="admin"}">
    </form>
  </div>
{/if}
</div>
			{/if}
			
			{/if}

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
