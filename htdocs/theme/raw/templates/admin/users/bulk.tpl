{include file="header.tpl"}

<p>{str tag=editselectedusersdescription section=admin}</p>

<div>
  <div class="bulkactionform">
    <span class="bulkaction-title">{str tag=exportusersascsv section=admin}:</span>
    <a href="{$WWWROOT}download.php" target="_blank">{str tag=Download section=admin}</a>
  </div>
  {$suspendform|safe}
</div>

<div>
  {$changeauthform|safe}
  {$deleteform|safe}
</div>

<div class="cl"></div>

<h2>{str tag=selectedusers section=admin} ({count($users)})</h2>
{include file="admin/users/userlist.tpl" users=$users}

{include file="footer.tpl"}
