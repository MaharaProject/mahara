{include file="header.tpl"}

<p>{str tag=editselectedusersdescription1 section=admin}</p>

<div>
  {$suspendform|safe}
  {$changeauthform|safe}
  {$probationform|safe}
  {$deleteform|safe}
</div>

<div class="cl"></div>

<h2>{str tag=selectedusers section=admin} ({count($users)})</h2>
{include file="admin/users/userlist.tpl" users=$users}

{include file="footer.tpl"}
