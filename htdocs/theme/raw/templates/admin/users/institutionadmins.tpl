{include file="header.tpl"}
<p class="lead">{str tag="institutionadminuserspagedescription1" section="admin"}</p>
<div class="panel panel-default panel-body">
	{$institutionselector|safe}
    <div class="userlistform">
		{$adminusersform|safe}
    </div>
</div>
{include file="footer.tpl"}
