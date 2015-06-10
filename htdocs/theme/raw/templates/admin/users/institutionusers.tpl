{include file="header.tpl"}
<div class="panel panel-body">
	<p>{str tag="institutionmemberspagedescription" section="admin"}</p>
	<p>{$instructions}</p>
	{$usertypeselector|safe}
	<div class="userlistform">
		{$institutionusersform|safe}
	</div>
</div>
{include file="footer.tpl"}
