{include file="header.tpl"}

{if $delete_form}

<h3>{str tag="deleteinstitution" section="admin"}</h3>
<p><strong>{$institutionname}</strong></p>
<p>{str tag="deleteinstitutionconfirm" section="admin"}</p>
{$delete_form|safe}

{elseif $institution_form}

  {if $suspended}
<div class="message">
  <h4>{$suspended}</h4>
  <div id="suspendedhelp">
    {if $USER->get('admin')}
    <p class="description">{str tag="unsuspendinstitutiondescription_top" section="admin"}</p>
    {else}
    <p class="description">{str tag="unsuspendinstitutiondescription_top_instadmin" section="admin"}</p>
    {/if}
  </div>
  <div class="center">{$suspendform_top|safe}</div>
</div>
  {/if}
  {if $add}
<h3>{str tag="addinstitution" section="admin"}</h3>
  {/if}
{$institution_form|safe}
  {if $suspendform}
<div id="suspendinstitution">
  <h3 id="suspend">{str tag="suspendinstitution" section=admin}</h3>
  <div class="suspendform">{$suspendform|safe}</div>
</div>
  {/if}

{else}
<table id="adminstitutionslist" class="fullwidth">
	<thead>
	<tr>
		<th>{str tag="institution"}</th>
		<th class="center">{str tag="Members" section="admin"}</th>
		<th class="center">{str tag="Maximum" section="admin"}</th>
		<th class="center">{str tag="Staff" section="admin"}</th>
		<th class="center">{str tag="Admins" section="admin"}</th>
		<th></th>
        <th></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td>
        {if $siteadmin}
            <form action="" method="post">
                <input type="submit" class="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
            </form>
        {/if}
        </td>
        <td colspan="5" class="institutionedituserbuttons right">{if count($institutions) > 1}
            <form action="{$WWWROOT}admin/users/institutionusers.php" method="post">
                <input type="submit" class="submit" name="editmembers" value="{str tag="editmembers" section="admin"}">
            </form>
            <form action="{$WWWROOT}admin/users/institutionstaff.php" method="post">
                <input type="submit" class="submit" name="editstaff" value="{str tag="editstaff" section="admin"}">
            </form>
            <form action="{$WWWROOT}admin/users/institutionadmins.php" method="post">
                <input type="submit" class="submit" name="editadmins" value="{str tag="editadmins" section="admin"}">
            </form>
        {/if}</td>
        <td></td>
    </tr>
	</tfoot>
	<tbody>
	{foreach from=$institutions item=institution}
	<tr class="{cycle values='r0,r1'}">
		<td>{$institution->displayname}</td>
		<td class="center">
		  {if $institution->name != 'mahara'}
			<a href="{$WWWROOT}admin/users/institutionusers.php?usertype=members&amp;institution={$institution->name}">{$institution->members}</a>
		  {else}
			<a href="{$WWWROOT}admin/users/search.php?institution=mahara">{$institution->members}</a>
		  {/if}
		</td>
		<td class="center">{$institution->maxuseraccounts}</td>
		<td class="center"><a href="{$WWWROOT}admin/users/institutionstaff.php?institution={$institution->name}">{$institution->staff}</a></td>
		<td class="center"><a href="{$WWWROOT}admin/users/institutionadmins.php?institution={$institution->name}">{$institution->admins}</a></td>
		<td class="admininstitutionbtns right">
			<form action="" method="post">
				<input type="hidden" name="i" value="{$institution->name}">
				<input type="submit" class="submit btn-edit s" name="edit" value="{str tag="edit"}">
				{if $siteadmin && !$institution->members && $institution->name != 'mahara'}<input type="submit" class="submit btn-del s" name="delete" value="{str tag="delete"}">{/if}
			</form>
		</td>
          <td class="center">{if $institution->suspended}<span class="suspended">{str tag="suspendedinstitution" section=admin}</span>{/if}</td>
	</tr>
	{/foreach}
	</tbody>
</table>

{/if}

{include file="footer.tpl"}
