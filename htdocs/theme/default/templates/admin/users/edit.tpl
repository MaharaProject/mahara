{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <h2><a href="{$WWWROOT}user/view.php?id={$user->id}">{$user->firstname} {$user->lastname} ({$user->username})</a></h2>
    {if empty($user->suspendedcusr)}
      <h3>{str tag="suspenduser" section="admin"}</h3>
    {else}
      <h4>{str tag="thisuserissuspended" section="admin"}</h4>
      <div><strong>{str tag="suspendedreason" section="admin"}:</strong></div>
      <div>{$user->suspendedreason}</div>
    {/if}
    {$suspendform}
    <h3>{str tag="siteaccountsettings" section="admin"}</h3>
    {$mainform}
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

