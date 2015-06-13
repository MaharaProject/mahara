{include file="header.tpl"}

<div class="form with-heading">
  
  {$suspendform|safe}
  {$changeauthform|safe}
  {$probationform|safe}
  {$deleteform|safe}

</div>

<p class="mtxl lead">{str tag=editselectedusersdescription1 section=admin}</p>

<div class="panel panel-default">
	<h2 class="panel-heading">{str tag=selectedusers section=admin} ({count($users)})</h2>
	{include file="admin/users/userlist.tpl" users=$users}
</div>

{include file="footer.tpl"}
