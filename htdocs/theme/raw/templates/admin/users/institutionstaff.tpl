{include file="header.tpl"}
<div class="panel panel-body">
	<p>{str tag="institutionstaffuserspagedescription1" section="admin"}</p>
	{$institutionselector|safe}
    <div class="userlistform">
		{$staffusersform|safe}
    </div>
</div>
{include file="footer.tpl"}
