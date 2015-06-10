{include file="header.tpl"}
<div class="panel panel-body">
	<p>{str tag="institutionadminuserspagedescription1" section="admin"}</p>
	{$institutionselector|safe}
    <div class="userlistform">
		{$adminusersform|safe}
    </div>
</div>
{include file="footer.tpl"}
